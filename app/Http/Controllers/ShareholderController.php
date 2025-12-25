<?php

namespace App\Http\Controllers;

use App\Services\ShareholderService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShareholderController extends Controller
{
    public function __construct(private ShareholderService $shareholderService) {}

    /**
     * Display shareholder dashboard.
     */
    public function index()
    {
        $summary = $this->shareholderService->getOwnershipSummary();

        return view('shareholders.index', compact('summary'));
    }

    /**
     * Display shareholder register.
     */
    public function register(Request $request)
    {
        $atDate = $request->input('at_date')
            ? Carbon::parse($request->input('at_date'))
            : Carbon::now();

        $shareholders = $this->shareholderService->getShareholderRegisterAtDate($atDate);

        return view('shareholders.register', compact('shareholders', 'atDate'));
    }

    /**
     * Display share classes.
     */
    public function classes()
    {
        return view('shareholders.classes');
    }

    /**
     * Display transactions.
     */
    public function transactions(Request $request)
    {
        $fromDate = $request->input('from_date')
            ? Carbon::parse($request->input('from_date'))
            : Carbon::now()->startOfYear();

        $toDate = $request->input('to_date')
            ? Carbon::parse($request->input('to_date'))
            : Carbon::now();

        $transactions = $this->shareholderService->getTransactionHistory($fromDate, $toDate);

        return view('shareholders.transactions', compact('transactions', 'fromDate', 'toDate'));
    }

    /**
     * Display dividends.
     */
    public function dividends(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $dividends = $this->shareholderService->getDividendHistory($year);

        return view('shareholders.dividends', compact('dividends', 'year'));
    }

    /**
     * Display shareholder reports.
     */
    public function reports()
    {
        return view('shareholders.reports');
    }

    /**
     * Display capital changes report.
     */
    public function capitalChanges(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $changes = $this->shareholderService->getCapitalChanges($year);

        return view('shareholders.capital-changes', compact('changes', 'year'));
    }
}
