<?php

namespace App\Livewire\Payroll;

use App\Models\AgaZone;
use App\Models\PayrollRun;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class SettingsManager extends Component
{
    use AuthorizesRequests;

    public string $agaZone = '1';

    public function mount(): void
    {
        $company = app('current.company');
        // Load company's AGA zone preference (would be stored in company settings)
        $this->agaZone = $company->aga_zone ?? '1';
    }

    public function save(): void
    {
        $this->authorize('viewAny', PayrollRun::class);

        // Save settings logic would go here
        session()->flash('success', 'Innstillinger lagret.');
    }

    public function render()
    {
        $zones = AgaZone::active()->orderBy('code')->get();

        return view('livewire.payroll.settings-manager', [
            'zones' => $zones,
        ]);
    }
}
