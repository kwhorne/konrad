<?php

namespace App\Policies;

use App\Models\Timesheet;
use App\Models\User;

class TimesheetPolicy
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
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Timesheet $timesheet): bool
    {
        // Can view own timesheet
        if ($timesheet->user_id === $user->id) {
            return true;
        }

        // Can view if user can approve timesheets
        return $user->canManage($timesheet->company);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Timesheet $timesheet): bool
    {
        // Must be own timesheet
        if ($timesheet->user_id !== $user->id) {
            return false;
        }

        // Must be in editable status
        return $timesheet->is_editable;
    }

    /**
     * Determine whether the user can submit the timesheet.
     */
    public function submit(User $user, Timesheet $timesheet): bool
    {
        // Must be own timesheet
        if ($timesheet->user_id !== $user->id) {
            return false;
        }

        // Must be submittable (editable and has hours)
        return $timesheet->is_submittable;
    }

    /**
     * Determine whether the user can approve the timesheet.
     */
    public function approve(User $user, Timesheet $timesheet): bool
    {
        // Cannot approve own timesheet
        if ($timesheet->user_id === $user->id) {
            return false;
        }

        // Must be submitted
        if ($timesheet->status !== Timesheet::STATUS_SUBMITTED) {
            return false;
        }

        // Must be able to manage the company
        return $user->canManage($timesheet->company);
    }

    /**
     * Determine whether the user can reject the timesheet.
     */
    public function reject(User $user, Timesheet $timesheet): bool
    {
        // Same rules as approve
        return $this->approve($user, $timesheet);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Timesheet $timesheet): bool
    {
        // Must be own timesheet
        if ($timesheet->user_id !== $user->id) {
            return false;
        }

        // Can only delete draft timesheets
        return $timesheet->status === Timesheet::STATUS_DRAFT;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Timesheet $timesheet): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Timesheet $timesheet): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view timesheets awaiting approval.
     */
    public function viewAwaitingApproval(User $user): bool
    {
        // Must be able to manage the current company
        $company = $user->currentCompany;

        if (! $company) {
            return false;
        }

        return $user->canManage($company);
    }
}
