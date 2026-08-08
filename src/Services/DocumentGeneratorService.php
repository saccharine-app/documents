<?php

namespace Saccharine\Documents\Services;

use App\Interfaces\HtmlToPdfInterface;
use App\Interfaces\FillablePdfInterface;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

class DocumentGeneratorService
{
    /**
     * @param HtmlToPdfInterface $htmlEngine
     * @param FillablePdfInterface $fillableEngine
     */
    public function __construct(
        protected HtmlToPdfInterface $htmlEngine,
        protected FillablePdfInterface $fillableEngine
    ) {}

    /**
     * Generate a PDF using the Gotenberg Chromium microservice.
     */
    public function generateFromMarkup(string $content, array $payload, string $format = 'html_blade'): string
    {
        $compiledHtml = '';

        if ($format === 'html_blade') {
            $compiledHtml = Blade::render($content, $payload);
        } elseif ($format === 'markdown') {
            $compiledBlade = Blade::render($content, $payload);
            $compiledHtml = Str::markdown($compiledBlade);
        }

        return $this->htmlEngine->convertHtml($compiledHtml);
    }

    /**
     * Map data to a fillable PDF using PDFtk.
     */
    public function generateFromFillablePdf(string $templatePath, array $payload): string
    {
        return $this->fillableEngine->fill($templatePath, $payload);
    }
}