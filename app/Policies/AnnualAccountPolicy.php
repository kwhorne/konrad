<?php

namespace App\Policies;

use App\Models\AnnualAccount;
use App\Models\User;

class AnnualAccountPolicy
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

    public function view(User $user, AnnualAccount $annualAccount): bool
    {
        return $user->is_economy;
    }

    public function create(User $user): bool
    {
        return $user->is_economy;
    }

    public function update(User $user, AnnualAccount $annualAccount): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $annualAccount->canBeEdited();
    }

    public function delete(User $user, AnnualAccount $annualAccount): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return ! $annualAccount->isSubmitted();
    }

    public function approve(User $user, AnnualAccount $annualAccount): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $annualAccount->isDraft();
    }

    public function markAsDraft(User $user, AnnualAccount $annualAccount): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return ! $annualAccount->isSubmitted();
    }

    public function submitToAltinn(User $user, AnnualAccount $annualAccount): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $annualAccount->canBeSubmitted();
    }
}
