# Saccharine Documents

A modular, format-agnostic document hydration and management engine for Laravel.

This package provides a flexible pipeline for compiling dynamic templates—such as Laravel Blade, Markdown, or fillable PDFs—with structured data payloads. It supports headless generation for background automation (e.g., process execution engines) as well as rich UI integration for Filament admin panels.

*Status: v0.1.1-alpha. This package is currently in active early development. The database schema, APIs, and scaffolding commands are subject to change without notice. It is not yet recommended for production environments*

---

## Features

* **Multiple Template Formats:** Render PDFs from HTML/Laravel Blade, Markdown, or fillable PDF templates.
* **Format-Agnostic Engine:** Decouples template hydration from output rendering (e.g., Gotenberg, PDFtk, or raw text streams).
* **Polymorphic Model Support:** Attach generated document artifacts to any record in your application.
* **Temporal Template Versioning:** Maintain strict compliance and audit trails by versioning layouts and logic across time.
* **Filament Integration:** Includes a ready-to-use live document generator and testing panel for Filament v3.

---

## Architecture Overview

The package strictly enforces a separation between rendering mechanics and business domain logic:

1. **`DocumentEngine`:** A pure, lightweight pipeline that takes template strings or files, hydrates them with data payloads, and passes them to configured rendering drivers.
2. **`DocumentManager`:** The database-aware ledger that handles temporal version resolution, data mapping, and polymorphic persistence.

---

## Installation

You can install the package via Composer:

```bash
composer require saccharine/documents
```

Publish the package configuration:

```bash
php artisan vendor:publish --tag="documents-config"
```

If your host application uses Filament v3, register the plugin in your Panel Provider:

```php
use Saccharine\Documents\Filament\SaccharineDocumentsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(SaccharineDocumentsPlugin::make());
}
```

## Development & Testing

This package uses Pest PHP for unit and integration testing.

To run the test suite:

```bash
vendor/bin/pest
```

## **License**

This package is open-source software licensed under the [MIT License](https://opensource.org/license/mit).