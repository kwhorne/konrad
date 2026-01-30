<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

class QuotePolicy
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
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Quote $quote): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Quote $quote): bool
    {
        // Cannot update converted/rejected quotes
        $lockedStatuses = ['converted', 'rejected'];

        return ! in_array($quote->quoteStatus?->code, $lockedStatuses, true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Quote $quote): bool
    {
        // Cannot delete converted quotes
        if ($quote->quoteStatus?->code === 'converted') {
            return false;
        }

        // Cannot delete quotes with orders
        if ($quote->orders()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can convert the quote to an order.
     */
    public function convertToOrder(User $user, Quote $quote): bool
    {
        return $quote->can_convert ?? false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Quote $quote): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Quote $quote): bool
    {
        return false;
    }
}
