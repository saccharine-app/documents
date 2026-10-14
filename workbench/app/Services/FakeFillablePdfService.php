<?php

namespace Workbench\App\Services;

use Saccharine\Documents\Interfaces\FillablePdfInterface;

/**
 * In-memory fillable-PDF engine used inside Workbench.
 *
 * Stands in for the real pdftk binary so fillable-PDF generation can be
 * exercised without shelling out. Records the last fill so tests can assert
 * the payload that reached the engine.
 */
class FakeFillablePdfService implements FillablePdfInterface
{
    /** @var array{templatePath: string, payload: array<string, mixed>}|null */
    public ?array $lastFill = null;

    public function fill(string $templatePath, array $payload): string
    {
        $this->lastFill = ['templatePath' => $templatePath, 'payload' => $payload];

        return "%PDF-FAKE-FILL\n" . $templatePath . "\n" . json_encode($payload);
    }
}
