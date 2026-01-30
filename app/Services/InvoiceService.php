<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoicePayment;
use App\Models\InvoiceStatus;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * Default payment terms in days when not specified.
     */
    public const DEFAULT_PAYMENT_TERMS_DAYS = 14;

    /**
     * Default reminder days after due date.
     */
    public const DEFAULT_REMINDER_DAYS = 14;

    public function __construct(
        private DocumentTotalsService $totalsService
    ) {}

    /**
     * Calculate the due date based on invoice date and payment terms.
     */
    public function calculateDueDate(Carbon $invoiceDate, int $paymentTermsDays): Carbon
    {
        return $invoiceDate->copy()->addDays($paymentTermsDays);
    }

    /**
     * Calculate the reminder date based on due date and reminder days.
     */
    public function calculateReminderDate(Carbon $dueDate, int $reminderDays): Carbon
    {
        return $dueDate->copy()->addDays($reminderDays);
    }

    /**
     * Create a credit note from an existing invoice.
     */
    public function createCreditNote(Invoice $invoice): Invoice
    {
        if ($invoice->is_credit_note) {
            throw new \InvalidArgumentException('Kan ikke lage kreditnota av en kreditnota.');
        }

        $creditNote = Invoice::create([
            'invoice_type' => 'credit_note',
            'title' => 'Kreditnota for '.$invoice->invoice_number,
            'description' => $invoice->description,
            'contact_id' => $invoice->contact_id,
            'project_id' => $invoice->project_id,
            'original_invoice_id' => $invoice->id,
            'created_by' => auth()->id(),
            'invoice_date' => now(),
            'due_date' => now(),
            'payment_terms_days' => 0,
            'customer_name' => $invoice->customer_name,
            'customer_address' => $invoice->customer_address,
            'customer_postal_code' => $invoice->customer_postal_code,
            'customer_city' => $invoice->customer_city,
            'customer_country' => $invoice->customer_country,
            'customer_reference' => $invoice->customer_reference,
            'subtotal' => -$invoice->subtotal,
            'discount_total' => -$invoice->discount_total,
            'vat_total' => -$invoice->vat_total,
            'total' => -$invoice->total,
            'paid_amount' => -$invoice->total,
            'balance' => 0,
        ]);

        foreach ($invoice->lines as $line) {
            InvoiceLine::create([
                'invoice_id' => $creditNote->id,
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => -$line->quantity,
                'unit' => $line->unit,
                'unit_price' => $line->unit_price,
                'discount_percent' => $line->discount_percent,
                'vat_rate_id' => $line->vat_rate_id,
                'vat_percent' => $line->vat_percent,
                'sort_order' => $line->sort_order,
            ]);
        }

        // Update original invoice status to credited
        $creditedStatus = InvoiceStatus::where('code', 'credited')->first();
        if ($creditedStatus) {
            $invoice->update(['invoice_status_id' => $creditedStatus->id]);
        }

        return $creditNote;
    }

    /**
     * Mark an invoice as sent.
     */
    public function markAsSent(Invoice $invoice): Invoice
    {
        $sentStatus = InvoiceStatus::where('code', 'sent')->first();

        if ($sentStatus) {
            $invoice->update([
                'invoice_status_id' => $sentStatus->id,
                'sent_at' => now(),
            ]);
        }

        return $invoice->fresh();
    }

    /**
     * Record a payment for an invoice.
     *
     * @param  array{payment_method_id: int, payment_date: string, amount: float|int, reference?: string|null, notes?: string|null}  $data
     */
    public function recordPayment(Invoice $invoice, array $data): InvoicePayment
    {
        $payment = InvoicePayment::create([
            'invoice_id' => $invoice->id,
            'payment_method_id' => $data['payment_method_id'],
            'payment_date' => $data['payment_date'],
            'amount' => $data['amount'],
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $this->updatePaidAmount($invoice);

        return $payment;
    }

    /**
     * Update an existing payment.
     *
     * @param  array{payment_method_id: int, payment_date: string, amount: float|int, reference?: string|null, notes?: string|null}  $data
     */
    public function updatePayment(InvoicePayment $payment, array $data): InvoicePayment
    {
        $payment->update([
            'payment_method_id' => $data['payment_method_id'],
            'payment_date' => $data['payment_date'],
            'amount' => $data['amount'],
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->updatePaidAmount($payment->invoice);

        return $payment->fresh();
    }

    /**
     * Delete a payment from an invoice.
     */
    public function deletePayment(InvoicePayment $payment): void
    {
        $invoice = $payment->invoice;
        $payment->delete();
        $this->updatePaidAmount($invoice);
    }

    /**
     * Update the paid amount and balance for an invoice.
     */
    public function updatePaidAmount(Invoice $invoice): void
    {
        $paidAmount = $invoice->payments()->sum('amount');

        $invoice->update([
            'paid_amount' => $paidAmount,
            'balance' => $invoice->total - $paidAmount,
            'paid_at' => $paidAmount >= $invoice->total ? now() : null,
        ]);

        $this->updatePaymentStatus($invoice);
    }

    /**
     * Update the payment status based on paid amount.
     */
    public function updatePaymentStatus(Invoice $invoice): void
    {
        $invoice->refresh();

        if ($invoice->balance <= 0) {
            $paidStatus = InvoiceStatus::where('code', 'paid')->first();
            if ($paidStatus) {
                $invoice->update(['invoice_status_id' => $paidStatus->id]);
            }
        } elseif ($invoice->paid_amount > 0) {
            $partialStatus = InvoiceStatus::where('code', 'partially_paid')->first();
            if ($partialStatus) {
                $invoice->update(['invoice_status_id' => $partialStatus->id]);
            }
        }
    }

    /**
     * Recalculate totals for an invoice based on its lines.
     */
    public function recalculateTotals(Invoice $invoice): void
    {
        $invoice->load('lines');
        $totals = $this->totalsService->calculate($invoice->lines);

        $invoice->update([
            'subtotal' => $totals['subtotal'],
            'discount_total' => $totals['discount_total'],
            'vat_total' => $totals['vat_total'],
            'total' => $totals['total'],
            'balance' => $totals['total'] - ($invoice->paid_amount ?? 0),
        ]);
    }

    /**
     * Get the default invoice status (draft).
     */
    public function getDefaultStatus(): ?InvoiceStatus
    {
        return InvoiceStatus::where('code', 'draft')->first();
    }

    /**
     * Prepare reminder date string from due date and reminder days.
     */
    public function prepareReminderDate(?string $dueDate, ?int $reminderDays): ?string
    {
        if (! $dueDate || ! $reminderDays) {
            return null;
        }

        return date('Y-m-d', strtotime($dueDate.' +'.$reminderDays.' days'));
    }

    /**
     * Calculate due date and reminder date for a new or updated invoice.
     * This is the single source of truth for date calculations.
     *
     * @return array{due_date: Carbon, reminder_date: Carbon|null}
     */
    public function calculateInvoiceDates(
        Carbon $invoiceDate,
        ?int $paymentTermsDays = null,
        ?int $reminderDays = null
    ): array {
        $paymentTermsDays = $paymentTermsDays ?? self::DEFAULT_PAYMENT_TERMS_DAYS;
        $reminderDays = $reminderDays ?? self::DEFAULT_REMINDER_DAYS;

        $dueDate = $this->calculateDueDate($invoiceDate, $paymentTermsDays);
        $reminderDate = $reminderDays > 0 ? $this->calculateReminderDate($dueDate, $reminderDays) : null;

        return [
            'due_date' => $dueDate,
            'reminder_date' => $reminderDate,
        ];
    }

    /**
     * Calculate due date string from invoice date string and payment terms.
     * Used by Livewire components that work with string dates.
     */
    public function calculateDueDateString(string $invoiceDate, int $paymentTermsDays): string
    {
        return Carbon::parse($invoiceDate)->addDays($paymentTermsDays)->format('Y-m-d');
    }
}
