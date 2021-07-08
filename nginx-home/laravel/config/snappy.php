<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Snappy PDF / Image Configuration
    |--------------------------------------------------------------------------
    |
    | This option contains settings for PDF generation.
    |
    | Enabled:
    |    
    |    Whether to load PDF / Image generation.
    |
    | Binary:
    |    
    |    The file path of the wkhtmltopdf / wkhtmltoimage executable.
    |
    | Timout:
    |    
    |    The amount of time to wait (in seconds) before PDF / Image generation is stopped.
    |    Setting this to false disables the timeout (unlimited processing time).
    |
    | Options:
    |
    |    The wkhtmltopdf command options. These are passed directly to wkhtmltopdf.
    |    See https://wkhtmltopdf.org/usage/wkhtmltopdf.txt for all options.
    |
    | Env:
    |
    |    The environment variables to set while running the wkhtmltopdf process.
    |
    */
    // SDS, Changed from /usr/local/bin to /usr/bin for Docker Setup for binaries
    
    'pdf' => [
        'enabled' => true,
        'binary'  => env('WKHTML_PDF_BINARY', '/usr/bin/wkhtmltopdf'),
        'timeout' => false,
        'options' => array(

				'encoding'=> "UTF-8",
                'margin-top' => '.50in',
                'margin-bottom' => '.50in',
                'margin-left' => '.50in',
                'margin-right' => '.50in',
                'zoom' => 1,
                'print-media-type' => true,
                'lowquality' => false,
                'dpi' => 150,
                'no-images' => false,
                'grayscale' => false,
                'page-size' => 'Letter',
                'orientation' => 'Portrait',
                'grayscale' => false,
                'footer-center' => "Page [page] of [toPage], [date]",
                'enable-forms' => false,
                'enable-smart-shrinking' => true

            ),
        'env'     => [],
    ],
    
    'image' => [
        'enabled' => true,
        'binary'  => env('WKHTML_IMG_BINARY', '/usr/bin/wkhtmltoimage'),
        'timeout' => false,
        'options' => array(),
        'env'     => [],
    ],

];
