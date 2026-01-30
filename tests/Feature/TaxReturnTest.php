<?php

use App\Models\TaxReturn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// Basic Creation
test('tax return can be created', function () {
    $taxReturn = TaxReturn::factory()->create([
        'fiscal_year' => 2024,
        'accounting_profit' => 1000000,
        'tax_rate' => 22.00,
    ]);

    expect($taxReturn)->toBeInstanceOf(TaxReturn::class);
    expect($taxReturn->fiscal_year)->toBe(2024);
    expect((float) $taxReturn->accounting_profit)->toBe(1000000.0);
    expect((float) $taxReturn->tax_rate)->toBe(22.0);
});

// Tax Rate Constant
test('tax return has correct norwegian tax rate constant', function () {
    expect(TaxReturn::TAX_RATE)->toBe(22.00);
});

// Status Labels
test('tax return status label returns correct norwegian translation', function (string $status, string $expectedLabel) {
    $taxReturn = TaxReturn::factory()->create(['status' => $status]);

    expect($taxReturn->getStatusLabel())->toBe($expectedLabel);
})->with([
    ['draft', 'Utkast'],
    ['ready', 'Klar for innsending'],
    ['submitted', 'Sendt inn'],
]);

// Status Badge Colors
test('tax return status badge color returns correct color', function (string $status, string $expectedColor) {
    $taxReturn = TaxReturn::factory()->create(['status' => $status]);

    expect($taxReturn->getStatusBadgeColor())->toBe($expectedColor);
})->with([
    ['draft', 'warning'],
    ['ready', 'info'],
    ['submitted', 'success'],
]);

// Formatted Values
test('tax return formatted accounting profit includes currency', function () {
    $taxReturn = TaxReturn::factory()->create(['accounting_profit' => 1500000.00]);

    expect($taxReturn->getFormattedAccountingProfit())->toBe('1 500 000,00 NOK');
});

test('tax return formatted taxable income includes currency', function () {
    $taxReturn = TaxReturn::factory()->create(['taxable_income' => 1200000.00]);

    expect($taxReturn->getFormattedTaxableIncome())->toBe('1 200 000,00 NOK');
});

test('tax return formatted tax payable includes currency', function () {
    $taxReturn = TaxReturn::factory()->create(['tax_payable' => 264000.00]);

    expect($taxReturn->getFormattedTaxPayable())->toBe('264 000,00 NOK');
});

// Report Period
test('tax return report period formats correctly', function () {
    $taxReturn = TaxReturn::factory()->create([
        'period_start' => '2024-01-01',
        'period_end' => '2024-12-31',
    ]);

    expect($taxReturn->getReportPeriod())->toBe('01.01.2024 - 31.12.2024');
});

// Deadline
test('tax return deadline is may 31 next year', function () {
    $taxReturn = TaxReturn::factory()->create(['fiscal_year' => 2024]);

    $deadline = $taxReturn->getDeadline();

    expect($deadline->year)->toBe(2025);
    expect($deadline->month)->toBe(5);
    expect($deadline->day)->toBe(31);
});

// Overdue
test('tax return is overdue when past deadline and not submitted', function () {
    $taxReturn = TaxReturn::factory()->create([
        'fiscal_year' => 2020,
        'status' => 'draft',
    ]);

    expect($taxReturn->isOverdue())->toBeTrue();
});

test('tax return is not overdue when submitted', function () {
    $taxReturn = TaxReturn::factory()->submitted()->create([
        'fiscal_year' => 2020,
    ]);

    expect($taxReturn->isOverdue())->toBeFalse();
});

// Relationships
test('tax return belongs to creator', function () {
    $creator = User::factory()->create();
    $taxReturn = TaxReturn::factory()->create(['created_by' => $creator->id]);

    expect($taxReturn->creator->id)->toBe($creator->id);
});

// Status Methods
test('tax return isDraft returns true when status is draft', function () {
    $taxReturn = TaxReturn::factory()->create(['status' => 'draft']);

    expect($taxReturn->isDraft())->toBeTrue();
});

test('tax return isReady returns true when status is ready', function () {
    $taxReturn = TaxReturn::factory()->ready()->create();

    expect($taxReturn->isReady())->toBeTrue();
});

test('tax return isSubmitted returns true when status is submitted', function () {
    $taxReturn = TaxReturn::factory()->submitted()->create();

    expect($taxReturn->isSubmitted())->toBeTrue();
});

test('tax return canBeEdited returns true for draft or ready status', function () {
    $draft = TaxReturn::factory()->create(['status' => 'draft']);
    $ready = TaxReturn::factory()->ready()->create();
    $submitted = TaxReturn::factory()->submitted()->create();

    expect($draft->canBeEdited())->toBeTrue();
    expect($ready->canBeEdited())->toBeTrue();
    expect($submitted->canBeEdited())->toBeFalse();
});

test('tax return canBeSubmitted returns true only for ready status', function () {
    $draft = TaxReturn::factory()->create(['status' => 'draft']);
    $ready = TaxReturn::factory()->ready()->create();
    $submitted = TaxReturn::factory()->submitted()->create();

    expect($draft->canBeSubmitted())->toBeFalse();
    expect($ready->canBeSubmitted())->toBeTrue();
    expect($submitted->canBeSubmitted())->toBeFalse();
});

