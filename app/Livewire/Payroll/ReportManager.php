<?php

namespace App\Livewire\Payroll;

use App\Models\PayrollRun;
use Livewire\Component;

class ReportManager extends Component
{
    public int $selectedYear;

    public function mount(): void
    {
        $this->selectedYear = now()->year;
    }

    public function render()
    {
        $company = app('current.company');

        // Monthly summary
        $monthlyData = PayrollRun::where('company_id', $company->id)
            ->forYear($this->selectedYear)
            ->whereIn('status', [PayrollRun::STATUS_PAID, PayrollRun::STATUS_REPORTED])
            ->orderBy('month')
            ->get();

        // YTD totals
        $ytdTotals = [
            'bruttolonn' => $monthlyData->sum('total_bruttolonn'),
            'forskuddstrekk' => $monthlyData->sum('total_forskuddstrekk'),
            'nettolonn' => $monthlyData->sum('total_nettolonn'),
            'arbeidsgiveravgift' => $monthlyData->sum('total_arbeidsgiveravgift'),
            'otp' => $monthlyData->sum('total_otp'),
            'feriepenger_grunnlag' => $monthlyData->sum('total_feriepenger_grunnlag'),
        ];

        return view('livewire.payroll.report-manager', [
            'monthlyData' => $monthlyData,
            'ytdTotals' => $ytdTotals,
        ]);
    }
}
