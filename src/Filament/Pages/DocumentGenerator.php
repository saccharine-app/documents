<?php

namespace Saccharine\Documents\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Saccharine\Documents\Services\DocumentEngine;
use Saccharine\Documents\Support\DemoTemplates;
use Closure;

class DocumentGenerator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog';
    protected static \UnitEnum|string|null $navigationGroup = 'System Utilities';
    protected string $view = 'saccharine-documents::filament.pages.document-generator';
    protected static ?string $title = 'Document Generator';
    protected static ?string $navigationLabel = 'Document Generator';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('demo_preset')
                    ->label('Load Sample Template')
                    ->options([
                        'nda' => 'Standard NDA (Markdown)',
                        'invoice' => 'Simple B2B Invoice (Basic HTML)',
                        'contractor' => 'Construction/Renovation Contract (Advanced HTML)',
                        'blank' => 'Blank Template',
                    ])
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if ($state === 'contractor') {
                            $set('template_type', 'html_blade');
                            $set('html_content', DemoTemplates::getContractorHtml());
                            $set('json_payload', DemoTemplates::getContractorJson());
                        } elseif ($state === 'invoice') {
                            $set('template_type', 'html_blade');
                            $set('html_content', DemoTemplates::getSimpleInvoiceHtml());
                            $set('json_payload', DemoTemplates::getSimpleInvoiceJson());
                        } elseif ($state === 'nda') {
                            $set('template_type', 'markdown');
                            $set('html_content', DemoTemplates::getNdaMarkdown());
                            $set('json_payload', DemoTemplates::getNdaJson());
                        } elseif ($state === 'blank') {
                            $set('html_content', '');
                            $set('json_payload', '{}');
                        }
                    })
                    ->columnSpanFull(),
                    
                Section::make('1. Template Definition')
                    ->columnSpan(1)
                    ->description('Provide the raw template file or markup.')
                    ->schema([
                        Select::make('template_type')
                            ->label('Template Format')
                            ->options([
                                'html_blade' => 'HTML / Laravel Blade',
                                'markdown' => 'Markdown',
                                'fillable_pdf' => 'Fillable PDF Document',
                            ])
                            ->required()
                            ->live(),
                        
                        CodeEditor::make('html_content')
                            ->label('Template Content')
                            ->visible(fn (Get $get) => in_array($get('template_type'), ['html_blade', 'markdown']))
                            ->required(fn (Get $get) => in_array($get('template_type'), ['html_blade', 'markdown']))
                            // Dynamically switch the language highlighting based on the selected type
                            ->language(fn (Get $get) => 
                                $get('template_type') === 'markdown' ? Language::Markdown : Language::Html)
                            ->columnSpanFull(),
                        
                        FileUpload::make('pdf_template')
                            ->label('Upload Blank Fillable PDF')
                            ->acceptedFileTypes(['application/pdf'])
                            ->visible(fn (Get $get) => $get('template_type') === 'fillable_pdf')
                            ->required(fn (Get $get) => $get('template_type') === 'fillable_pdf')
                            ->storeFiles(false),
                    ]),

                Section::make('2. Data Payload')
                    ->columnSpan(1)
                    ->description('Provide the JSON object to inject into the template placeholders.')
                    ->schema([
                        CodeEditor::make('json_payload')
                            ->label('JSON Data')
                            ->required()
                            ->language(Language::Json)
                            ->rules([
                                fn () => function (string $attribute, $value, Closure $fail) {
                                    json_decode($value);
                                    if (json_last_error() !== JSON_ERROR_NONE) {
                                        $fail('The payload must be a valid JSON string.');
                                    }
                                },
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('3. Output Configuration')
                    ->schema([
                        Radio::make('output_method')
                            ->label('Action')
                            ->options([
                                'stream_inline' => 'Preview (Inline Browser Stream)',
                                'stream_url' => 'Preview (Public Static Link)',
                                'download' => 'Force Download PDF',
                                'email' => 'Email to Address',
                            ])
                            ->inline()
                            ->live()
                            ->default('stream')
                            ->required(),
                            
                        TextInput::make('email_address')
                            ->label('Recipient Email')
                            ->email()
                            ->visible(fn (Get $get) => $get('output_method') === 'email')
                            ->required(fn (Get $get) => $get('output_method') === 'email'),
                    ]),
            ])
            ->statePath('data');
    }

    public function generatePdf(DocumentEngine $documentEngine)
    {
        $state = $this->form->getState();
        $payload = json_decode($state['json_payload'], true);

        try {
            $pdfContent = null;

            if (in_array($state['template_type'], ['html_blade', 'markdown'])) {
                // Call the service for markup
                $pdfContent = $documentEngine->generateFromMarkup(
                    $state['html_content'], 
                    $payload, 
                    $state['template_type']
                );
            } elseif ($state['template_type'] === 'fillable_pdf') {
                // Filament returns an array of files even if multiple() isn't set
                $uploadedFiles = $state['pdf_template'] ?? [];
                $uploadedFile = is_array($uploadedFiles) ? 
                    array_values($uploadedFiles)[0] ?? null : $uploadedFiles;

                if (!$uploadedFile) {
                    throw new \Exception('Please upload a fillable PDF template.');
                }
                
                // Extract the absolute path straight from the Livewire temporary file
                $fullPath = $uploadedFile->getRealPath();
                
                if (!file_exists($fullPath)) {
                    throw new \Exception("Uploaded template file not found at: {$fullPath}");
                }
                
                // Feed the engine
                $pdfContent = $documentEngine->generateFromFillablePdf($fullPath, $payload);
                
                // No manual Storage::delete() needed!
            }

            if (!$pdfContent) {
                throw new \Exception('No PDF document content was returned.');
            }

            return $this->handleOutput($pdfContent, $state);

        } catch (\Throwable $e) {
            // Catching Throwable instead of Exception prevents Blade parsing errors from crashing the page
            FilamentNotification::make()
                ->title('Rendering Error')
                ->body('Check your template syntax: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function handleOutput(string $pdfContent, array $state)
    {
        $filename = 'document_' . time() . '.pdf';

        // Method A: Force Download
        if ($state['output_method'] === 'download') {
            return response()->streamDownload(function () use ($pdfContent) {
                echo $pdfContent;
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        // Method B: Inline Browser Stream (No disk write required)
        if ($state['output_method'] === 'stream_inline') {
            return response()->stream(function () use ($pdfContent) {
                echo $pdfContent;
            }, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        }

        // Method C: Static Public URL (Writes to disk, requires storage:link)
        if ($state['output_method'] === 'stream_url') {
            $tempFilename = 'preview_' . Str::uuid() . '.pdf';
            
            try {
                if (!Storage::disk('public')->exists('temp_previews')) {
                    Storage::disk('public')->makeDirectory('temp_previews');
                }
                Storage::disk('public')->put('temp_previews/' . $tempFilename, $pdfContent);
            } catch (\Exception $writeException) {
                throw new \Exception('Storage error: ' . $writeException->getMessage());
            }

            $url = asset('storage/temp_previews/' . $tempFilename);
            $this->redirect($url);
            
            return;
        }

        // Method D: Email Routing
        if ($state['output_method'] === 'email') {
            $email = $state['email_address'];
            
            try {
                Mail::raw("Please find your generated document attached.", function ($message) use ($email, $pdfContent, $filename) {
                    $message->to($email)
                        ->subject("Saccharine Generated Document")
                        ->attachData($pdfContent, $filename, [
                            'mime' => 'application/pdf',
                        ]);
                });

                FilamentNotification::make()
                    ->title('Email Sent')
                    ->body('The generated PDF was sent to ' . $email)
                    ->success()
                    ->send();
            } catch (\Exception $mailException) {
                throw new \Exception('SMTP Error: ' . $mailException->getMessage());
            }
        }
    }
}