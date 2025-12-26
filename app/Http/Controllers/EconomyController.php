<?php

namespace App\Http\Controllers;

use App\Services\LedgerService;
use Illuminate\View\View;

class EconomyController extends Controller
{
    public function dashboard(LedgerService $ledgerService): View
    {
        $customerBalance = $ledgerService->getTotalCustomerBalance();
        $supplierBalance = $ledgerService->getTotalSupplierBalance();
        $customerAging = $ledgerService->getCustomerAging();
        $supplierAging = $ledgerService->getSupplierAging();

        return view('economy.dashboard', compact(
            'customerBalance',
            'supplierBalance',
            'customerAging',
            'supplierAging'
        ));
    }

    public function accounting(LedgerService $ledgerService): View
    {
        $customerBalance = $ledgerService->getTotalCustomerBalance();
        $supplierBalance = $ledgerService->getTotalSupplierBalance();
        $customerAging = $ledgerService->getCustomerAging();
        $supplierAging = $ledgerService->getSupplierAging();

        return view('economy.accounting', compact(
            'customerBalance',
            'supplierBalance',
            'customerAging',
            'supplierAging'
        ));
    }

    public function incoming(): View
    {
        return view('economy.incoming');
    }

    public function vouchers(): View
    {
        return view('economy.vouchers');
    }

    public function customerLedger(): View
    {
        return view('economy.customer-ledger');
    }

    public function supplierLedger(): View
    {
        return view('economy.supplier-ledger');
    }

    public function reports(): View
    {
        return view('economy.reports');
    }

    public function vatReports(): View
    {
        return view('economy.vat-reports');
    }

    public function accounts(): View
    {
        return view('economy.accounts');
    }

    public function shareholders(): View
    {
        return view('economy.shareholders');
    }

    public function tax(): View
    {
        return view('economy.tax');
    }

    public function annualAccounts(): View
    {
        return view('economy.annual-accounts');
    }

    public function altinn(): View
    {
        return view('economy.altinn');
    }
}