// Status Transitions
test('tax return can be marked as ready', function () {
    $taxReturn = TaxReturn::factory()->create(['status' => 'draft']);

    $taxReturn->markAsReady();

    expect($taxReturn->status)->toBe('ready');
});

test('tax return can be marked as submitted', function () {
    $taxReturn = TaxReturn::factory()->ready()->create();

    $taxReturn->markAsSubmitted();

    expect($taxReturn->status)->toBe('submitted');
});

test('tax return can be marked as draft', function () {
    $taxReturn = TaxReturn::factory()->ready()->create();

    $taxReturn->markAsDraft();

    expect($taxReturn->status)->toBe('draft');
});

// Tax Calculation
test('tax return calculates tax correctly', function () {
    $taxReturn = TaxReturn::factory()->create([
        'accounting_profit' => 1000000,
        'permanent_differences' => 50000,
        'temporary_differences_change' => 0,
        'tax_rate' => 22.00,
        'losses_brought_forward' => 0,
        'deferred_tax_change' => 0,
    ]);

    $taxReturn->calculateTax();

    // Taxable income = 1000000 + 50000 = 1050000
    expect((float) $taxReturn->taxable_income)->toBe(1050000.0);
    // Tax payable = 1050000 * 0.22 = 231000
    expect((float) $taxReturn->tax_payable)->toBe(231000.0);
});

test('tax return uses losses to reduce taxable income', function () {
    $taxReturn = TaxReturn::factory()->withLosses(200000)->create([
        'accounting_profit' => 500000,
        'permanent_differences' => 0,
        'temporary_differences_change' => 0,
        'tax_rate' => 22.00,
        'deferred_tax_change' => 0,
    ]);

    $taxReturn->calculateTax();

    // Taxable before losses = 500000, losses = 200000
    expect((float) $taxReturn->losses_used)->toBe(200000.0);
    expect((float) $taxReturn->taxable_income)->toBe(300000.0);
    expect((float) $taxReturn->losses_carried_forward)->toBe(0.0);
});

test('tax return effective tax rate calculates correctly', function () {
    $taxReturn = TaxReturn::factory()->create([
        'accounting_profit' => 1000000,
        'total_tax_expense' => 220000,
    ]);

    expect($taxReturn->getEffectiveTaxRate())->toBe(22.0);
});

test('tax return effective tax rate is zero when no profit', function () {
    $taxReturn = TaxReturn::factory()->create([
        'accounting_profit' => 0,
        'total_tax_expense' => 0,
    ]);

    expect($taxReturn->getEffectiveTaxRate())->toBe(0.0);
});

// Scopes
test('forYear scope filters correctly', function () {
    TaxReturn::factory()->create(['fiscal_year' => 2024]);
    TaxReturn::factory()->create(['fiscal_year' => 2023]);
    TaxReturn::factory()->create(['fiscal_year' => 2022]);

    expect(TaxReturn::forYear(2024)->count())->toBe(1);
    expect(TaxReturn::forYear(2023)->count())->toBe(1);
});

test('byStatus scope filters correctly', function () {
    TaxReturn::factory()->create(['fiscal_year' => 2020, 'status' => 'draft']);
    TaxReturn::factory()->create(['fiscal_year' => 2021, 'status' => 'draft']);
    TaxReturn::factory()->submitted()->create(['fiscal_year' => 2022]);
    TaxReturn::factory()->submitted()->create(['fiscal_year' => 2023]);
    TaxReturn::factory()->submitted()->create(['fiscal_year' => 2024]);

    expect(TaxReturn::byStatus('draft')->count())->toBe(2);
    expect(TaxReturn::byStatus('submitted')->count())->toBe(3);
});

test('draft scope filters correctly', function () {
    TaxReturn::factory()->create(['fiscal_year' => 2020, 'status' => 'draft']);
    TaxReturn::factory()->create(['fiscal_year' => 2021, 'status' => 'draft']);
    TaxReturn::factory()->ready()->create(['fiscal_year' => 2022]);
    TaxReturn::factory()->ready()->create(['fiscal_year' => 2023]);
    TaxReturn::factory()->ready()->create(['fiscal_year' => 2024]);

    expect(TaxReturn::draft()->count())->toBe(2);
});

// Soft Deletes
test('tax return can be soft deleted', function () {
    $taxReturn = TaxReturn::factory()->create();

    $taxReturn->delete();

    expect($taxReturn->trashed())->toBeTrue();
    expect(TaxReturn::count())->toBe(0);
    expect(TaxReturn::withTrashed()->count())->toBe(1);
});

// Factory States
test('tax return factory ready state works', function () {
    $taxReturn = TaxReturn::factory()->ready()->create();

    expect($taxReturn->status)->toBe('ready');
});

test('tax return factory submitted state works', function () {
    $taxReturn = TaxReturn::factory()->submitted()->create();

    expect($taxReturn->status)->toBe('submitted');
});

test('tax return factory withLosses state works', function () {
    $taxReturn = TaxReturn::factory()->withLosses(150000)->create();

    expect((float) $taxReturn->losses_brought_forward)->toBe(150000.0);
});
