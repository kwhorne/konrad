<?php

namespace App\Policies;

use App\Models\StockLocation;
use App\Models\User;

class StockLocationPolicy
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

    public function view(User $user, StockLocation $location): bool
    {
        return $user->is_economy;
    }

    public function create(User $user): bool
    {
        return $user->is_economy;
    }

    public function update(User $user, StockLocation $location): bool
    {
        return $user->is_economy;
    }

    public function delete(User $user, StockLocation $location): bool
    {
        return $user->is_economy;
    }
}
