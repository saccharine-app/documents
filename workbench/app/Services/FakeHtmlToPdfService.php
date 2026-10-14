<?php

namespace Workbench\App\Services;

use Saccharine\Documents\Interfaces\HtmlToPdfInterface;

/**
 * In-memory HTML -> PDF engine used inside Workbench.
 *
 * Stands in for the real Gotenberg HTTP service so the DocumentEngine pipeline
 * can be exercised (and previewed via `workbench:serve`) without a running
 * Gotenberg container. Returns a deterministic pseudo-PDF byte string that
 * embeds a hash of the input so callers can assert the HTML reached the engine
 * unchanged.
 */
class FakeHtmlToPdfService implements HtmlToPdfInterface
{
    public function convertHtml(string $html): string
    {
        return "%PDF-FAKE\nsha1:" . sha1($html) . "\n" . $html;
    }
}
