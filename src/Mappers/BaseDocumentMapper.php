<?php

namespace Saccharine\Documents\Mappers;

use Illuminate\Http\Resources\Json\JsonResource;
use Saccharine\Documents\DTOs\DocumentContext;

abstract class BaseDocumentMapper extends JsonResource
{
    protected ?DocumentContext $context = null;

    /**
     * Inject the generation context into the mapper.
     */
    public function withContext(DocumentContext $context): self
    {
        $this->context = $context;
        
        return $this;
    }

    /**
     * Enforce that all concrete mappers return a strictly typed array.
     */
    abstract public function toArray($request = null): array;
}