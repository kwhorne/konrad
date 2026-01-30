<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Account Classes (Kontoklasser)
    |--------------------------------------------------------------------------
    |
    | Defines the account classes used in the Norwegian standard chart of accounts.
    | Each class maps to a name and its role in financial reports.
    |
    | report_category options:
    |   - 'balance_sheet_asset' (Balanse: Eiendeler)
    |   - 'balance_sheet_equity_liability' (Balanse: Egenkapital og gjeld)
    |   - 'income_revenue' (Resultat: Inntekter)
    |   - 'income_cost_of_goods' (Resultat: Varekostnad)
    |   - 'income_payroll' (Resultat: Lønn og personal)
    |   - 'income_depreciation' (Resultat: Avskrivninger)
    |   - 'income_operating_other' (Resultat: Andre driftskostnader)
    |   - 'income_financial' (Resultat: Finansposter)
    |
    */

    'account_classes' => [
        '1' => [
            'name' => 'Eiendeler',
            'report_category' => 'balance_sheet_asset',
        ],
        '2' => [
            'name' => 'Egenkapital og gjeld',
            'report_category' => 'balance_sheet_equity_liability',
        ],
        '3' => [
            'name' => 'Salgsinntekter',
            'report_category' => 'income_revenue',
        ],
        '4' => [
            'name' => 'Varekostnad',
            'report_category' => 'income_cost_of_goods',
        ],
        '5' => [
            'name' => 'Lønn og personal',
            'report_category' => 'income_payroll',
        ],
        '6' => [
            'name' => 'Avskrivninger',
            'report_category' => 'income_depreciation',
        ],
        '7' => [
            'name' => 'Andre driftskostnader',
            'report_category' => 'income_operating_other',
        ],
        '8' => [
            'name' => 'Finansposter',
            'report_category' => 'income_financial',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Categories Grouping
    |--------------------------------------------------------------------------
    |
    | Defines which account classes belong to each report category.
    | This is derived from account_classes but provided for convenience.
    |
    */

    'report_categories' => [
        'balance_sheet_asset' => ['1'],
        'balance_sheet_equity_liability' => ['2'],
        'income_revenue' => ['3'],
        'income_cost_of_goods' => ['4'],
        'income_payroll' => ['5'],
        'income_depreciation' => ['6'],
        'income_operating_other' => ['7'],
        'income_financial' => ['8'],
    ],
];
