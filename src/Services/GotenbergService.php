<?php

namespace Saccharine\Documents\Services;

use App\Interfaces\HtmlToPdfInterface;
use Illuminate\Support\Facades\Http;

class GotenbergService implements HtmlToPdfInterface
{
    public function convertHtml(string $html): string
    {
        $gotenbergUrl = env('GOTENBERG_URL', 'http://localhost:3000');

        $response = Http::attach(
            'files', $html, 'index.html'
        )->post($gotenbergUrl . '/forms/chromium/convert/html');

        if ($response->failed()) {
            throw new \Exception('Gotenberg rendering failed: ' . $response->body());
        }

        return $response->body();
    }
}