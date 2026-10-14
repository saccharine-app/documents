<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Workbench host application service provider for the Documents package.
 *
 * Stands in for the host app that would normally consume the package. The
 * DocumentsServiceProvider already binds the PDF engines based on config; here
 * we override those bindings with test-friendly fakes so integration tests and
 * the `workbench:serve` preview never hit a real Gotenberg server or shell out
 * to the pdftk binary.
 */
class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Swap the HTML -> PDF engine for an in-memory fake. Gotenberg normally
        // POSTs to an HTTP service; the fake returns deterministic bytes so the
        // DocumentEngine pipeline is exercised without network access.
        $this->app->bind(
            \Saccharine\Documents\Interfaces\HtmlToPdfInterface::class,
            \Workbench\App\Services\FakeHtmlToPdfService::class
        );

        $this->app->bind(
            \Saccharine\Documents\Interfaces\FillablePdfInterface::class,
            \Workbench\App\Services\FakeFillablePdfService::class
        );
    }
}
