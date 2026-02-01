<?php

namespace App\Livewire;

use App\Models\AccountingSettings;
use App\Models\Department;
use Livewire\Component;

class AccountingSettingsManager extends Component
{
    public bool $departments_enabled = false;

    public bool $require_department_on_vouchers = false;

    public ?int $default_department_id = null;

    public function mount()
    {
        $company = app('current.company');
        if (! $company) {
            return;
        }

        $settings = AccountingSettings::getOrCreate($company->id);
        $this->departments_enabled = $settings->departments_enabled;
        $this->require_department_on_vouchers = $settings->require_department_on_vouchers;
        $this->default_department_id = $settings->default_department_id;
    }

    public function save()
    {
        $company = app('current.company');
        if (! $company) {
            return;
        }

        if (! auth()->user()->canManage($company)) {
            $this->dispatch('toast', message: 'Du har ikke tilgang til a endre innstillinger.', variant: 'danger');

            return;
        }

        $settings = AccountingSettings::getOrCreate($company->id);

        // If disabling departments, clear related settings
        if (! $this->departments_enabled) {
            $this->require_department_on_vouchers = false;
            $this->default_department_id = null;
        }

        // Verify default department belongs to company
        if ($this->default_department_id) {
            $department = Department::find($this->default_department_id);
            if (! $department || $department->company_id !== $company->id) {
                $this->default_department_id = null;
            }
        }

        $settings->update([
            'departments_enabled' => $this->departments_enabled,
            'require_department_on_vouchers' => $this->require_department_on_vouchers,
            'default_department_id' => $this->default_department_id ?: null,
        ]);

        $this->dispatch('toast', message: 'Innstillinger lagret', variant: 'success');
    }

    public function render()
    {
        $company = app('current.company');
        $departments = $company ? Department::where('company_id', $company->id)->active()->ordered()->get() : collect();
        $canManage = $company && auth()->user()->canManage($company);

        return view('livewire.accounting-settings-manager', [
            'departments' => $departments,
            'canManage' => $canManage,
        ]);
    }
}
