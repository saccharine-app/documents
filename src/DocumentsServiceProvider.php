<?php

namespace Saccharine\Documents;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Saccharine\Documents\Interfaces\HtmlToPdfInterface;
use Saccharine\Documents\Interfaces\FillablePdfInterface;
use Saccharine\Documents\Services\GotenbergService;
use Saccharine\Documents\Services\PdftkService;

class DocumentsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/documents.php', 'documents');

        // Bind the HTML to PDF Engine based on configuration
        $this->app->bind(HtmlToPdfInterface::class, function ($app) {
            $engine = config('documents.html_engine');

            return match ($engine) {
                'gotenberg' => new GotenbergService(),
                // 'dompdf' => new DomPdfService(), // Easy to add later!
                default => throw new \Exception("Unsupported HTML to PDF engine: {$engine}"),
            };
        });

        // Bind the Fillable PDF Engine based on configuration
        $this->app->bind(FillablePdfInterface::class, function ($app) {
            $engine = config('documents.fillable_pdf_engine');

            return match ($engine) {
                'pdftk' => new PdftkService(),
                default => throw new \Exception("Unsupported Fillable PDF engine: {$engine}"),
            };
        });
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Load the migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Allow the host application to publish the config file
        $this->publishes([
            __DIR__.'/../config/documents.php' => config_path('documents.php'),
        ], 'documents-config');

        // Register package views (e.g., resources/views mapped to 'saccharine-documents')
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'saccharine-documents');

        // Conditionally load Filament resources and pages
        if (class_exists(\Filament\FilamentServiceProvider::class)) {
            $this->registerFilamentComponents();
        }
    }

    /**
     * Register Filament UI components only if the host app uses Filament.
     */
    protected function registerFilamentComponents(): void
    {
        // In Filament v3, a host app's PanelProvider usually registers packages via a Plugin class.
        // However, for the Service Provider to register Livewire components natively 
        // to bypass needing a Plugin registration in the host app, Livewire can be used directly here:
        
        \Livewire\Livewire::component(
            'saccharine.documents.pages.document-generator', 
            \Saccharine\Documents\Filament\Pages\DocumentGenerator::class
        );
        
        \Livewire\Livewire::component(
            'saccharine.documents.pages.document-wizard', 
            \Saccharine\Documents\Filament\Pages\DocumentWizard::class
        );

        // Note: For full Filament v3 integration, it is standard to create a `DocumentsPlugin` 
        // that implements `Filament\Contracts\Plugin` where the host app registers resources.
        // For immediate demoability, we ensure the classes exist and Livewire knows about them.
    }
}