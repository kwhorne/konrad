<?php

namespace App\Policies;

use App\Models\IncomingVoucher;
use App\Models\User;

class IncomingVoucherPolicy
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

    public function view(User $user, IncomingVoucher $voucher): bool
    {
        return $user->is_economy;
    }

    public function create(User $user): bool
    {
        return $user->is_economy;
    }

    public function update(User $user, IncomingVoucher $voucher): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return ! in_array($voucher->status, [IncomingVoucher::STATUS_APPROVED, IncomingVoucher::STATUS_POSTED]);
    }

    public function delete(User $user, IncomingVoucher $voucher): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return ! in_array($voucher->status, [IncomingVoucher::STATUS_APPROVED, IncomingVoucher::STATUS_POSTED]);
    }

    public function attest(User $user, IncomingVoucher $voucher): bool
    {
        return $user->is_economy;
    }

    public function approve(User $user, IncomingVoucher $voucher): bool
    {
        return $user->is_economy;
    }

    public function reject(User $user, IncomingVoucher $voucher): bool
    {
        return $user->is_economy;
    }
}
