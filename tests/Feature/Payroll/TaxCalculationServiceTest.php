<?php

use App\Models\EmployeePayrollSettings;
use App\Services\Payroll\TaxCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = createTestCompanyContext();
    $this->service = app(TaxCalculationService::class);
});

// Tabelltrekk tests
test('calculates tabelltrekk for low income', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'skatt_type' => EmployeePayrollSettings::SKATT_TYPE_TABELLTREKK,
        'skattetabell' => '7100',
    ]);

    // 15000 is <= 20000, so rate is 0%
    $tax = $this->service->calculateForskuddstrekk(15000, $settings);

    expect($tax)->toBe(0.0); // 15000 * 0% = 0
});

test('calculates tabelltrekk for medium income', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'skatt_type' => EmployeePayrollSettings::SKATT_TYPE_TABELLTREKK,
        'skattetabell' => '7100',
    ]);

    $tax = $this->service->calculateForskuddstrekk(50000, $settings);

    // Using table 7100 bracket for 40000-50000: 30%
    // Actually at 50000 it's 33%
    expect($tax)->toBe(16500.0); // 50000 * 33% = 16500
});

test('calculates tabelltrekk for high income', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'skatt_type' => EmployeePayrollSettings::SKATT_TYPE_TABELLTREKK,
        'skattetabell' => '7100',
    ]);

    $tax = $this->service->calculateForskuddstrekk(100000, $settings);

    // Using table 7100 bracket for 80000-100000: 38% -> 100000: 40%
    expect($tax)->toBe(40000.0); // 100000 * 40% = 40000
});

test('calculates tabelltrekk with table 6 adjustment', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'skatt_type' => EmployeePayrollSettings::SKATT_TYPE_TABELLTREKK,
        'skattetabell' => '6100',
    ]);

    $tax = $this->service->calculateForskuddstrekk(50000, $settings);

    // Table 6 has -2% adjustment: 33% - 2% = 31%
    expect($tax)->toBe(15500.0); // 50000 * 31% = 15500
});

test('calculates tabelltrekk with table 8 adjustment', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'skatt_type' => EmployeePayrollSettings::SKATT_TYPE_TABELLTREKK,
        'skattetabell' => '8100',
    ]);

    $tax = $this->service->calculateForskuddstrekk(50000, $settings);

    // Table 8 has +2% adjustment: 33% + 2% = 35%
    expect($tax)->toBe(17500.0); // 50000 * 35% = 17500
});

// Prosenttrekk tests
test('calculates prosenttrekk correctly', function () {
    $settings = EmployeePayrollSettings::factory()->withProsenttrekk(30)->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
    ]);

    $tax = $this->service->calculateForskuddstrekk(50000, $settings);

    expect($tax)->toBe(15000.0); // 50000 * 30% = 15000
});

test('calculates prosenttrekk with custom percentage', function () {
    $settings = EmployeePayrollSettings::factory()->withProsenttrekk(45)->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
    ]);

    $tax = $this->service->calculateForskuddstrekk(50000, $settings);

    expect($tax)->toBe(22500.0); // 50000 * 45% = 22500
});

test('uses default 30% when prosenttrekk is null', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'skatt_type' => EmployeePayrollSettings::SKATT_TYPE_PROSENTTREKK,
        'skatteprosent' => null,
    ]);

    $tax = $this->service->calculateForskuddstrekk(50000, $settings);

    expect($tax)->toBe(15000.0); // 50000 * 30% = 15000
});

// Kildeskatt tests
test('calculates kildeskatt at 25%', function () {
    $settings = EmployeePayrollSettings::factory()->withKildeskatt()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
    ]);

    $tax = $this->service->calculateForskuddstrekk(50000, $settings);

    expect($tax)->toBe(12500.0); // 50000 * 25% = 12500
});

test('calculateKildeskatt method returns 25%', function () {
    $tax = $this->service->calculateKildeskatt(50000);

    expect($tax)->toBe(12500.0);
});

// Frikort tests
test('calculates frikort with remaining amount', function () {
    $settings = EmployeePayrollSettings::factory()->withFrikort(65000)->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'frikort_brukt' => 0,
    ]);

    $tax = $this->service->calculateForskuddstrekk(50000, $settings);

    // Entire salary within frikort limit, no tax
    expect($tax)->toBe(0.0);
});

