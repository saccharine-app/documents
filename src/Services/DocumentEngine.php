<?php

namespace Saccharine\Documents\Services;

use Saccharine\Documents\Interfaces\HtmlToPdfInterface;
use Saccharine\Documents\Interfaces\FillablePdfInterface;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

class DocumentEngine
{
    /**
     * Inject the specific rendering interfaces.
     */
    public function __construct(
        protected HtmlToPdfInterface $htmlEngine,
        protected FillablePdfInterface $fillableEngine
    ) {}

    /**
     * Hydrate markup with data and compile it into a PDF string.
     */
    public function generateFromMarkup(string $content, array $payload, string $format = 'html_blade'): string
    {
        $compiledHtml = '';

        if ($format === 'html_blade') {
            // Hydrate the Blade template with the payload
            $compiledHtml = Blade::render($content, $payload);
        } elseif ($format === 'markdown') {
            // Allow Blade variables inside the Markdown before parsing to HTML
            $compiledBlade = Blade::render($content, $payload);
            $compiledHtml = Str::markdown($compiledBlade);
        }

        // Pass the hydrated string to the rendering engine (e.g., Gotenberg)
        return $this->htmlEngine->convertHtml($compiledHtml);
    }

    /**
     * Map data to a fillable PDF using the injected engine (e.g., PDFtk).
     */
    public function generateFromFillablePdf(string $templatePath, array $payload): string
    {
        return $this->fillableEngine->fill($templatePath, $payload);
    }
}