<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Create a voucher from an outgoing invoice.
     * Debit: 1500 Kundefordringer (total inkl. MVA)
     * Credit: 3000 Salgsinntekt (subtotal)
     * Credit: 2700 Utgående MVA (vat_total)
     */
    public function createInvoiceVoucher(Invoice $invoice): Voucher
    {
        return DB::transaction(function () use ($invoice) {
            $voucher = Voucher::create([
                'voucher_date' => $invoice->invoice_date,
                'description' => "Faktura {$invoice->invoice_number}",
                'voucher_type' => 'invoice',
                'reference_type' => Invoice::class,
                'reference_id' => $invoice->id,
                'created_by' => auth()->id(),
            ]);

            $sortOrder = 0;

            // Debit: Kundefordringer (1500)
            $customerReceivablesAccount = Account::where('account_number', '1500')->first();
            if ($customerReceivablesAccount) {
                VoucherLine::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $customerReceivablesAccount->id,
                    'description' => "Kundefordring {$invoice->customer_name}",
                    'debit' => $invoice->total,
                    'credit' => 0,
                    'contact_id' => $invoice->contact_id,
                    'sort_order' => $sortOrder++,
                ]);
            }

            // Credit: Salgsinntekt (3000)
            $revenueAccount = Account::where('account_number', '3000')->first();
            if ($revenueAccount) {
                VoucherLine::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $revenueAccount->id,
                    'description' => "Salg {$invoice->invoice_number}",
                    'debit' => 0,
                    'credit' => $invoice->subtotal - $invoice->discount_total,
                    'sort_order' => $sortOrder++,
                ]);
            }

            // Credit: Utgående MVA (2700)
            if ($invoice->vat_total > 0) {
                $vatAccount = Account::where('account_number', '2700')->first();
                if ($vatAccount) {
                    VoucherLine::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $vatAccount->id,
                        'description' => "MVA {$invoice->invoice_number}",
                        'debit' => 0,
                        'credit' => $invoice->vat_total,
                        'vat_amount' => $invoice->vat_total,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }

            $voucher->refresh();
            $voucher->post();

            return $voucher;
        });
    }

    /**
     * Create a voucher from a customer payment.
     * Debit: 1920 Bank (amount)
     * Credit: 1500 Kundefordringer (amount)
     */
    public function createPaymentVoucher(InvoicePayment $payment): Voucher
    {
        return DB::transaction(function () use ($payment) {
            $invoice = $payment->invoice;

            $voucher = Voucher::create([
                'voucher_date' => $payment->payment_date,
                'description' => "Innbetaling faktura {$invoice->invoice_number}",
                'voucher_type' => 'payment',
                'reference_type' => InvoicePayment::class,
                'reference_id' => $payment->id,
                'created_by' => auth()->id(),
            ]);

            $sortOrder = 0;

            // Debit: Bank (1920)
            $bankAccount = Account::where('account_number', '1920')->first();
            if ($bankAccount) {
                VoucherLine::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $bankAccount->id,
                    'description' => "Innbetaling {$payment->reference}",
                    'debit' => $payment->amount,
                    'credit' => 0,
                    'sort_order' => $sortOrder++,
                ]);
            }

            // Credit: Kundefordringer (1500)
            $customerReceivablesAccount = Account::where('account_number', '1500')->first();
            if ($customerReceivablesAccount) {
                VoucherLine::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $customerReceivablesAccount->id,
                    'description' => "Betaling fra {$invoice->customer_name}",
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'contact_id' => $invoice->contact_id,
                    'sort_order' => $sortOrder++,
                ]);
            }

            $voucher->refresh();
            $voucher->post();

            return $voucher;
        });
    }

    /**
     * Create a voucher from a supplier invoice.
     * Debit: Expense accounts (per line)
     * Debit: 2710 Inngående MVA (vat_total)
     * Credit: 2400 Leverandørgjeld (total)
     */
    public function createSupplierInvoiceVoucher(SupplierInvoice $invoice): Voucher
    {
        return DB::transaction(function () use ($invoice) {
            $voucher = Voucher::create([
                'voucher_date' => $invoice->invoice_date,
                'description' => "Leverandørfaktura {$invoice->invoice_number} fra {$invoice->contact->company_name}",
                'voucher_type' => 'supplier_invoice',
                'reference_type' => SupplierInvoice::class,
                'reference_id' => $invoice->id,
                'created_by' => auth()->id(),
            ]);

            $sortOrder = 0;

            // Debit: Expense accounts per line
            foreach ($invoice->lines as $line) {
                if ($line->account_id) {
                    $lineSubtotal = $line->quantity * $line->unit_price;
                    VoucherLine::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $line->account_id,
                        'description' => $line->description ?? "Linje fra {$invoice->invoice_number}",
                        'debit' => $lineSubtotal,
                        'credit' => 0,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }

            // Debit: Inngående MVA (2710)
            if ($invoice->vat_total > 0) {
                $vatAccount = Account::where('account_number', '2710')->first();
                if ($vatAccount) {
                    VoucherLine::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $vatAccount->id,
                        'description' => "Inngående MVA {$invoice->invoice_number}",
                        'debit' => $invoice->vat_total,
                        'credit' => 0,
                        'vat_amount' => $invoice->vat_total,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }

            // Credit: Leverandørgjeld (2400)
            $supplierLiabilityAccount = Account::where('account_number', '2400')->first();
            if ($supplierLiabilityAccount) {
                VoucherLine::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $supplierLiabilityAccount->id,
                    'description' => "Gjeld til {$invoice->contact->company_name}",
                    'debit' => 0,
                    'credit' => $invoice->total,
                    'contact_id' => $invoice->contact_id,
                    'sort_order' => $sortOrder++,
                ]);
            }

            $voucher->refresh();
            $voucher->post();

            // Link voucher to invoice
            $invoice->update(['voucher_id' => $voucher->id]);

            return $voucher;
        });
    }

    /**
     * Create a voucher from a supplier payment.
     * Debit: 2400 Leverandørgjeld (amount)
     * Credit: 1920 Bank (amount)
     */
    public function createSupplierPaymentVoucher(SupplierPayment $payment): Voucher
    {
        return DB::transaction(function () use ($payment) {
            $invoice = $payment->supplierInvoice;

            $voucher = Voucher::create([
                'voucher_date' => $payment->payment_date,
                'description' => "Utbetaling leverandørfaktura {$invoice->invoice_number}",
                'voucher_type' => 'supplier_payment',
                'reference_type' => SupplierPayment::class,
                'reference_id' => $payment->id,
                'created_by' => auth()->id(),
            ]);

            $sortOrder = 0;

            // Debit: Leverandørgjeld (2400)
            $supplierLiabilityAccount = Account::where('account_number', '2400')->first();
            if ($supplierLiabilityAccount) {
                VoucherLine::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $supplierLiabilityAccount->id,
                    'description' => "Betaling til {$invoice->contact->company_name}",
                    'debit' => $payment->amount,
                    'credit' => 0,
                    'contact_id' => $invoice->contact_id,
                    'sort_order' => $sortOrder++,
                ]);
            }

            // Credit: Bank (1920)
            $bankAccount = Account::where('account_number', '1920')->first();
            if ($bankAccount) {
                VoucherLine::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $bankAccount->id,
                    'description' => "Utbetaling {$payment->reference}",
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'sort_order' => $sortOrder++,
                ]);
            }

            $voucher->refresh();
            $voucher->post();

            // Link voucher to payment
            $payment->update(['voucher_id' => $voucher->id]);

            return $voucher;
        });
    }

    /**
     * Get account balance at a specific date.
     */
    public function getAccountBalance(Account $account, ?Carbon $date = null): float
    {
        $query = VoucherLine::where('account_id', $account->id)
            ->whereHas('voucher', function ($q) use ($date) {
                $q->where('is_posted', true);
                if ($date) {
                    $q->where('voucher_date', '<=', $date);
                }
            });

        $totals = $query->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')->first();

        $debit = $totals->total_debit ?? 0;
        $credit = $totals->total_credit ?? 0;

        // Debit-normal accounts (assets, expenses): balance = debit - credit
        // Credit-normal accounts (liabilities, equity, revenue): balance = credit - debit
        if (in_array($account->account_type, ['asset', 'expense'])) {
            return $debit - $credit;
        }

        return $credit - $debit;
    }

    /**
     * Get account statement (all transactions for an account in a period).
     */
    public function getAccountStatement(Account $account, Carbon $from, Carbon $to): Collection
    {
        return VoucherLine::where('account_id', $account->id)
            ->whereHas('voucher', function ($q) use ($from, $to) {
                $q->where('is_posted', true)
                    ->whereBetween('voucher_date', [$from, $to]);
            })
            ->with(['voucher', 'contact'])
            ->orderBy('created_at')
            ->get();
    }
}
