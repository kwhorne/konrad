<?php

namespace App\Policies;

use App\Models\ShareTransaction;
use App\Models\User;

class ShareTransactionPolicy
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

    public function view(User $user, ShareTransaction $transaction): bool
    {
        return $user->is_economy;
    }

    public function create(User $user): bool
    {
        return $user->is_economy;
    }
}
