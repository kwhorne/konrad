<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
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

    public function viewAny(User $user): bool
    {
        return $user->is_economy;
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->is_economy;
    }

    public function create(User $user): bool
    {
        return $user->is_economy;
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->is_economy;
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->is_economy;
    }

    public function approve(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->is_economy;
    }

    public function submitForApproval(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->is_economy;
    }

    public function markAsSent(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->is_economy;
    }

    public function cancel(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->is_economy;
    }
}
