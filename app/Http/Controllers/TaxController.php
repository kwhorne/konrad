<?php

namespace App\Http\Controllers;

use App\Services\TaxCalculationService;

class TaxController extends Controller
{
    public function __construct(private TaxCalculationService $taxService) {}

    /**
     * Display tax returns overview.
     */
    public function returns()
    {
        return view('tax.returns');
    }

    /**
     * Display tax adjustments.
     */
    public function adjustments()
    {
        return view('tax.adjustments');
    }

    /**
     * Display deferred tax items.
     */
    public function deferred()
    {
        return view('tax.deferred');
    }

    /**
     * Display depreciation schedules.
     */
    public function depreciation()
    {
        return view('tax.depreciation');
    }
}
