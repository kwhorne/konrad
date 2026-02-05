<?php

namespace App\Policies;

use App\Models\ShareholderReport;
use App\Models\User;

class ShareholderReportPolicy
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

    public function view(User $user, ShareholderReport $report): bool
    {
        return $user->is_economy;
    }

    public function create(User $user): bool
    {
        return $user->is_economy;
    }

    public function update(User $user, ShareholderReport $report): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $report->canBeEdited();
    }

    public function delete(User $user, ShareholderReport $report): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return ! $report->isSubmitted();
    }

    public function markAsReady(User $user, ShareholderReport $report): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $report->isDraft();
    }

    public function markAsDraft(User $user, ShareholderReport $report): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return ! $report->isSubmitted();
    }

    public function submitToAltinn(User $user, ShareholderReport $report): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $report->canBeSubmitted();
    }
}
