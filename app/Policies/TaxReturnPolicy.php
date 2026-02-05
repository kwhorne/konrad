<?php

namespace App\Policies;

use App\Models\TaxReturn;
use App\Models\User;

class TaxReturnPolicy
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

    public function view(User $user, TaxReturn $taxReturn): bool
    {
        return $user->is_economy;
    }

    public function create(User $user): bool
    {
        return $user->is_economy;
    }

    public function update(User $user, TaxReturn $taxReturn): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $taxReturn->canBeEdited();
    }

    public function delete(User $user, TaxReturn $taxReturn): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return ! $taxReturn->isSubmitted();
    }

    public function calculate(User $user, TaxReturn $taxReturn): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $taxReturn->canBeEdited();
    }

    public function markAsReady(User $user, TaxReturn $taxReturn): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $taxReturn->isDraft();
    }

    public function markAsDraft(User $user, TaxReturn $taxReturn): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return ! $taxReturn->isSubmitted();
    }

    public function submitToAltinn(User $user, TaxReturn $taxReturn): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $taxReturn->canBeSubmitted();
    }
}
