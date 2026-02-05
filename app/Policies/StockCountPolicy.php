<?php

namespace App\Policies;

use App\Models\StockCount;
use App\Models\User;

class StockCountPolicy
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

    public function view(User $user, StockCount $stockCount): bool
    {
        return $user->is_economy;
    }

    public function create(User $user): bool
    {
        return $user->is_economy;
    }

    public function update(User $user, StockCount $stockCount): bool
    {
        return $user->is_economy;
    }

    public function delete(User $user, StockCount $stockCount): bool
    {
        return $user->is_economy;
    }

    public function start(User $user, StockCount $stockCount): bool
    {
        return $user->is_economy;
    }

    public function complete(User $user, StockCount $stockCount): bool
    {
        return $user->is_economy;
    }

    public function post(User $user, StockCount $stockCount): bool
    {
        return $user->is_economy;
    }

    public function cancel(User $user, StockCount $stockCount): bool
    {
        return $user->is_economy;
    }
}
