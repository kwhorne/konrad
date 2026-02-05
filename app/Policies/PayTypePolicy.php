<?php

namespace App\Policies;

use App\Models\PayType;
use App\Models\User;

class PayTypePolicy
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
        return $user->is_payroll;
    }

    public function view(User $user, PayType $payType): bool
    {
        return $user->is_payroll;
    }

    public function create(User $user): bool
    {
        return $user->is_payroll;
    }

    public function update(User $user, PayType $payType): bool
    {
        return $user->is_payroll;
    }

    public function delete(User $user, PayType $payType): bool
    {
        return $user->is_payroll;
    }
}
