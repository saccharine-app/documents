<?php

namespace Saccharine\Documents\Filament\Resources;

use App\Filament\Resources\DocumentTemplateResource\Pages;
use App\Filament\Resources\DocumentTemplateResource\RelationManagers;
use App\Models\DocumentTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Get;
// Include the TiptapEditor and TiptapOutput classes
use FilamentTiptapEditor\TiptapEditor;
use FilamentTiptapEditor\Enums\TiptapOutput; 

use App\Models\DocumentCategory;

class DocumentTemplateResource extends Resource
{
    protected static ?string $model = DocumentTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = 'System Utilities';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('Is Active')
                    ->default(true),
                Forms\Components\Select::make('type')
                    ->options([
                        'tiptap_block' => 'Tiptap Block',
                        'fillable_pdf' => 'Fillable PDF',
                        'blade_view' => 'Blade View',
                    ])
                    ->required()
                    ->searchable()
                    ->live(),
                Forms\Components\Select::make('document_category_id')
                    ->options(DocumentCategory::pluck('name', 'id')),
                TiptapEditor::make('content')
                    ->profile('default') // Use the default profile
                    ->required()
                    ->visible(fn (Get $get) => $get('type') === 'tiptap_block')
                    ->output(TiptapOutput::Json), // Store the content as JSON
                Forms\Components\FileUpload::make('file_path')
                    ->label('Upload Fillable PDF')
                    ->directory('document_templates')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240) // 10 MB
                    ->visible(fn (Get $get) => $get('type') === 'fillable_pdf'),
                Forms\Components\TextInput::make('view_name')
                    ->label('View Name')
                    ->visible(fn (Get $get) => $get('type') === 'blade_view'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentTemplates::route('/'),
            'create' => Pages\CreateDocumentTemplate::route('/create'),
            'edit' => Pages\EditDocumentTemplate::route('/{record}/edit'),
        ];
    }
}
