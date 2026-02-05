<?php

namespace App\Policies;

use App\Models\Shareholder;
use App\Models\User;

class ShareholderPolicy
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

    public function view(User $user, Shareholder $shareholder): bool
    {
        return $user->is_economy;
    }

    public function create(User $user): bool
    {
        return $user->is_economy;
    }

    public function update(User $user, Shareholder $shareholder): bool
    {
        return $user->is_economy;
    }

    public function delete(User $user, Shareholder $shareholder): bool
    {
        return $user->is_economy;
    }
}
