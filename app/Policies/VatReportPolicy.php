<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VatReport;

class VatReportPolicy
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

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_economy;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VatReport $vatReport): bool
    {
        return $user->is_economy;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_economy;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VatReport $vatReport): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        // Cannot update accepted reports
        return $vatReport->status !== 'accepted';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VatReport $vatReport): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        // Can only delete draft reports
        return $vatReport->status === 'draft';
    }

    /**
     * Determine whether the user can submit the report.
     */
    public function submit(User $user, VatReport $vatReport): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $vatReport->status === 'draft';
    }

    /**
     * Determine whether the user can change the status (accept/reject).
     */
    public function changeStatus(User $user, VatReport $vatReport): bool
    {
        if (! $user->is_economy) {
            return false;
        }

        return $vatReport->status === 'submitted';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VatReport $vatReport): bool
    {
        return $user->is_economy;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VatReport $vatReport): bool
    {
        return false;
    }
}
