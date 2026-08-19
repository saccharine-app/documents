<?php

namespace Saccharine\Documents\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Placeholder;
use Livewire\Attributes\Url;
use Illuminate\Support\HtmlString;

class DocumentWizard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-sparkles';
    protected static \UnitEnum|string|null $navigationGroup = 'System Utilities';
    protected string $view = 'saccharine-documents::filament.pages.document-wizard';
    protected static ?string $title = 'Document Wizard';

    // Hold the form state
    public ?array $data = [];

    // Capture URL parameters automatically (e.g., ?profile=123&target_type=at_need)
    #[Url]
    public ?string $profile = null;
    
    #[Url]
    public ?string $target_type = null;

    #[Url]
    public ?string $target_id = null;

    public function mount(): void
    {
        // Initialize the form with data from the URL if present
        $this->form->fill([
            'target_type' => $this->target_type,
            'target_id' => $this->target_id,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Context')
                        ->description('Verify the target record')
                        ->schema([
                            Select::make('target_type')
                                ->label('Record Type')
                                ->options([
                                    'A' => 'A',
                                    'B' => 'B',
                                    'C' => 'C',
                                ])
                                ->required()
                                ->live(), // Re-render when changed so we can fetch specific records below
                        ]),

                    Step::make('Templates')
                        ->description('Select documents to generate')
                        ->schema([
                            // We will build a CheckboxList or Repeater here later
                            // to select DocumentTemplates and set their modes
                            Placeholder::make('template_selection_ui')
                                ->hiddenLabel()
                                ->content(new HtmlString('<p class="text-sm text-gray-500">Template selection UI will go here.</p>'))
                        ]),

                    Step::make('Output')
                        ->description('Routing and finalization')
                        ->schema([
                            Radio::make('output_preference')
                                ->label('What should we do with these documents?')
                                ->options([
                                    'save_to_case' => 'Save directly to Case File',
                                    'download_zip' => 'Download as ZIP archive',
                                    'email_to_family' => 'Email directly to family',
                                ])
                                ->default('save_to_case')
                                ->required(),
                        ]),
                ])
                ->submitAction(new HtmlString('<button type="submit" class="filament-button">Generate Documents</button>'))
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        // Instead of mapping to a specific column, map directly to the morph fields:
        $documentData = [
            'documentable_type' => $state['target_type'], // e.g., 'App\Models\Case'
            'documentable_id'   => $state['target_id'],
            // ... template mapping and other data
        ];

        // TODO: Create the DocumentGenerationRun record here
        // $run = DocumentGenerationRun::create([...]);
        
        // TODO: Dispatch a job or call the DocumentGenerationService
        
        // Notification::make()->title('Documents generating...')->success()->send();
        // $this->redirect('/path-to-case');
    }
}