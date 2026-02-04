<?php

namespace App\Livewire\Payroll;

use App\Models\EmployeePayrollSettings;
use App\Models\PayrollRun;
use Livewire\Component;

class Dashboard extends Component
{
    public int $currentYear;

    public function mount(): void
    {
        $this->currentYear = now()->year;
    }

    public function render()
    {
        $company = app('current.company');

        // Get employee count
        $employeeCount = EmployeePayrollSettings::where('company_id', $company->id)
            ->currentlyEmployed()
            ->count();

        // Get recent payroll runs
        $recentRuns = PayrollRun::where('company_id', $company->id)
            ->ordered()
            ->limit(5)
            ->get();

        // Get YTD totals
        $ytdRuns = PayrollRun::where('company_id', $company->id)
            ->forYear($this->currentYear)
            ->whereIn('status', [PayrollRun::STATUS_PAID, PayrollRun::STATUS_REPORTED])
            ->get();

        $ytdTotals = [
            'bruttolonn' => $ytdRuns->sum('total_bruttolonn'),
            'forskuddstrekk' => $ytdRuns->sum('total_forskuddstrekk'),
            'nettolonn' => $ytdRuns->sum('total_nettolonn'),
            'arbeidsgiveravgift' => $ytdRuns->sum('total_arbeidsgiveravgift'),
            'otp' => $ytdRuns->sum('total_otp'),
        ];

        // Get pending runs
        $pendingRuns = PayrollRun::where('company_id', $company->id)
            ->whereIn('status', [PayrollRun::STATUS_DRAFT, PayrollRun::STATUS_CALCULATED])
            ->count();

        return view('livewire.payroll.dashboard', [
            'employeeCount' => $employeeCount,
            'recentRuns' => $recentRuns,
            'ytdTotals' => $ytdTotals,
            'pendingRuns' => $pendingRuns,
        ]);
    }
}
