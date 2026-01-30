<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
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
    public function view(User $user, Order $order): bool
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
    public function update(User $user, Order $order): bool
    {
        // Cannot update invoiced orders
        $lockedStatuses = ['invoiced', 'cancelled'];

        return ! in_array($order->orderStatus?->code, $lockedStatuses, true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        // Cannot delete invoiced orders
        if ($order->orderStatus?->code === 'invoiced') {
            return false;
        }

        // Cannot delete orders with invoices
        if ($order->invoices()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can convert the order to an invoice.
     */
    public function convertToInvoice(User $user, Order $order): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $order->can_convert ?? false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return false;
    }
}
