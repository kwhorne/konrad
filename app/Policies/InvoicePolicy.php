<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Admins can do everything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_admin) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_economy;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->is_economy;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_economy;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        // Cannot update sent/paid invoices (must create credit note instead)
        if ($invoice->sent_at && $invoice->invoice_type === 'invoice') {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        // Cannot delete sent invoices
        if ($invoice->sent_at) {
            return false;
        }

        // Cannot delete invoices with payments
        if ($invoice->paid_amount > 0) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can create a credit note for the invoice.
     */
    public function createCreditNote(User $user, Invoice $invoice): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        // Can only credit regular invoices, not credit notes
        return $invoice->invoice_type === 'invoice';
    }

    /**
     * Determine whether the user can mark the invoice as sent.
     */
    public function markAsSent(User $user, Invoice $invoice): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        // Already sent
        if ($invoice->sent_at) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can record a payment.
     */
    public function recordPayment(User $user, Invoice $invoice): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        // Can only record payments on sent invoices
        return $invoice->sent_at !== null;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->is_economy;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        // Only admins can force delete (handled by before())
        return false;
    }
}
