<?php

namespace App\Services\Payroll;

use App\Models\EmployeePayrollSettings;
use App\Models\HolidayPayBalance;
use App\Models\PayrollEntry;
use App\Models\User;

class HolidayPayService
{
    /**
     * Standard holiday pay rate (4 weeks + 1 day).
     */
    public const STANDARD_RATE = 10.2;

    /**
     * Rate for 5 weeks holiday (tariff-based).
     */
    public const FIVE_WEEKS_RATE = 12.0;

    /**
     * Additional rate for employees over 60.
     */
    public const OVER_60_ADDITION = 2.3;

    /**
     * Calculate holiday pay basis from a payroll entry.
     */
    public function calculateFeriepengerGrunnlag(PayrollEntry $entry): float
    {
        // Holiday pay basis includes most salary components
        // but excludes holiday pay itself and certain allowances
        return $entry->grunnlonn + $entry->overtid_belop + $entry->bonus + $entry->tillegg;
    }

    /**
     * Calculate holiday pay accrual for an amount.
     */
    public function calculateFeriepengerAvsetning(float $grunnlag, EmployeePayrollSettings $settings): float
    {
        $rate = $this->getEffectiveRate($settings);

        return round($grunnlag * ($rate / 100), 2);
    }

    /**
     * Get the effective holiday pay rate for an employee.
     */
    public function getEffectiveRate(EmployeePayrollSettings $settings): float
    {
        // 5 weeks holiday (tariff-based)
        if ($settings->ferie_5_uker) {
            $rate = self::FIVE_WEEKS_RATE;

            // Over 60 with 5 weeks gets additional week = 14.3%
            if ($settings->over_60) {
                $rate = 14.3;
            }

            return $rate;
        }

        // Standard 4 weeks + 1 day
        $rate = self::STANDARD_RATE;

        // Over 60 gets extra week = 12.5%
        if ($settings->over_60) {
            $rate = self::STANDARD_RATE + self::OVER_60_ADDITION;
        }

        return $rate;
    }

    /**
     * Record holiday pay accrual for a payroll entry.
     */
    public function recordAccrual(PayrollEntry $entry): HolidayPayBalance
    {
        $year = $entry->payrollRun->year;

        $balance = HolidayPayBalance::firstOrCreate(
            [
                'company_id' => $entry->company_id,
                'user_id' => $entry->user_id,
                'opptjeningsaar' => $year,
            ],
            [
                'grunnlag' => 0,
                'opptjent' => 0,
                'utbetalt' => 0,
                'gjenstaaende' => 0,
            ]
        );

        $balance->addAccrual($entry->feriepenger_grunnlag, $entry->feriepenger_avsetning);

        return $balance;
    }

    /**
     * Process holiday pay payout for an employee.
     */
    public function processHolidayPayPayout(User $employee, int $opptjeningsaar): float
    {
        $balance = $this->getBalance($employee, $opptjeningsaar);

        if (! $balance || $balance->gjenstaaende <= 0) {
            return 0;
        }

        $payoutAmount = $balance->gjenstaaende;
        $balance->recordPayout($payoutAmount);

        return $payoutAmount;
    }

    /**
     * Get holiday pay balance for an employee and year.
     */
    public function getBalance(User $employee, int $opptjeningsaar): ?HolidayPayBalance
    {
        return HolidayPayBalance::forUser($employee)
            ->forYear($opptjeningsaar)
            ->first();
    }

    /**
     * Get all balances for an employee.
     */
    public function getAllBalances(User $employee): \Illuminate\Database\Eloquent\Collection
    {
        return HolidayPayBalance::forUser($employee)
            ->orderByDesc('opptjeningsaar')
            ->get();
    }

    /**
     * Get total remaining holiday pay for an employee.
     */
    public function getTotalRemaining(User $employee): float
    {
        return HolidayPayBalance::forUser($employee)
            ->withRemaining()
            ->sum('gjenstaaende');
    }

    /**
     * Calculate expected holiday pay for next year based on current year's accruals.
     */
    public function calculateExpectedPayout(User $employee, int $opptjeningsaar): float
    {
        $balance = $this->getBalance($employee, $opptjeningsaar);

        return $balance ? $balance->gjenstaaende : 0;
    }
}
