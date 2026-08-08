<?php

namespace Saccharine\Documents\DTOs;

use Saccharine\Documents\Models\User;

class DocumentContext
{
    public function __construct(
        public readonly ?User $generatedBy = null,
        public readonly array $wizardInput = [],
        public readonly string $outputPreference = 'save_to_case',
        public readonly ?\DateTimeImmutable $generatedAt = null,
    ) {
        $this->generatedAt = $generatedAt ?? new \DateTimeImmutable();
    }
}