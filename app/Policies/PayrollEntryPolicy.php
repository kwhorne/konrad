<?php

namespace App\Policies;

use App\Models\PayrollEntry;
use App\Models\User;

class PayrollEntryPolicy
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
    public function view(User $user, PayrollEntry $payrollEntry): bool
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
    public function update(User $user, PayrollEntry $payrollEntry): bool
    {
        if (! $user->is_payroll) {
            return false;
        }

        return $payrollEntry->payrollRun->is_editable;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PayrollEntry $payrollEntry): bool
    {
        if (! $user->is_payroll) {
            return false;
        }

        return $payrollEntry->payrollRun->is_editable;
    }

    /**
     * Determine whether the user can download the payslip.
     */
    public function downloadPayslip(User $user, PayrollEntry $payrollEntry): bool
    {
        return $user->is_payroll;
    }

    /**
     * Determine whether the user can send the payslip.
     */
    public function sendPayslip(User $user, PayrollEntry $payrollEntry): bool
    {
        return $user->is_payroll;
    }
}
