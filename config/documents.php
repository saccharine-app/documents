<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Rendering Engines
    |--------------------------------------------------------------------------
    |
    | Here you can specify which engine should be used to render HTML to PDF
    | and which should be used to map data to fillable PDFs.
    |
    | Supported HTML engines: 'gotenberg'
    | Supported Fillable PDF engines: 'pdftk'
    |
    */
    'html_engine' => env('DOCUMENTS_HTML_ENGINE', 'gotenberg'),
    
    'fillable_pdf_engine' => env('DOCUMENTS_PDF_ENGINE', 'pdftk'),

    /*
    |--------------------------------------------------------------------------
    | Engine Specific Configurations
    |--------------------------------------------------------------------------
    */
    'engines' => [
        'gotenberg' => [
            'url' => env('GOTENBERG_URL', 'http://localhost:3000'),
        ],
        
        'pdftk' => [
            // Example: Path to binary if it's not in the global $PATH
            'binary_path' => env('PDFTK_BINARY_PATH', 'pdftk'), 
        ],
    ],

];