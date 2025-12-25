<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index()
    {
        return view('reports.index');
    }

    public function generalLedger(Request $request)
    {
        $fromDate = $request->input('from_date')
            ? Carbon::parse($request->input('from_date'))
            : Carbon::now()->startOfYear();

        $toDate = $request->input('to_date')
            ? Carbon::parse($request->input('to_date'))
            : Carbon::now();

        $accountId = $request->input('account_id');

        $ledger = $this->reportService->getGeneralLedger($fromDate, $toDate, $accountId);
        $accounts = Account::active()->ordered()->get();

        return view('reports.general-ledger', compact('ledger', 'accounts', 'fromDate', 'toDate', 'accountId'));
    }

    public function voucherJournal(Request $request)
    {
        $fromDate = $request->input('from_date')
            ? Carbon::parse($request->input('from_date'))
            : Carbon::now()->startOfYear();

        $toDate = $request->input('to_date')
            ? Carbon::parse($request->input('to_date'))
            : Carbon::now();

        $vouchers = $this->reportService->getVoucherJournal($fromDate, $toDate);

        return view('reports.voucher-journal', compact('vouchers', 'fromDate', 'toDate'));
    }

    public function trialBalance(Request $request)
    {
        $atDate = $request->input('at_date')
            ? Carbon::parse($request->input('at_date'))
            : Carbon::now();

        $balances = $this->reportService->getTrialBalance($atDate);

        $totalDebit = $balances->sum('debit');
        $totalCredit = $balances->sum('credit');

        return view('reports.trial-balance', compact('balances', 'atDate', 'totalDebit', 'totalCredit'));
    }

    public function incomeStatement(Request $request)
    {
        $fromDate = $request->input('from_date')
            ? Carbon::parse($request->input('from_date'))
            : Carbon::now()->startOfYear();

        $toDate = $request->input('to_date')
            ? Carbon::parse($request->input('to_date'))
            : Carbon::now();

        $statement = $this->reportService->getIncomeStatement($fromDate, $toDate);

        return view('reports.income-statement', compact('statement', 'fromDate', 'toDate'));
    }

    public function balanceSheet(Request $request)
    {
        $atDate = $request->input('at_date')
            ? Carbon::parse($request->input('at_date'))
            : Carbon::now();

        $balance = $this->reportService->getBalanceSheet($atDate);

        return view('reports.balance-sheet', compact('balance', 'atDate'));
    }
}
