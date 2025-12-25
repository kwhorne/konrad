<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SupplierInvoice;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LedgerService
{
    /**
     * Get customer ledger (accounts receivable) - aggregated from invoices.
     *
     * @param  int|null  $contactId  Filter by specific contact
     */
    public function getCustomerLedger(?int $contactId = null): Collection
    {
        $query = Invoice::with(['contact', 'invoiceStatus'])
            ->where('invoice_type', 'invoice')
            ->where('balance', '>', 0)
            ->orderBy('due_date');

        if ($contactId) {
            $query->where('contact_id', $contactId);
        }

        return $query->get();
    }

    /**
     * Get aging analysis for customer receivables.
     * Returns invoices grouped by: 0-30, 31-60, 61-90, 90+ days overdue.
     */
    public function getCustomerAging(): array
    {
        $today = Carbon::today();

        $invoices = Invoice::with('contact')
            ->where('invoice_type', 'invoice')
            ->where('balance', '>', 0)
            ->whereNotNull('due_date')
            ->get();

        $aging = [
            'current' => ['invoices' => collect(), 'total' => 0],      // Not yet due
            '1-30' => ['invoices' => collect(), 'total' => 0],        // 1-30 days overdue
            '31-60' => ['invoices' => collect(), 'total' => 0],       // 31-60 days overdue
            '61-90' => ['invoices' => collect(), 'total' => 0],       // 61-90 days overdue
            '90+' => ['invoices' => collect(), 'total' => 0],         // 90+ days overdue
        ];

        foreach ($invoices as $invoice) {
            $daysOverdue = $invoice->due_date->diffInDays($today, false);

            if ($daysOverdue <= 0) {
                $aging['current']['invoices']->push($invoice);
                $aging['current']['total'] += $invoice->balance;
            } elseif ($daysOverdue <= 30) {
                $aging['1-30']['invoices']->push($invoice);
                $aging['1-30']['total'] += $invoice->balance;
            } elseif ($daysOverdue <= 60) {
                $aging['31-60']['invoices']->push($invoice);
                $aging['31-60']['total'] += $invoice->balance;
            } elseif ($daysOverdue <= 90) {
                $aging['61-90']['invoices']->push($invoice);
                $aging['61-90']['total'] += $invoice->balance;
            } else {
                $aging['90+']['invoices']->push($invoice);
                $aging['90+']['total'] += $invoice->balance;
            }
        }

        return $aging;
    }

    /**
     * Get supplier ledger (accounts payable).
     *
     * @param  int|null  $contactId  Filter by specific contact
     */
    public function getSupplierLedger(?int $contactId = null): Collection
    {
        $query = SupplierInvoice::with(['contact'])
            ->where('balance', '>', 0)
            ->orderBy('due_date');

        if ($contactId) {
            $query->where('contact_id', $contactId);
        }

        return $query->get();
    }

    /**
     * Get aging analysis for supplier payables.
     * Returns invoices grouped by: 0-30, 31-60, 61-90, 90+ days overdue.
     */
    public function getSupplierAging(): array
    {
        $today = Carbon::today();

        $invoices = SupplierInvoice::with('contact')
            ->where('balance', '>', 0)
            ->whereNotNull('due_date')
            ->get();

        $aging = [
            'current' => ['invoices' => collect(), 'total' => 0],
            '1-30' => ['invoices' => collect(), 'total' => 0],
            '31-60' => ['invoices' => collect(), 'total' => 0],
            '61-90' => ['invoices' => collect(), 'total' => 0],
            '90+' => ['invoices' => collect(), 'total' => 0],
        ];

        foreach ($invoices as $invoice) {
            $daysOverdue = $invoice->due_date->diffInDays($today, false);

            if ($daysOverdue <= 0) {
                $aging['current']['invoices']->push($invoice);
                $aging['current']['total'] += $invoice->balance;
            } elseif ($daysOverdue <= 30) {
                $aging['1-30']['invoices']->push($invoice);
                $aging['1-30']['total'] += $invoice->balance;
            } elseif ($daysOverdue <= 60) {
                $aging['31-60']['invoices']->push($invoice);
                $aging['31-60']['total'] += $invoice->balance;
            } elseif ($daysOverdue <= 90) {
                $aging['61-90']['invoices']->push($invoice);
                $aging['61-90']['total'] += $invoice->balance;
            } else {
                $aging['90+']['invoices']->push($invoice);
                $aging['90+']['total'] += $invoice->balance;
            }
        }

        return $aging;
    }

    /**
     * Get total customer balance (total accounts receivable).
     */
    public function getTotalCustomerBalance(): float
    {
        return Invoice::where('invoice_type', 'invoice')
            ->where('balance', '>', 0)
            ->sum('balance');
    }

    /**
     * Get total supplier balance (total accounts payable).
     */
    public function getTotalSupplierBalance(): float
    {
        return SupplierInvoice::where('balance', '>', 0)->sum('balance');
    }

    /**
     * Get customer ledger summary grouped by contact.
     */
    public function getCustomerLedgerSummary(): Collection
    {
        return Invoice::with('contact')
            ->where('invoice_type', 'invoice')
            ->where('balance', '>', 0)
            ->selectRaw('contact_id, SUM(total) as total_invoiced, SUM(balance) as total_balance, COUNT(*) as invoice_count')
            ->groupBy('contact_id')
            ->get()
            ->map(function ($item) {
                return [
                    'contact' => $item->contact,
                    'total_invoiced' => $item->total_invoiced,
                    'total_balance' => $item->total_balance,
                    'invoice_count' => $item->invoice_count,
                ];
            });
    }

    /**
     * Get supplier ledger summary grouped by contact.
     */
    public function getSupplierLedgerSummary(): Collection
    {
        return SupplierInvoice::with('contact')
            ->where('balance', '>', 0)
            ->selectRaw('contact_id, SUM(total) as total_invoiced, SUM(balance) as total_balance, COUNT(*) as invoice_count')
            ->groupBy('contact_id')
            ->get()
            ->map(function ($item) {
                return [
                    'contact' => $item->contact,
                    'total_invoiced' => $item->total_invoiced,
                    'total_balance' => $item->total_balance,
                    'invoice_count' => $item->invoice_count,
                ];
            });
    }
}
