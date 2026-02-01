<?php

use App\Models\Company;
use App\Models\DeferredTaxItem;
use App\Models\TaxAdjustment;
use App\Models\TaxDepreciationSchedule;
use App\Models\TaxReturn;
use App\Models\User;
use App\Services\ReportService;
use App\Services\TaxCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function setupTaxContext(): array
{
    $user = User::factory()->create(['onboarding_completed' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);
    app()->instance('current.company', $company);

    return ['user' => $user->fresh(), 'company' => $company];
}

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = setupTaxContext();
    $this->actingAs($this->user);
});

describe('TaxCalculationService Constants', function () {
    test('uses correct Norwegian corporate tax rate', function () {
        expect(TaxCalculationService::TAX_RATE)->toBe(0.22);
    });
});

describe('TaxCalculationService Create Tax Return', function () {
    test('creates tax return for fiscal year', function () {
        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $taxReturn = $service->createTaxReturn(2024, $this->user->id);

        expect($taxReturn)->toBeInstanceOf(TaxReturn::class)
            ->and($taxReturn->fiscal_year)->toBe(2024)
            ->and($taxReturn->period_start->format('Y-m-d'))->toBe('2024-01-01')
            ->and($taxReturn->period_end->format('Y-m-d'))->toBe('2024-12-31')
            ->and((float) $taxReturn->tax_rate)->toBe(22.00)
            ->and($taxReturn->status)->toBe('draft');
    });

    test('carries forward losses from previous year', function () {
        // Create previous year tax return with losses
        TaxReturn::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2023,
            'losses_carried_forward' => 50000,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $taxReturn = $service->createTaxReturn(2024, $this->user->id);

        expect((float) $taxReturn->losses_brought_forward)->toBe(50000.00);
    });

    test('starts with zero losses if no previous year', function () {
        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $taxReturn = $service->createTaxReturn(2024, $this->user->id);

        expect((float) $taxReturn->losses_brought_forward)->toBe(0.00);
    });
});

