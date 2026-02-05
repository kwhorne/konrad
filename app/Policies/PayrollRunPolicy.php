<?php

namespace App\Policies;

use App\Models\PayrollRun;
use App\Models\User;

class PayrollRunPolicy
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
        return $user->is_payroll;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PayrollRun $payrollRun): bool
    {
        return $user->is_payroll;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_payroll;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PayrollRun $payrollRun): bool
    {
        if (! $user->is_payroll) {
            return false;
        }

        return $payrollRun->is_editable;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PayrollRun $payrollRun): bool
    {
        if (! $user->is_payroll) {
            return false;
        }

        return $payrollRun->is_editable;
    }

    /**
     * Determine whether the user can calculate the payroll run.
     */
    public function calculate(User $user, PayrollRun $payrollRun): bool
    {
        if (! $user->is_payroll) {
            return false;
        }

        return $payrollRun->status === PayrollRun::STATUS_DRAFT;
    }

    /**
     * Determine whether the user can approve the payroll run.
     */
    public function approve(User $user, PayrollRun $payrollRun): bool
    {
        if (! $user->is_payroll) {
            return false;
        }

        return $payrollRun->status === PayrollRun::STATUS_CALCULATED;
    }

    /**
     * Determine whether the user can mark the payroll run as paid.
     */
    public function markAsPaid(User $user, PayrollRun $payrollRun): bool
    {
        if (! $user->is_payroll) {
            return false;
        }

        return $payrollRun->status === PayrollRun::STATUS_APPROVED;
    }
}
