<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    |
    | This file contains the company information used in PDF documents
    | like invoices, quotes, and orders.
    |
    */

    'name' => env('COMPANY_NAME', 'Ditt Firma AS'),
    'address' => env('COMPANY_ADDRESS', 'Gateadresse 1'),
    'postal_code' => env('COMPANY_POSTAL_CODE', '0000'),
    'city' => env('COMPANY_CITY', 'Oslo'),
    'country' => env('COMPANY_COUNTRY', 'Norge'),
    'org_number' => env('COMPANY_ORG_NUMBER', '999 999 999'),
    'bank_account' => env('COMPANY_BANK_ACCOUNT', '1234.56.78901'),
    'email' => env('COMPANY_EMAIL', 'post@dittfirma.no'),
    'phone' => env('COMPANY_PHONE', '+47 00 00 00 00'),
    'website' => env('COMPANY_WEBSITE', 'www.dittfirma.no'),
    'logo_path' => env('COMPANY_LOGO_PATH', null),

];
