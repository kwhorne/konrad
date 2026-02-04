<?php

namespace App\Livewire\Payroll;

use App\Models\HolidayPayBalance;
use Livewire\Component;

class HolidayPayManager extends Component
{
    public int $selectedYear;

    public function mount(): void
    {
        $this->selectedYear = now()->year - 1; // Show last year's accrual
    }

    public function render()
    {
        $company = app('current.company');

        $balances = HolidayPayBalance::where('company_id', $company->id)
            ->forYear($this->selectedYear)
            ->with('user')
            ->orderBy('user_id')
            ->get();

        $totals = [
            'grunnlag' => $balances->sum('grunnlag'),
            'opptjent' => $balances->sum('opptjent'),
            'utbetalt' => $balances->sum('utbetalt'),
            'gjenstaaende' => $balances->sum('gjenstaaende'),
        ];

        return view('livewire.payroll.holiday-pay-manager', [
            'balances' => $balances,
            'totals' => $totals,
        ]);
    }
}