test('calculates frikort when partially used', function () {
    $settings = EmployeePayrollSettings::factory()->withFrikort(65000)->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'frikort_brukt' => 40000, // 25000 remaining
    ]);

    $tax = $this->service->calculateForskuddstrekk(50000, $settings);

    // 25000 within frikort (no tax), 25000 taxed at 50%
    expect($tax)->toBe(12500.0); // 25000 * 50% = 12500
});

test('calculates frikort when fully exhausted', function () {
    $settings = EmployeePayrollSettings::factory()->withFrikort(65000)->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'frikort_brukt' => 65000, // Fully used
    ]);

    $tax = $this->service->calculateForskuddstrekk(50000, $settings);

    // Entire salary taxed at 50%
    expect($tax)->toBe(25000.0); // 50000 * 50% = 25000
});

// Utility method tests
test('getTaxTableRate returns correct bracket rates', function () {
    // Brackets: <=20000->0%, <=30000->25%, <=40000->30%, <=50000->33%, <=60000->35%, <=80000->38%, <=100000->40%, <=150000->43%, >150000->45%
    expect($this->service->getTaxTableRate('7100', 0))->toBe(0.0);       // <= 20000
    expect($this->service->getTaxTableRate('7100', 15000))->toBe(0.0);   // <= 20000
    expect($this->service->getTaxTableRate('7100', 20000))->toBe(0.0);   // Exactly at threshold
    expect($this->service->getTaxTableRate('7100', 25000))->toBe(25.0);  // > 20000, <= 30000
    expect($this->service->getTaxTableRate('7100', 50000))->toBe(33.0);  // Exactly at 50000
    expect($this->service->getTaxTableRate('7100', 100000))->toBe(40.0); // Exactly at 100000
    expect($this->service->getTaxTableRate('7100', 200000))->toBe(45.0); // Above 150000
});

test('updates frikort usage correctly', function () {
    $settings = EmployeePayrollSettings::factory()->withFrikort(65000)->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'frikort_brukt' => 0,
    ]);

    $this->service->updateFrikortUsage($settings, 25000);

    expect($settings->fresh()->frikort_brukt)->toBe('25000.00');
});

test('updateFrikortUsage only works for frikort type', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'skatt_type' => EmployeePayrollSettings::SKATT_TYPE_TABELLTREKK,
        'frikort_brukt' => 0,
    ]);

    $this->service->updateFrikortUsage($settings, 25000);

    expect($settings->fresh()->frikort_brukt)->toBe('0.00');
});

test('resets frikort usage for new year', function () {
    $settings = EmployeePayrollSettings::factory()->withFrikort(65000)->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'frikort_brukt' => 50000,
    ]);

    $this->service->resetFrikortUsage($settings);

    expect($settings->fresh()->frikort_brukt)->toBe('0.00');
});

test('resetFrikortUsage only resets for frikort type', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'skatt_type' => EmployeePayrollSettings::SKATT_TYPE_TABELLTREKK,
        'frikort_brukt' => 50000,
    ]);

    $this->service->resetFrikortUsage($settings);

    expect($settings->fresh()->frikort_brukt)->toBe('50000.00');
});

test('checks tax card needs renewal when no expiry date', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'skattekort_gyldig_til' => null,
    ]);

    expect($this->service->needsTaxCardRenewal($settings))->toBeTrue();
});

test('checks tax card needs renewal when about to expire', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'skattekort_gyldig_til' => now()->addDays(15),
    ]);

    expect($this->service->needsTaxCardRenewal($settings))->toBeTrue();
});

test('checks tax card does not need renewal when valid', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'skattekort_gyldig_til' => now()->addDays(60),
    ]);

    expect($this->service->needsTaxCardRenewal($settings))->toBeFalse();
});

test('gets tax info summary', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'skatt_type' => EmployeePayrollSettings::SKATT_TYPE_TABELLTREKK,
        'skattetabell' => '7100',
        'skatteprosent' => null,
        'skattekort_gyldig_til' => now()->addMonths(6),
    ]);

    $summary = $this->service->getTaxInfoSummary($settings);

    expect($summary['type'])->toBe('Tabelltrekk');
    expect($summary['table'])->toBe('7100');
    expect($summary['valid_until'])->not->toBeNull();
});

test('gets tax info summary for frikort', function () {
    $settings = EmployeePayrollSettings::factory()->withFrikort(65000)->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'frikort_brukt' => 20000,
    ]);

    $summary = $this->service->getTaxInfoSummary($settings);

    expect($summary['type'])->toBe('Frikort');
    expect($summary['frikort_remaining'])->toBe(45000.0); // 65000 - 20000
});
