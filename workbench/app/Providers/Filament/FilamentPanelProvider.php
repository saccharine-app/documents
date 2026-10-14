<?php

namespace Workbench\App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saccharine\Documents\Filament\Pages\DocumentGenerator;
use Saccharine\Documents\Filament\Pages\DocumentWizard;

/**
 * A minimal Filament panel used to integration-test the Documents package's
 * Filament pages without a host application.
 *
 * Only the package's own pages are registered here (not the full
 * SaccharineDocumentsPlugin) so the panel stays free of the optional Tiptap
 * editor dependency that the DocumentTemplateResource requires.
 */
class FilamentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('workbench')
            ->path('workbench-admin')
            ->colors(['primary' => Color::Indigo])
            ->pages([
                Dashboard::class,
                DocumentGenerator::class,
                DocumentWizard::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
