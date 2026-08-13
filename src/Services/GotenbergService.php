<?php

namespace Saccharine\Documents\Services;

use Saccharine\Documents\Interfaces\HtmlToPdfInterface;
use Illuminate\Support\Facades\Http;

class GotenbergService implements HtmlToPdfInterface
{
    public function convertHtml(string $html): string
    {
        // Pull safely from the merged config
        $gotenbergUrl = config('documents.engines.gotenberg.url', 'http://localhost:3000');

        $response = Http::attach(
            'files', $html, 'index.html'
        )->post($gotenbergUrl . '/forms/chromium/convert/html');

        if ($response->failed()) {
            throw new \Exception('Gotenberg rendering failed: ' . $response->body());
        }

        return $response->body();
    }
}