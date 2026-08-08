<?php

namespace Saccharine\Documents\Filament\Resources\DocumentTemplateResource\Pages;

use Saccharine\Documents\Filament\Resources\DocumentTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentTemplate extends EditRecord
{
    protected static string $resource = DocumentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
