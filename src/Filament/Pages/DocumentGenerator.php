<?php

namespace Saccharine\Documents\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Get;
use Filament\Forms\Set;
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

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'System Utilities';
    protected static string $view = 'saccharine-documents::filament.pages.document-generator';
    protected static ?string $title = 'Document Generator';
    protected static ?string $navigationLabel = 'Document Generator';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('demo_preset')
                    ->label('Load Sample Template')
                    ->options([
                        'contractor' => 'Construction/Renovation Contract',
                        'blank' => 'Blank Template',
                    ])
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if ($state === 'contractor') {
                            $set('template_type', 'html_blade');
                            $set('html_content', DemoTemplates::getContractorHtml());
                            $set('json_payload', DemoTemplates::getContractorJson());
                        } elseif ($state === 'blank') {
                            $set('html_content', '');
                            $set('json_payload', '{}');
                        }
                    })
                    ->columnSpanFull(),
                    
                Grid::make(2)
                    ->schema([
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
                                
                                Textarea::make('html_content')
                                    ->label('Template Content')
                                    ->visible(fn (Get $get) => in_array($get('template_type'), ['html_blade', 'markdown']))
                                    ->required(fn (Get $get) => in_array($get('template_type'), ['html_blade', 'markdown']))
                                    ->extraInputAttributes(['style' => 'font-family: monospace;'])
                                    ->rows(15),
                                
                                FileUpload::make('pdf_template')
                                    ->label('Upload Blank Fillable PDF')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->visible(fn (Get $get) => $get('template_type') === 'fillable_pdf')
                                    ->required(fn (Get $get) => $get('template_type') === 'fillable_pdf'),
                            ]),

                        Section::make('2. Data Payload')
                            ->columnSpan(1)
                            ->description('Provide the JSON object to inject into the template placeholders.')
                            ->schema([
                                Textarea::make('json_payload')
                                    ->label('JSON Data')
                                    ->required()
                                    ->extraInputAttributes(['style' => 'font-family: monospace;'])
                                    ->rows(15)
                                    ->rules([
                                        fn () => function (string $attribute, $value, Closure $fail) {
                                            json_decode($value);
                                            if (json_last_error() !== JSON_ERROR_NONE) {
                                                $fail('The payload must be a valid JSON string.');
                                            }
                                        },
                                    ]),
                            ]),
                    ]),

                Section::make('3. Output Configuration')
                    ->schema([
                        Radio::make('output_method')
                            ->label('Action')
                            ->options([
                                'stream' => 'Stream to Browser (Preview)',
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
                $uploadedPath = $state['pdf_template'];
                
                if (!$uploadedPath) {
                    throw new \Exception('Please upload a fillable PDF template.');
                }
                
                $fullPath = storage_path('app/public/' . $uploadedPath);
                
                if (!file_exists($fullPath)) {
                    throw new \Exception('Uploaded template file not found.');
                }
                
                // Call the service for PDF mapping
                $pdfContent = $documentEngine->generateFromFillablePdf($fullPath, $payload);
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
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // Method B: Stream / Preview
        if ($state['output_method'] === 'stream') {
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

            // Redirect browser directly to the public preview link
            $this->redirect($url);
            
            return;
        }

        // Method C: Email Routing
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