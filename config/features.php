<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    |
    | This file contains feature toggles that can be enabled or disabled
    | via environment variables. This allows you to control which modules
    | are available in the application.
    |
    */

    'contracts' => env('CONTRACTS_ENABLED', false),
    'assets' => env('ASSETS_ENABLED', false),
    'contacts' => env('CONTACTS_ENABLED', false),
    'products' => env('PRODUCTS_ENABLED', false),
    'projects' => env('PROJECTS_ENABLED', false),
    'work_orders' => env('WORK_ORDERS_ENABLED', false),
    'sales' => env('SALES_ENABLED', false),
    'shareholders' => env('SHAREHOLDERS_ENABLED', false),
    'altinn' => env('ALTINN_ENABLED', false),
    'inventory' => env('INVENTORY_ENABLED', false),
    'payroll' => env('PAYROLL_ENABLED', false),

];
