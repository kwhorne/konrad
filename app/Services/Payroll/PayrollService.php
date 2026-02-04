<?php

namespace App\Services\Payroll;

use App\Models\Company;
use App\Models\EmployeePayrollSettings;
use App\Models\PayrollEntry;
use App\Models\PayrollRun;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function __construct(
        protected TaxCalculationService $taxService,
        protected HolidayPayService $holidayPayService,
        protected AgaService $agaService,
        protected OtpService $otpService
    ) {}

    /**
     * Create a new payroll run for a company.
     */
    public function createPayrollRun(
        Company $company,
        int $year,
        int $month,
        Carbon $paymentDate
    ): PayrollRun {
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $zone = $this->agaService->getZoneForCompany($company);

        return PayrollRun::create([
            'company_id' => $company->id,
            'year' => $year,
            'month' => $month,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'utbetalingsdato' => $paymentDate,
            'status' => PayrollRun::STATUS_DRAFT,
            'aga_sone' => $zone->code,
            'aga_sats' => $zone->rate,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Calculate payroll for all active employees in a run.
     */
    public function calculatePayroll(PayrollRun $run): void
    {
        DB::transaction(function () use ($run) {
            // Get all active employees with payroll settings
            $employees = EmployeePayrollSettings::where('company_id', $run->company_id)
                ->currentlyEmployed()
                ->with('user')
                ->get();

            foreach ($employees as $settings) {
                $this->calculateEmployeeSalary($run, $settings->user, $settings);
            }

            // Update run status and recalculate totals
            $run->recalculateTotals();
            $run->status = PayrollRun::STATUS_CALCULATED;
            $run->save();
        });
    }

    /**
     * Calculate salary for a single employee.
     */
    public function calculateEmployeeSalary(
        PayrollRun $run,
        User $employee,
        ?EmployeePayrollSettings $settings = null
    ): PayrollEntry {
        $settings = $settings ?? EmployeePayrollSettings::where('company_id', $run->company_id)
            ->where('user_id', $employee->id)
            ->first();

        if (! $settings) {
            throw new \InvalidArgumentException('Employee has no payroll settings');
        }

        // Create or update the payroll entry
        $entry = PayrollEntry::updateOrCreate(
            [
                'company_id' => $run->company_id,
                'payroll_run_id' => $run->id,
                'user_id' => $employee->id,
            ],
            $this->calculateEntryData($run, $settings)
        );

        // Record holiday pay accrual
        $this->holidayPayService->recordAccrual($entry);

        // Update frikort usage if applicable
        if ($settings->skatt_type === EmployeePayrollSettings::SKATT_TYPE_FRIKORT) {
            $this->taxService->updateFrikortUsage($settings, $entry->bruttolonn);
        }

        return $entry;
    }

    /**
     * Calculate the data for a payroll entry.
     */
    protected function calculateEntryData(PayrollRun $run, EmployeePayrollSettings $settings): array
    {
        // Import timesheet hours if applicable
        $hours = $this->importTimesheetHours($run, $settings);

        // Calculate gross salary components
        $grunnlonn = $this->calculateGrunnlonn($settings, $hours);
        $overtidBelop = $this->calculateOvertid($settings, $hours);
        $bruttolonn = $grunnlonn + $overtidBelop;

        // Calculate tax
        $forskuddstrekk = $this->taxService->calculateForskuddstrekk($bruttolonn, $settings);

        // Calculate net salary
        $nettolonn = $bruttolonn - $forskuddstrekk;

        // Calculate holiday pay accrual
        $feriepengerGrunnlag = $bruttolonn;
        $feriepengerAvsetning = $this->holidayPayService->calculateFeriepengerAvsetning(
            $feriepengerGrunnlag,
            $settings
        );

        // Calculate employer contributions
        $agaBasis = $bruttolonn + $feriepengerAvsetning;
        $arbeidsgiveravgift = $this->agaService->calculateArbeidsgiveravgift(
            $agaBasis,
            $run->aga_sone
        );

        // Calculate OTP
        $aarslonn = $settings->aarslonn ?? ($settings->maanedslonn ? $settings->maanedslonn * 12 : 0);
        $otpBelop = $this->otpService->calculateMonthlyOtp(
            $bruttolonn,
            $settings
        );

        return [
            'timer_ordinaer' => $hours['ordinaer'],
            'timer_overtid_50' => $hours['overtid_50'],
            'timer_overtid_100' => $hours['overtid_100'],
            'grunnlonn' => $grunnlonn,
            'overtid_belop' => $overtidBelop,
            'bonus' => 0,
            'tillegg' => 0,
            'bruttolonn' => $bruttolonn,
            'forskuddstrekk' => $forskuddstrekk,
            'fagforening' => 0,
            'andre_trekk' => 0,
            'nettolonn' => $nettolonn,
            'feriepenger_grunnlag' => $feriepengerGrunnlag,
            'feriepenger_avsetning' => $feriepengerAvsetning,
            'arbeidsgiveravgift' => $arbeidsgiveravgift,
            'otp_belop' => $otpBelop,
            'skatt_type_brukt' => $settings->skatt_type,
            'skatteprosent_brukt' => $settings->skatteprosent,
        ];
    }

    /**
     * Import timesheet hours for the payroll period.
     */
    protected function importTimesheetHours(PayrollRun $run, EmployeePayrollSettings $settings): array
    {
        // Get approved timesheets for this period
        $timesheets = Timesheet::where('company_id', $run->company_id)
            ->where('user_id', $settings->user_id)
            ->where('status', Timesheet::STATUS_APPROVED)
            ->where('week_start', '>=', $run->period_start)
            ->where('week_end', '<=', $run->period_end)
            ->get();

        $totalHours = $timesheets->sum('total_hours');

        // Standard monthly hours (assuming 37.5 hours/week * 4.33 weeks)
        $standardMonthlyHours = 162.5 * ($settings->stillingsprosent / 100);

        return [
            'ordinaer' => min($totalHours, $standardMonthlyHours),
            'overtid_50' => 0, // Would be calculated from timesheet entries
            'overtid_100' => 0,
        ];
    }

    /**
     * Calculate base salary for the period.
     */
    protected function calculateGrunnlonn(EmployeePayrollSettings $settings, array $hours): float
    {
        if ($settings->lonn_type === EmployeePayrollSettings::LONN_TYPE_FAST) {
            // Fixed monthly salary, adjusted for position percentage
            return ($settings->maanedslonn ?? 0) * ($settings->stillingsprosent / 100);
        }

        // Hourly salary
        return $hours['ordinaer'] * ($settings->timelonn ?? 0);
    }

    /**
     * Calculate overtime pay.
     */
    protected function calculateOvertid(EmployeePayrollSettings $settings, array $hours): float
    {
        $hourlyRate = $settings->timelonn ?? ($settings->maanedslonn / 162.5);

        $overtid50 = $hours['overtid_50'] * $hourlyRate * 1.5;
        $overtid100 = $hours['overtid_100'] * $hourlyRate * 2.0;

        return round($overtid50 + $overtid100, 2);
    }

    /**
     * Approve a payroll run.
     */
    public function approveRun(PayrollRun $run, User $approver): bool
    {
        return $run->approve($approver);
    }

    /**
     * Mark a payroll run as paid.
     */
    public function markAsPaid(PayrollRun $run): bool
    {
        return $run->markAsPaid();
    }

    /**
     * Get summary statistics for a payroll run.
     */
    public function getRunSummary(PayrollRun $run): array
    {
        $entries = $run->entries()->with('user')->get();

        return [
            'employee_count' => $entries->count(),
            'total_bruttolonn' => $run->total_bruttolonn,
            'total_forskuddstrekk' => $run->total_forskuddstrekk,
            'total_nettolonn' => $run->total_nettolonn,
            'total_feriepenger_avsetning' => $entries->sum('feriepenger_avsetning'),
            'total_arbeidsgiveravgift' => $run->total_arbeidsgiveravgift,
            'total_otp' => $run->total_otp,
            'total_employer_cost' => $run->total_employer_cost,
        ];
    }
}
