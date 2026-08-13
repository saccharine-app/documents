<?php

use Saccharine\Documents\Services\DocumentEngine;
use Saccharine\Documents\Interfaces\HtmlToPdfInterface;
use Saccharine\Documents\Interfaces\FillablePdfInterface;

it('hydrates blade templates correctly before sending to the html engine', function () {
    // Arrange: Mock the engines
    $htmlEngineMock = Mockery::mock(HtmlToPdfInterface::class);
    $fillableEngineMock = Mockery::mock(FillablePdfInterface::class);

    // We expect Gotenberg to receive the successfully interpolated string
    $htmlEngineMock->shouldReceive('convertHtml')
        ->once()
        ->with('<h1>Hello Wayne Enterprises</h1>')
        ->andReturn('dummy-pdf-binary-data');

    $engine = new DocumentEngine($htmlEngineMock, $fillableEngineMock);

    // Act: Pass the raw template and payload
    $template = '<h1>Hello {{ $client }}</h1>';
    $payload = ['client' => 'Wayne Enterprises'];
    
    $result = $engine->generateFromMarkup($template, $payload, 'html_blade');

    // Assert
    expect($result)->toBe('dummy-pdf-binary-data');
});

it('hydrates and parses markdown correctly before sending to the html engine', function () {
    $htmlEngineMock = Mockery::mock(HtmlToPdfInterface::class);
    $fillableEngineMock = Mockery::mock(FillablePdfInterface::class);

    // Str::markdown() wraps output in <p> tags and converts # to <h1>
    $expectedHtml = "<h1>Contract</h1>\n<p>Client: Wayne Enterprises</p>\n";

    $htmlEngineMock->shouldReceive('convertHtml')
        ->once()
        ->with($expectedHtml)
        ->andReturn('dummy-pdf-binary-data');

    $engine = new DocumentEngine($htmlEngineMock, $fillableEngineMock);

    $template = "# Contract\nClient: {{ \$client }}";
    $payload = ['client' => 'Wayne Enterprises'];
    
    $result = $engine->generateFromMarkup($template, $payload, 'markdown');

    expect($result)->toBe('dummy-pdf-binary-data');
});