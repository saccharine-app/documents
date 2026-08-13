<?php

namespace Saccharine\Documents\Services;

use Saccharine\Documents\Models\DocumentTemplateVersion;
use Saccharine\Documents\Models\Document;
use Saccharine\Documents\DTOs\DocumentContext;
use Illuminate\Database\Eloquent\Model;

class DocumentManager
{
    /**
     * Inject the format-agnostic generation engine.
     */
    public function __construct(
        protected DocumentEngine $engine
    ) {}

    /**
     * Create a payload for a document template version using the associated mapper class.
     * (Extracted from the original DocumentGeneratorService)
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

    /**
     * ORCHESTRATION STUB: The primary entry point for the host application.
     */
    public function generateAndStore(
        Model $targetModel, 
        string $templateId, 
        DocumentContext $context
    ): Document {
        // TODO: 1. Temporal Resolution
        // Load the DocumentTemplate by ID and find the active DocumentTemplateVersion
        // based on the current timestamp or the creation date of the $targetModel.

        // TODO: 2. Hydration
        // $payload = $this->buildPayload($activeVersion, $targetModel, $context);
        
        // TODO: 3. Rendering
        // Pass $activeVersion->content and $payload to $this->engine->generateFromMarkup()
        
        // TODO: 4. Storage
        // Save the raw PDF/HTML output to the host application's default storage disk.
        
        // TODO: 5. The Ledger
        // Create a new Saccharine\Documents\Models\Document record mapping the file path, 
        // the $activeVersion->id, and utilizing $targetModel->morphMany() to attach it.
        // Return the resulting Document model.

        throw new \Exception('DocumentManager::generateAndStore is not yet implemented.');
    }
    
    /**
     * BUNDLING STUB: Handles generating multiple documents into an Envelope.
     */
    public function generateEnvelope(
        Model $targetModel, 
        array $templateIds, 
        DocumentContext $context
    ) {
        // TODO: Create a DocumentEnvelope record.
        // TODO: Loop through $templateIds, calling generateAndStore() for each.
        // TODO: Associate each generated Document with the Envelope.
        // TODO: Dispatch to the designated e-sign driver if configured.
    }
}