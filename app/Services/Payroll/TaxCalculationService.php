<?php

namespace App\Services\Payroll;

use App\Models\EmployeePayrollSettings;

class TaxCalculationService
{
    /**
     * Calculate forskuddstrekk (withholding tax) for an employee.
     */
    public function calculateForskuddstrekk(float $bruttolonn, EmployeePayrollSettings $settings): float
    {
        return match ($settings->skatt_type) {
            EmployeePayrollSettings::SKATT_TYPE_TABELLTREKK => $this->calculateTabelltrekk($bruttolonn, $settings),
            EmployeePayrollSettings::SKATT_TYPE_PROSENTTREKK => $this->calculateProsenttrekk($bruttolonn, $settings),
            EmployeePayrollSettings::SKATT_TYPE_KILDESKATT => $this->calculateKildeskatt($bruttolonn),
            EmployeePayrollSettings::SKATT_TYPE_FRIKORT => $this->calculateFrikorttrekk($bruttolonn, $settings),
            default => $this->calculateTabelltrekk($bruttolonn, $settings),
        };
    }

    /**
     * Calculate tax using table-based method.
     * Note: This is a simplified implementation. A full implementation would
     * use the actual tax tables from Skatteetaten.
     */
    protected function calculateTabelltrekk(float $bruttolonn, EmployeePayrollSettings $settings): float
    {
        // Get the estimated tax percentage from the table
        $percentage = $this->getTaxTableRate($settings->skattetabell ?? '7100', $bruttolonn);

        return round($bruttolonn * ($percentage / 100), 0);
    }

    /**
     * Calculate tax using percentage-based method.
     */
    protected function calculateProsenttrekk(float $bruttolonn, EmployeePayrollSettings $settings): float
    {
        $percentage = $settings->skatteprosent ?? 30;

        return round($bruttolonn * ($percentage / 100), 0);
    }

    /**
     * Calculate kildeskatt (source tax for foreign workers).
     * Standard rate is 25% for most workers.
     */
    public function calculateKildeskatt(float $bruttolonn): float
    {
        return round($bruttolonn * 0.25, 0);
    }

    /**
     * Calculate tax when employee has a frikort (tax-free card).
     */
    protected function calculateFrikorttrekk(float $bruttolonn, EmployeePayrollSettings $settings): float
    {
        $remainingFrikort = $settings->remaining_frikort;

        if ($remainingFrikort <= 0) {
            // Frikort exhausted, use 50% as default emergency rate
            return round($bruttolonn * 0.50, 0);
        }

        if ($bruttolonn <= $remainingFrikort) {
            // Entire salary is within frikort limit
            return 0;
        }

        // Part of salary exceeds frikort - tax that part at 50%
        $taxableAmount = $bruttolonn - $remainingFrikort;

        return round($taxableAmount * 0.50, 0);
    }

    /**
     * Get tax rate from tax table.
     * This is a simplified lookup. A real implementation would use
     * Skatteetaten's actual tax tables.
     *
     * @param  string  $tableCode  The tax table code (e.g., "7100")
     * @param  float  $monthlyIncome  Monthly gross income
     * @return float Tax percentage
     */
    public function getTaxTableRate(string $tableCode, float $monthlyIncome): float
    {
        // Simplified tax brackets for table 7100 (standard table)
        // These are approximate and should be replaced with actual tables
        // Format: [threshold, rate] - rate applies when income <= threshold
        $brackets = [
            [20000, 0],
            [30000, 25],
            [40000, 30],
            [50000, 33],
            [60000, 35],
            [80000, 38],
            [100000, 40],
            [150000, 43],
        ];

        // Adjust for table variations (simplified)
        $adjustment = match (substr($tableCode, 0, 1)) {
            '6' => -2, // Lower tables (fewer deductions)
            '7' => 0,  // Standard table
            '8' => 2,  // Higher tables (more deductions possible)
            default => 0,
        };

        $rate = 45; // Default max rate for income above all brackets
        foreach ($brackets as [$threshold, $bracketRate]) {
            if ($monthlyIncome <= $threshold) {
                $rate = $bracketRate;
                break;
            }
        }

        return max(0, $rate + $adjustment);
    }

    /**
     * Update frikort usage for an employee.
     */
    public function updateFrikortUsage(EmployeePayrollSettings $settings, float $amount): void
    {
        if ($settings->skatt_type !== EmployeePayrollSettings::SKATT_TYPE_FRIKORT) {
            return;
        }

        $settings->frikort_brukt += $amount;
        $settings->save();
    }

    /**
     * Reset frikort usage for a new year.
     */
    public function resetFrikortUsage(EmployeePayrollSettings $settings): void
    {
        if ($settings->skatt_type === EmployeePayrollSettings::SKATT_TYPE_FRIKORT) {
            $settings->frikort_brukt = 0;
            $settings->save();
        }
    }

    /**
     * Check if tax card needs renewal (expired or about to expire).
     */
    public function needsTaxCardRenewal(EmployeePayrollSettings $settings): bool
    {
        if (! $settings->skattekort_gyldig_til) {
            return true;
        }

        // Warn 30 days before expiry
        return now()->diffInDays($settings->skattekort_gyldig_til, false) < 30;
    }

    /**
     * Get the tax info summary for payslip.
     */
    public function getTaxInfoSummary(EmployeePayrollSettings $settings): array
    {
        return [
            'type' => $settings->skatt_type_label,
            'table' => $settings->skattetabell,
            'percentage' => $settings->skatteprosent,
            'frikort_remaining' => $settings->remaining_frikort,
            'valid_until' => $settings->skattekort_gyldig_til?->format('d.m.Y'),
        ];
    }
}
