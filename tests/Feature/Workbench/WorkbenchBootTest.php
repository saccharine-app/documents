<?php

use Illuminate\Support\Facades\Route;
use Saccharine\Documents\DocumentsServiceProvider;
use Saccharine\Documents\Interfaces\FillablePdfInterface;
use Saccharine\Documents\Interfaces\HtmlToPdfInterface;
use Saccharine\Documents\Models\DocumentCategory;
use Saccharine\Documents\Models\DocumentTemplate;
use Saccharine\Documents\Services\DocumentEngine;
use Workbench\App\Services\FakeFillablePdfService;
use Workbench\App\Services\FakeHtmlToPdfService;
use Workbench\Database\Seeders\DatabaseSeeder;

/*
|--------------------------------------------------------------------------
| Workbench Integration Test
|--------------------------------------------------------------------------
|
| Boots the Documents package inside Orchestra Workbench with no host app.
| The WorkbenchServiceProvider swaps the real Gotenberg/pdftk engines for
| in-memory fakes, so the full DocumentEngine pipeline (Blade hydration -> PDF)
| is exercised without network access or external binaries.
|
*/

it('boots the package provider via testbench.yaml', function () {
    expect($this->app->getProviders(DocumentsServiceProvider::class))->not->toBeEmpty();
});

it('binds the workbench fake PDF engines instead of the real ones', function () {
    expect(app(HtmlToPdfInterface::class))->toBeInstanceOf(FakeHtmlToPdfService::class)
        ->and(app(FillablePdfInterface::class))->toBeInstanceOf(FakeFillablePdfService::class);
});

it('generates a PDF from a Blade template through the container-resolved engine', function () {
    $engine = app(DocumentEngine::class);

    $result = $engine->generateFromMarkup('<h1>Hello {{ $client }}</h1>', ['client' => 'Wayne']);

    // The fake engine prefixes a PDF header and embeds a hash of the hydrated HTML.
    expect($result)
        ->toStartWith("%PDF-FAKE")
        ->toContain(sha1('<h1>Hello Wayne</h1>'));
});

it('generates a PDF from markdown through the container-resolved engine', function () {
    $engine = app(DocumentEngine::class);

    $result = $engine->generateFromMarkup("# Contract\nClient: {{ \$client }}", ['client' => 'Acme'], 'markdown');

    expect($result)->toStartWith("%PDF-FAKE");
});

it('runs the package migrations and workbench seeder', function () {
    $this->seed(DatabaseSeeder::class);

    $category = DocumentCategory::where('name', 'Workbench Contracts')->first();
    expect($category)->not->toBeNull();

    $template = DocumentTemplate::where('name', 'Workbench NDA')->first();
    expect($template)->not->toBeNull()
        ->and($template->activeVersion())->not->toBeNull()
        ->and($template->activeVersion()->status)->toBe('published');
});

it('registers the Filament pages in the workbench panel', function () {
    // Filament is an optional host concern; the panel provider (and this test)
    // only apply in environments where `filament/filament` is installed.
    if (! class_exists(\Filament\Facades\Filament::class)) {
        $this->markTestSkipped('filament/filament is not installed.');
    }

    $pages = \Filament\Facades\Filament::getCurrentPanel()?->getPages() ?? [];

    expect(collect($pages)->map(fn ($p) => is_array($p) ? $p[0] : $p))
        ->toContain(
            \Saccharine\Documents\Filament\Pages\DocumentGenerator::class,
            \Saccharine\Documents\Filament\Pages\DocumentWizard::class,
        );
})->skip(fn () => ! class_exists(\Filament\Facades\Filament::class));
