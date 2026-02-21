<?php

namespace App\Policies;

use App\Models\Dividend;
use App\Models\User;

class DividendPolicy
{
    /**
     * Admins can do everything.
     */
    public function before(User $user, string $ability, mixed $model = null): ?bool
    {
        if ($user->is_admin) {
            return true;
        }

        if (is_object($model) && isset($model->company_id) && app()->bound('current.company')) {
            $company = app('current.company');
            if ($company && $model->company_id !== $company->id) {
                return false;
            }
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->is_economy;
    }

    public function view(User $user, Dividend $dividend): bool
    {
        return $user->is_economy;
    }

    public function create(User $user): bool
    {
        return $user->is_economy;
    }

    public function update(User $user, Dividend $dividend): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return ! $dividend->isPaid();
    }

    public function delete(User $user, Dividend $dividend): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return ! $dividend->isPaid();
    }

    public function approve(User $user, Dividend $dividend): bool
    {
        return $user->is_economy;
    }

    public function markAsPaid(User $user, Dividend $dividend): bool
    {
        return $user->is_economy;
    }

    public function cancel(User $user, Dividend $dividend): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $dividend->canBeCancelled();
    }
}
