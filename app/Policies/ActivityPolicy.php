<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
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

    /**
     * Any authenticated company member can manage activities.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Activity $activity): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Activity $activity): bool
    {
        return true;
    }

    public function delete(User $user, Activity $activity): bool
    {
        return true;
    }
}
