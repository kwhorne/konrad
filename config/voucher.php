<?php

return [
    /*
    |--------------------------------------------------------------------------
    | E-post konfigurasjon
    |--------------------------------------------------------------------------
    |
    | Innstillinger for automatisk henting av bilag fra e-post.
    |
    */
    'email' => [
        'enabled' => env('VOUCHER_EMAIL_ENABLED', false),
        'host' => env('VOUCHER_EMAIL_HOST', '{imap.example.com:993/imap/ssl}INBOX'),
        'username' => env('VOUCHER_EMAIL_USERNAME'),
        'password' => env('VOUCHER_EMAIL_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI-konfigurasjon
    |--------------------------------------------------------------------------
    |
    | Innstillinger for AI-tolkning av bilag med Prism.
    |
    */
    'ai' => [
        'provider' => env('VOUCHER_AI_PROVIDER', 'openai'),
        'model' => env('VOUCHER_AI_MODEL', 'gpt-4o'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Lagring
    |--------------------------------------------------------------------------
    |
    | Innstillinger for lagring av opplastede bilag.
    |
    */
    'storage' => [
        'disk' => env('VOUCHER_STORAGE_DISK', 'local'),
        'path' => 'incoming-vouchers',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tillatte filtyper
    |--------------------------------------------------------------------------
    |
    | Liste over MIME-typer som kan lastes opp som bilag.
    |
    */
    'allowed_mime_types' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Maks filstørrelse
    |--------------------------------------------------------------------------
    |
    | Maksimal filstørrelse i kilobytes.
    |
    */
    'max_file_size' => env('VOUCHER_MAX_FILE_SIZE', 10240), // 10MB
];
