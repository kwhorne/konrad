<?php

namespace App\Services;

use App\Models\DeferredTaxItem;
use App\Models\TaxAdjustment;
use App\Models\TaxDepreciationSchedule;
use App\Models\TaxReturn;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TaxCalculationService
{
    public const TAX_RATE = 0.22; // 22% norsk selskapsskatt

    public function __construct(private ReportService $reportService) {}

    /**
     * Create a new tax return for a fiscal year.
     */
    public function createTaxReturn(int $year, int $createdBy): TaxReturn
    {
        $periodStart = Carbon::create($year, 1, 1);
        $periodEnd = Carbon::create($year, 12, 31);

        // Initialize depreciation schedules for the year
        TaxDepreciationSchedule::initializeForYear($year, $createdBy);

        // Get previous year's losses to carry forward
        $previousReturn = TaxReturn::where('fiscal_year', $year - 1)->first();
        $lossesForward = $previousReturn?->losses_carried_forward ?? 0;

        return TaxReturn::create([
            'fiscal_year' => $year,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'tax_rate' => self::TAX_RATE * 100,
            'losses_brought_forward' => $lossesForward,
            'status' => 'draft',
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Calculate full tax return from accounting data.
     */
    public function calculateTaxReturn(TaxReturn $taxReturn): TaxReturn
    {
        $year = $taxReturn->fiscal_year;
        $periodStart = $taxReturn->period_start;
        $periodEnd = $taxReturn->period_end;

        // 1. Get accounting profit from income statement
        $incomeStatement = $this->reportService->getIncomeStatement($periodStart, $periodEnd);
        $accountingProfit = $incomeStatement['profit_before_tax'];

        // 2. Calculate permanent differences
        $permanentDifferences = $this->calculatePermanentDifferences($year);

        // 3. Calculate temporary differences change
        $temporaryDifferencesChange = $this->calculateTemporaryDifferencesChange($year);

        // 4. Calculate deferred tax change
        $deferredTaxChange = $this->calculateDeferredTaxChange($year);

        // Update tax return
        $taxReturn->update([
            'accounting_profit' => $accountingProfit,
            'permanent_differences' => $permanentDifferences,
            'temporary_differences_change' => $temporaryDifferencesChange,
            'deferred_tax_change' => $deferredTaxChange,
        ]);

        // Calculate tax
        $taxReturn->calculateTax();
        $taxReturn->save();

        // Store calculation details
        $taxReturn->update([
            'calculation_details' => $this->generateCalculationDetails($taxReturn),
        ]);

        return $taxReturn->fresh();
    }

    /**
     * Calculate sum of permanent differences for a year.
     */
    public function calculatePermanentDifferences(int $year): float
    {
        return TaxAdjustment::forYear($year)
            ->permanent()
            ->sum('difference');
    }

    /**
     * Calculate change in temporary differences for a year.
     */
    public function calculateTemporaryDifferencesChange(int $year): float
    {
        // Current year temporary adjustments
        $currentYearChange = TaxAdjustment::forYear($year)
            ->temporary()
            ->sum('difference');

        // Add depreciation difference
        $depreciationDifference = $this->calculateDepreciationDifference($year);

        return $currentYearChange + $depreciationDifference;
    }

    /**
     * Calculate depreciation difference (accounting vs tax).
     */
    public function calculateDepreciationDifference(int $year): float
    {
        // Tax depreciation (saldoavskrivning)
        $taxDepreciation = TaxDepreciationSchedule::forYear($year)
            ->sum('depreciation_amount');

        // Accounting depreciation would come from the accounting system
        // For now, we'll assume it's stored as an adjustment
        $accountingDepreciation = TaxAdjustment::forYear($year)
            ->byCategory('depreciation_difference')
            ->value('accounting_amount') ?? $taxDepreciation;

        return $accountingDepreciation - $taxDepreciation;
    }

    /**
     * Calculate change in deferred tax for a year.
     */
    public function calculateDeferredTaxChange(int $year): float
    {
        // Current year deferred tax
        $currentYear = DeferredTaxItem::forYear($year)->get();
        $currentTotal = $currentYear->sum(fn ($item) => $item->getSignedDeferredTax());

        // Previous year deferred tax
        $previousYear = DeferredTaxItem::forYear($year - 1)->get();
        $previousTotal = $previousYear->sum(fn ($item) => $item->getSignedDeferredTax());

        return $currentTotal - $previousTotal;
    }

    /**
     * Get deferred tax summary for a year.
     */
    public function getDeferredTaxSummary(int $year): array
    {
        $items = DeferredTaxItem::forYear($year)->get();

        $assets = $items->filter(fn ($item) => $item->isDeferredTaxAsset());
        $liabilities = $items->filter(fn ($item) => $item->isDeferredTaxLiability());

        return [
            'year' => $year,
            'items' => $items,
            'deferred_tax_assets' => $assets->sum('deferred_tax'),
            'deferred_tax_liabilities' => $liabilities->sum('deferred_tax'),
            'net_deferred_tax' => $items->sum(fn ($item) => $item->getSignedDeferredTax()),
        ];
    }

    /**
     * Get depreciation schedules for a year.
     */
    public function getDepreciationSchedules(int $year): Collection
    {
        return TaxDepreciationSchedule::forYear($year)
            ->ordered()
            ->get();
    }

    /**
     * Get tax adjustments summary for a year.
     */
    public function getTaxAdjustmentsSummary(int $year): array
    {
        $adjustments = TaxAdjustment::forYear($year)->get();

        return [
            'year' => $year,
            'permanent' => [
                'items' => $adjustments->where('adjustment_type', 'permanent'),
                'total' => $adjustments->where('adjustment_type', 'permanent')->sum('difference'),
            ],
            'temporary_deductible' => [
                'items' => $adjustments->where('adjustment_type', 'temporary_deductible'),
                'total' => $adjustments->where('adjustment_type', 'temporary_deductible')->sum('difference'),
            ],
            'temporary_taxable' => [
                'items' => $adjustments->where('adjustment_type', 'temporary_taxable'),
                'total' => $adjustments->where('adjustment_type', 'temporary_taxable')->sum('difference'),
            ],
            'total_permanent' => $adjustments->where('adjustment_type', 'permanent')->sum('difference'),
            'total_temporary' => $adjustments->whereIn('adjustment_type', ['temporary_deductible', 'temporary_taxable'])->sum('difference'),
        ];
    }

    /**
     * Generate detailed calculation breakdown.
     */
    private function generateCalculationDetails(TaxReturn $taxReturn): array
    {
        $year = $taxReturn->fiscal_year;

        return [
            'generated_at' => now()->toIso8601String(),
            'income_reconciliation' => [
                'accounting_profit' => $taxReturn->accounting_profit,
                'permanent_differences' => $taxReturn->permanent_differences,
                'temporary_differences_change' => $taxReturn->temporary_differences_change,
                'taxable_before_losses' => $taxReturn->accounting_profit + $taxReturn->permanent_differences + $taxReturn->temporary_differences_change,
                'losses_brought_forward' => $taxReturn->losses_brought_forward,
                'losses_used' => $taxReturn->losses_used,
                'taxable_income' => $taxReturn->taxable_income,
            ],
            'tax_calculation' => [
                'taxable_income' => $taxReturn->taxable_income,
                'tax_rate' => $taxReturn->tax_rate,
                'tax_payable' => $taxReturn->tax_payable,
                'deferred_tax_change' => $taxReturn->deferred_tax_change,
                'total_tax_expense' => $taxReturn->total_tax_expense,
            ],
            'losses' => [
                'brought_forward' => $taxReturn->losses_brought_forward,
                'used' => $taxReturn->losses_used,
                'carried_forward' => $taxReturn->losses_carried_forward,
            ],
            'adjustments' => $this->getTaxAdjustmentsSummary($year),
            'deferred_tax' => $this->getDeferredTaxSummary($year),
            'depreciation' => [
                'schedules' => $this->getDepreciationSchedules($year)->toArray(),
                'total_tax_depreciation' => $this->getDepreciationSchedules($year)->sum('depreciation_amount'),
            ],
        ];
    }

    /**
     * Validate tax return is complete and correct.
     */
    public function validateTaxReturn(TaxReturn $taxReturn): array
    {
        $errors = [];
        $warnings = [];

        // Check accounting profit is set
        if ($taxReturn->accounting_profit == 0) {
            $warnings[] = 'Regnskapsmessig resultat er null. Kontroller at dette er korrekt.';
        }

        // Check tax rate
        if ($taxReturn->tax_rate != self::TAX_RATE * 100) {
            $warnings[] = 'Skattesats avviker fra standard 22%. Kontroller at dette er korrekt.';
        }

        // Check depreciation schedules exist
        $schedules = TaxDepreciationSchedule::forYear($taxReturn->fiscal_year)->count();
        if ($schedules == 0) {
            $errors[] = 'Ingen saldoavskrivninger er registrert for året.';
        }

        // Verify calculations
        $expectedTaxableIncome = $taxReturn->accounting_profit
            + $taxReturn->permanent_differences
            + $taxReturn->temporary_differences_change
            - $taxReturn->losses_used;

        if (abs($expectedTaxableIncome - $taxReturn->taxable_income) > 0.01) {
            $errors[] = 'Beregnet skattepliktig inntekt stemmer ikke. Kjør beregning på nytt.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
}
