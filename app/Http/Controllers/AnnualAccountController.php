<?php

namespace App\Http\Controllers;

use App\Models\AnnualAccount;
use App\Services\AnnualAccountService;

class AnnualAccountController extends Controller
{
    public function __construct(private AnnualAccountService $service) {}

    /**
     * Display annual accounts overview.
     */
    public function index()
    {
        return view('annual-accounts.index');
    }

    /**
     * Display notes for an annual account.
     */
    public function notes(AnnualAccount $annualAccount)
    {
        return view('annual-accounts.notes', compact('annualAccount'));
    }

    /**
     * Display cash flow statement for an annual account.
     */
    public function cashFlow(AnnualAccount $annualAccount)
    {
        return view('annual-accounts.cash-flow', compact('annualAccount'));
    }
}
