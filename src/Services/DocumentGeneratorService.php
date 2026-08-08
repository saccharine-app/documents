<?php

namespace Saccharine\Documents\Services;

use Saccharine\Documents\Interfaces\HtmlToPdfInterface;
use Saccharine\Documents\Interfaces\FillablePdfInterface;
use Saccharine\Documents\Models\DocumentTemplateVersion;
use Saccharine\Documents\DTOs\DocumentContext;

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

    /**
     * Create a payload for a document template version using the associated mapper class.
     */
    public function buildPayload(
        DocumentTemplateVersion $version, 
        Model $targetModel, 
        DocumentContext $context
    ): array {
        $mapperClass = $version->dto_class;

        if (!class_exists($mapperClass)) {
            throw new \Exception("The mapper class [{$mapperClass}] defined on the template version does not exist.");
        }

        // Instantiate the resource, inject the context, and resolve the array
        $mapper = (new $mapperClass($targetModel))->withContext($context);
        
        return $mapper->resolve();
    }
}