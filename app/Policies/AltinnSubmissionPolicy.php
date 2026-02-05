<?php

namespace App\Policies;

use App\Models\AltinnSubmission;
use App\Models\User;

class AltinnSubmissionPolicy
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
        return (bool) $user->is_economy;
    }

    public function view(User $user, AltinnSubmission $altinnSubmission): bool
    {
        return (bool) $user->is_economy;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_economy;
    }

    public function update(User $user, AltinnSubmission $altinnSubmission): bool
    {
        return (bool) $user->is_economy && $altinnSubmission->canBeEdited();
    }

    public function delete(User $user, AltinnSubmission $altinnSubmission): bool
    {
        return (bool) $user->is_economy && $altinnSubmission->canBeEdited();
    }
}
