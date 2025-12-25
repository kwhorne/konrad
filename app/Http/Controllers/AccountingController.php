<?php

namespace App\Http\Controllers;

use App\Services\LedgerService;

class AccountingController extends Controller
{
    public function index(LedgerService $ledgerService)
    {
        $customerBalance = $ledgerService->getTotalCustomerBalance();
        $supplierBalance = $ledgerService->getTotalSupplierBalance();
        $customerAging = $ledgerService->getCustomerAging();
        $supplierAging = $ledgerService->getSupplierAging();

        return view('accounting.index', compact(
            'customerBalance',
            'supplierBalance',
            'customerAging',
            'supplierAging'
        ));
    }

    public function customerLedger()
    {
        return view('accounting.customer-ledger');
    }

    public function supplierLedger()
    {
        return view('accounting.supplier-ledger');
    }

    public function vouchers()
    {
        return view('accounting.vouchers');
    }
}
