<?php

namespace App\Policies;

use App\Models\AMeldingReport;
use App\Models\User;

class AMeldingReportPolicy
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
        return (bool) $user->is_payroll;
    }

    public function view(User $user, AMeldingReport $aMeldingReport): bool
    {
        return (bool) $user->is_payroll;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_payroll;
    }

    public function update(User $user, AMeldingReport $aMeldingReport): bool
    {
        return (bool) $user->is_payroll && $aMeldingReport->is_editable;
    }

    public function delete(User $user, AMeldingReport $aMeldingReport): bool
    {
        return (bool) $user->is_payroll && $aMeldingReport->is_editable;
    }
}
