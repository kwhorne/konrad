<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Altinn Environment
    |--------------------------------------------------------------------------
    |
    | This value determines which Altinn environment to use. Supported values
    | are "test" (tt02) and "production".
    |
    */
    'environment' => env('ALTINN_ENVIRONMENT', 'test'),

    /*
    |--------------------------------------------------------------------------
    | Maskinporten Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Maskinporten authentication. This is required for
    | machine-to-machine communication with Altinn APIs.
    |
    */
    'maskinporten' => [
        'issuer' => env('MASKINPORTEN_ISSUER'),
        'token_endpoint' => env('MASKINPORTEN_TOKEN_ENDPOINT'),
        'client_id' => env('MASKINPORTEN_CLIENT_ID'),
        'certificate_path' => env('MASKINPORTEN_CERTIFICATE_PATH'),
        'certificate_password' => env('MASKINPORTEN_CERTIFICATE_PASSWORD'),
        'scopes' => [
            'aksjonaerregister' => 'skatteetaten:aksjonaerregister',
            'skattemelding' => 'skatteetaten:skattemelding',
            'arsregnskap' => 'brreg:arsregnskap',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    |
    | The API endpoints for different environments.
    |
    */
    'endpoints' => [
        'test' => [
            'altinn' => 'https://platform.tt02.altinn.no',
            'maskinporten' => 'https://test.maskinporten.no',
            'skatteetaten' => 'https://api-test.skatteetaten.no',
            'brreg' => 'https://data.brreg.no/regnskapsregisteret/regnskap',
        ],
        'production' => [
            'altinn' => 'https://platform.altinn.no',
            'maskinporten' => 'https://maskinporten.no',
            'skatteetaten' => 'https://api.skatteetaten.no',
            'brreg' => 'https://data.brreg.no/regnskapsregisteret/regnskap',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Altinn App Services
    |--------------------------------------------------------------------------
    |
    | Configuration for the different Altinn app services.
    |
    */
    'services' => [
        'aksjonaerregister' => [
            'app_id' => 'skatteetaten/aksjonaerregisteret',
            'data_type' => 'aksjonaerregister',
            'deadline_month' => 1,
            'deadline_day' => 31,
        ],
        'skattemelding' => [
            'app_id' => 'skatteetaten/skattemelding-naering',
            'data_type' => 'skattemelding',
            'deadline_month' => 5,
            'deadline_day' => 31,
        ],
        'arsregnskap' => [
            'app_id' => 'regnskapsregisteret/arsregnskap',
            'data_type' => 'arsregnskap',
            'deadline_month' => 7,
            'deadline_day' => 31,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for retry logic when API calls fail.
    |
    */
    'retry' => [
        'max_attempts' => 3,
        'delay_ms' => 1000,
        'multiplier' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Settings for deadline notifications.
    |
    */
    'notifications' => [
        'deadline_reminders' => [30, 14, 7, 1], // Days before deadline
        'email_enabled' => env('ALTINN_EMAIL_NOTIFICATIONS', true),
    ],
];
