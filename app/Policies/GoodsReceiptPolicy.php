<?php

namespace App\Policies;

use App\Models\GoodsReceipt;
use App\Models\User;

class GoodsReceiptPolicy
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

    public function view(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->is_economy;
    }

    public function create(User $user): bool
    {
        return $user->is_economy;
    }

    public function update(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->is_economy;
    }

    public function delete(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->is_economy;
    }

    public function post(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->is_economy;
    }

    public function reverse(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->is_economy;
    }
}
