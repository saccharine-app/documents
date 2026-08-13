<?php

namespace Saccharine\Documents\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Saccharine\Documents\Filament\Pages\DocumentGenerator;
use Saccharine\Documents\Filament\Pages\DocumentWizard;
use Saccharine\Documents\Filament\Resources\DocumentTemplateResource;

class SaccharineDocumentsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'saccharine-documents';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                DocumentTemplateResource::class,
            ])
            ->pages([
                DocumentGenerator::class,
                DocumentWizard::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        // 
    }

    public static function make(): static
    {
        return app(static::class);
    }
}