describe('TaxCalculationService Permanent Differences', function () {
    test('calculates sum of permanent differences', function () {
        // Create permanent adjustments
        TaxAdjustment::factory()->permanent()->forYear(2024)->create([
            'company_id' => $this->company->id,
            'accounting_amount' => 10000,
            'tax_amount' => 0, // Non-deductible entertainment
        ]);
        TaxAdjustment::factory()->permanent()->forYear(2024)->create([
            'company_id' => $this->company->id,
            'accounting_amount' => 5000,
            'tax_amount' => 0, // Fines
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $permanentDifferences = $service->calculatePermanentDifferences(2024);

        // Both adjustments: (10000 - 0) + (5000 - 0) = 15000
        expect($permanentDifferences)->toBe(15000.00);
    });

    test('returns zero when no permanent differences', function () {
        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $result = $service->calculatePermanentDifferences(2024);

        expect($result)->toBe(0.00);
    });

    test('ignores temporary differences', function () {
        TaxAdjustment::factory()->permanent()->forYear(2024)->create([
            'company_id' => $this->company->id,
            'accounting_amount' => 10000,
            'tax_amount' => 0,
        ]);
        TaxAdjustment::factory()->temporaryDeductible()->forYear(2024)->create([
            'company_id' => $this->company->id,
            'accounting_amount' => 50000,
            'tax_amount' => 0,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $permanentDifferences = $service->calculatePermanentDifferences(2024);

        expect($permanentDifferences)->toBe(10000.00);
    });
});

describe('TaxCalculationService Temporary Differences', function () {
    test('calculates change in temporary differences', function () {
        // Use unique year to avoid interference from other tests
        $testYear = 2099;

        // Create temporary adjustment with explicit difference
        TaxAdjustment::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => $testYear,
            'adjustment_type' => 'temporary_deductible',
            'category' => 'provisions',
            'accounting_amount' => 20000,
            'tax_amount' => 0,
            'difference' => 20000,
        ]);

        // Create depreciation schedule with balances that result in 10000 depreciation
        // Group 'd' has 20% rate, so basis of 50000 gives 10000 depreciation
        TaxDepreciationSchedule::factory()->forYear($testYear)->forGroup('d')->create([
            'company_id' => $this->company->id,
            'opening_balance' => 50000,
            'additions' => 0,
            'disposals' => 0,
        ]);

        // Create depreciation difference adjustment (permanent, not counted in temporary sum)
        TaxAdjustment::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => $testYear,
            'adjustment_type' => 'permanent',
            'category' => 'depreciation_difference',
            'accounting_amount' => 15000,
            'tax_amount' => 0,
            'difference' => 15000,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $change = $service->calculateTemporaryDifferencesChange($testYear);

        // Temporary: 20000 + depreciation diff (15000 - 10000) = 25000
        expect($change)->toBe(25000.00);
    });
});

describe('TaxCalculationService Depreciation Difference', function () {
    test('calculates difference between accounting and tax depreciation', function () {
        // Tax depreciation from schedules - use specific values
        TaxDepreciationSchedule::factory()->forYear(2024)->forGroup('d')->create([
            'company_id' => $this->company->id,
            'opening_balance' => 250000,
            'additions' => 0,
            'disposals' => 0,
            'depreciation_amount' => 50000,
        ]);
        TaxDepreciationSchedule::factory()->forYear(2024)->forGroup('a')->create([
            'company_id' => $this->company->id,
            'opening_balance' => 100000,
            'additions' => 0,
            'disposals' => 0,
            'depreciation_amount' => 30000,
        ]);

        // Accounting depreciation stored as adjustment
        TaxAdjustment::factory()->depreciationDifference()->forYear(2024)->create([
            'company_id' => $this->company->id,
            'accounting_amount' => 70000,
            'tax_amount' => 0,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $difference = $service->calculateDepreciationDifference(2024);

        // Accounting (70000) - Tax (50000 + 30000) = -10000
        expect($difference)->toBe(-10000.00);
    });
});

describe('TaxCalculationService Deferred Tax', function () {
    test('calculates deferred tax change between years', function () {
        // Current year items
        DeferredTaxItem::factory()->asset()->forYear(2024)->create([
            'company_id' => $this->company->id,
            'accounting_value' => 100000,
            'tax_value' => 80000, // Creates deferred tax liability
        ]);

        // Previous year items
        DeferredTaxItem::factory()->asset()->forYear(2023)->create([
            'company_id' => $this->company->id,
            'accounting_value' => 90000,
            'tax_value' => 80000,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $change = $service->calculateDeferredTaxChange(2024);

        // Should calculate change between years
        expect($change)->not->toBe(0);
    });

    test('returns current year total when no previous year', function () {
        DeferredTaxItem::factory()->asset()->forYear(2024)->create([
            'company_id' => $this->company->id,
            'accounting_value' => 100000,
            'tax_value' => 80000,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $change = $service->calculateDeferredTaxChange(2024);

        expect($change)->not->toBe(0);
    });
});

describe('TaxCalculationService Deferred Tax Summary', function () {
    test('returns summary with assets and liabilities', function () {
        // Deferred tax asset (accounting < tax for assets)
        DeferredTaxItem::factory()->asset()->forYear(2024)->create([
            'company_id' => $this->company->id,
            'accounting_value' => 50000,
            'tax_value' => 80000, // Asset: accounting < tax = DTA
        ]);

        // Deferred tax liability (accounting > tax for assets)
        DeferredTaxItem::factory()->asset()->forYear(2024)->create([
            'company_id' => $this->company->id,
            'accounting_value' => 120000,
            'tax_value' => 100000, // Asset: accounting > tax = DTL
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $summary = $service->getDeferredTaxSummary(2024);

        expect($summary)->toBeArray()
            ->and($summary['year'])->toBe(2024)
            ->and($summary)->toHaveKeys(['items', 'deferred_tax_assets', 'deferred_tax_liabilities', 'net_deferred_tax']);
    });

    test('handles empty deferred tax items', function () {
        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $summary = $service->getDeferredTaxSummary(2024);

        expect($summary['deferred_tax_assets'])->toBe(0)
            ->and($summary['deferred_tax_liabilities'])->toBe(0)
            ->and($summary['net_deferred_tax'])->toBe(0);
    });
});

describe('TaxCalculationService Tax Adjustments Summary', function () {
    test('groups adjustments by type', function () {
        TaxAdjustment::factory()->permanent()->forYear(2024)->count(2)->create([
            'company_id' => $this->company->id,
        ]);
        TaxAdjustment::factory()->temporaryDeductible()->forYear(2024)->create([
            'company_id' => $this->company->id,
        ]);
        TaxAdjustment::factory()->temporaryTaxable()->forYear(2024)->create([
            'company_id' => $this->company->id,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $summary = $service->getTaxAdjustmentsSummary(2024);

        expect($summary)->toBeArray()
            ->and($summary['year'])->toBe(2024)
            ->and($summary)->toHaveKeys(['permanent', 'temporary_deductible', 'temporary_taxable', 'total_permanent', 'total_temporary'])
            ->and($summary['permanent']['items'])->toHaveCount(2)
            ->and($summary['temporary_deductible']['items'])->toHaveCount(1)
            ->and($summary['temporary_taxable']['items'])->toHaveCount(1);
    });
});

describe('TaxCalculationService Depreciation Schedules', function () {
    test('returns depreciation schedules for year', function () {
        TaxDepreciationSchedule::factory()->forYear(2024)->forGroup('a')->create([
            'company_id' => $this->company->id,
        ]);
        TaxDepreciationSchedule::factory()->forYear(2024)->forGroup('d')->create([
            'company_id' => $this->company->id,
        ]);
        TaxDepreciationSchedule::factory()->forYear(2023)->forGroup('a')->create([
            'company_id' => $this->company->id,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $schedules = $service->getDepreciationSchedules(2024);

        expect($schedules)->toHaveCount(2);
    });
});

describe('TaxCalculationService Validate Tax Return', function () {
    test('returns valid for correct tax return', function () {
        TaxDepreciationSchedule::factory()->forYear(2024)->create([
            'company_id' => $this->company->id,
        ]);

        $taxReturn = TaxReturn::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'accounting_profit' => 100000,
            'permanent_differences' => 5000,
            'temporary_differences_change' => 2000,
            'losses_used' => 0,
            'taxable_income' => 107000, // 100000 + 5000 + 2000
            'tax_rate' => 22.00,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $result = $service->validateTaxReturn($taxReturn);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty();
    });

    test('warns when accounting profit is zero', function () {
        TaxDepreciationSchedule::factory()->forYear(2024)->create([
            'company_id' => $this->company->id,
        ]);

        $taxReturn = TaxReturn::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'accounting_profit' => 0,
            'permanent_differences' => 0,
            'temporary_differences_change' => 0,
            'losses_used' => 0,
            'taxable_income' => 0,
            'tax_rate' => 22.00,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $result = $service->validateTaxReturn($taxReturn);

        expect($result['warnings'])->toContain('Regnskapsmessig resultat er null. Kontroller at dette er korrekt.');
    });

    test('warns when tax rate differs from standard', function () {
        TaxDepreciationSchedule::factory()->forYear(2024)->create([
            'company_id' => $this->company->id,
        ]);

        $taxReturn = TaxReturn::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'accounting_profit' => 100000,
            'tax_rate' => 25.00, // Non-standard rate
            'taxable_income' => 100000,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $result = $service->validateTaxReturn($taxReturn);

        expect($result['warnings'])->toContain('Skattesats avviker fra standard 22%. Kontroller at dette er korrekt.');
    });

    test('errors when no depreciation schedules exist', function () {
        $taxReturn = TaxReturn::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'accounting_profit' => 100000,
            'taxable_income' => 100000,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $result = $service->validateTaxReturn($taxReturn);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Ingen saldoavskrivninger er registrert for året.');
    });

    test('errors when calculated taxable income differs', function () {
        TaxDepreciationSchedule::factory()->forYear(2024)->create([
            'company_id' => $this->company->id,
        ]);

        $taxReturn = TaxReturn::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'accounting_profit' => 100000,
            'permanent_differences' => 5000,
            'temporary_differences_change' => 2000,
            'losses_used' => 0,
            'taxable_income' => 50000, // Wrong! Should be 107000
            'tax_rate' => 22.00,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $service = new TaxCalculationService($reportService);

        $result = $service->validateTaxReturn($taxReturn);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Beregnet skattepliktig inntekt stemmer ikke. Kjør beregning på nytt.');
    });
});

describe('TaxCalculationService Calculate Tax Return', function () {
    test('calculates complete tax return from accounting data', function () {
        // Create permanent adjustment
        TaxAdjustment::factory()->permanent()->forYear(2024)->create([
            'company_id' => $this->company->id,
            'accounting_amount' => 10000,
            'tax_amount' => 0,
        ]);

        // Create depreciation schedule
        TaxDepreciationSchedule::factory()->forYear(2024)->forGroup('d')->create([
            'company_id' => $this->company->id,
            'depreciation_amount' => 20000,
        ]);

        $taxReturn = TaxReturn::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'accounting_profit' => 0,
            'tax_rate' => 22.00,
        ]);

        $reportService = Mockery::mock(ReportService::class);
        $reportService->shouldReceive('getIncomeStatement')
            ->andReturn(['profit_before_tax' => 500000]);

        $service = new TaxCalculationService($reportService);

        $result = $service->calculateTaxReturn($taxReturn);

        expect((float) $result->accounting_profit)->toBe(500000.00)
            ->and((float) $result->permanent_differences)->toBe(10000.00)
            ->and($result->calculation_details)->toBeArray();
    });
});

describe('TaxReturn Model Calculations', function () {
    test('calculates tax correctly', function () {
        $taxReturn = TaxReturn::factory()->create([
            'company_id' => $this->company->id,
            'accounting_profit' => 1000000,
            'permanent_differences' => 50000,
            'temporary_differences_change' => 20000,
            'losses_brought_forward' => 0,
            'tax_rate' => 22.00,
            'deferred_tax_change' => 5000,
        ]);

        $taxReturn->calculateTax();

        // Taxable income = 1000000 + 50000 + 20000 = 1070000
        // Tax payable = 1070000 * 0.22 = 235400
        expect((float) $taxReturn->taxable_income)->toBe(1070000.00)
            ->and((float) $taxReturn->tax_payable)->toBe(235400.00)
            ->and((float) $taxReturn->total_tax_expense)->toBe(240400.00); // 235400 + 5000
    });

    test('uses losses to reduce taxable income', function () {
        $taxReturn = TaxReturn::factory()->create([
            'company_id' => $this->company->id,
            'accounting_profit' => 500000,
            'permanent_differences' => 0,
            'temporary_differences_change' => 0,
            'losses_brought_forward' => 200000,
            'tax_rate' => 22.00,
            'deferred_tax_change' => 0,
        ]);

        $taxReturn->calculateTax();

        // Taxable before losses = 500000
        // Losses used = min(500000, 200000) = 200000
        // Taxable income = 500000 - 200000 = 300000
        expect((float) $taxReturn->losses_used)->toBe(200000.00)
            ->and((float) $taxReturn->taxable_income)->toBe(300000.00)
            ->and((float) $taxReturn->losses_carried_forward)->toBe(0.00);
    });

    test('carries forward unused losses', function () {
        $taxReturn = TaxReturn::factory()->create([
            'company_id' => $this->company->id,
            'accounting_profit' => 100000,
            'permanent_differences' => 0,
            'temporary_differences_change' => 0,
            'losses_brought_forward' => 300000,
            'tax_rate' => 22.00,
            'deferred_tax_change' => 0,
        ]);

        $taxReturn->calculateTax();

        // Only 100000 of losses can be used
        expect((float) $taxReturn->losses_used)->toBe(100000.00)
            ->and((float) $taxReturn->taxable_income)->toBe(0.00)
            ->and((float) $taxReturn->losses_carried_forward)->toBe(200000.00);
    });

    test('adds to losses when result is negative', function () {
        $taxReturn = TaxReturn::factory()->create([
            'company_id' => $this->company->id,
            'accounting_profit' => -150000,
            'permanent_differences' => 0,
            'temporary_differences_change' => 0,
            'losses_brought_forward' => 50000,
            'tax_rate' => 22.00,
            'deferred_tax_change' => 0,
        ]);

        $taxReturn->calculateTax();

        // Negative result: no losses used, carry forward increases
        expect((float) $taxReturn->losses_used)->toBe(0.00)
            ->and((float) $taxReturn->taxable_income)->toBe(0.00)
            ->and((float) $taxReturn->losses_carried_forward)->toBe(200000.00); // 50000 + 150000
    });

    test('calculates effective tax rate', function () {
        $taxReturn = TaxReturn::factory()->create([
            'company_id' => $this->company->id,
            'accounting_profit' => 1000000,
            'total_tax_expense' => 220000,
        ]);

        expect($taxReturn->getEffectiveTaxRate())->toBe(22.00);
    });

    test('returns zero effective rate when no profit', function () {
        $taxReturn = TaxReturn::factory()->create([
            'company_id' => $this->company->id,
            'accounting_profit' => 0,
            'total_tax_expense' => 0,
        ]);

        expect($taxReturn->getEffectiveTaxRate())->toBe(0.0);
    });
});
