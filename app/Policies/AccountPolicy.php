<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
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

    public function view(User $user, Account $account): bool
    {
        return $user->is_economy;
    }

    public function create(User $user): bool
    {
        return $user->is_economy;
    }

    public function update(User $user, Account $account): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return ! $account->is_system;
    }

    public function delete(User $user, Account $account): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return ! $account->is_system;
    }
}
