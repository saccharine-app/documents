<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Saccharine\Documents\Models\DocumentCategory;
use Saccharine\Documents\Models\DocumentTemplate;
use Saccharine\Documents\Models\DocumentTemplateVersion;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed a document category, template, and a published version so the
     * `activeVersion()` temporal logic and the Filament pages have data to
     * render inside Workbench and in integration tests.
     */
    public function run(): void
    {
        $category = DocumentCategory::firstOrCreate(
            ['name' => 'Workbench Contracts'],
        );

        $template = DocumentTemplate::firstOrCreate(
            [
                'document_category_id' => $category->id,
                'name' => 'Workbench NDA',
            ],
            ['is_active' => true],
        );

        $template->versions()->firstOrCreate(
            [
                'version_number' => 1,
            ],
            [
                'status' => 'published',
                'type' => 'blade_view',
                'view_name' => 'workbench::nda',
                'content' => ['title' => 'Workbench NDA', 'body' => 'Mutual non-disclosure.'],
                'effective_from' => null,
                'effective_until' => null,
            ],
        );
    }
}
