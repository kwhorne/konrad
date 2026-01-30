<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Voucher;

class VoucherPolicy
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
    public function view(User $user, Voucher $voucher): bool
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
    public function update(User $user, Voucher $voucher): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        // Cannot update posted vouchers
        return ! $voucher->is_posted;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Voucher $voucher): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        // Cannot delete posted vouchers
        return ! $voucher->is_posted;
    }

    /**
     * Determine whether the user can post the voucher.
     */
    public function post(User $user, Voucher $voucher): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        // Cannot post already posted vouchers
        return ! $voucher->is_posted;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Voucher $voucher): bool
    {
        return $user->is_economy;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Voucher $voucher): bool
    {
        return false;
    }
}
