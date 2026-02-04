<?php

use App\Models\EmployeePayrollSettings;
use App\Models\HolidayPayBalance;
use App\Models\PayrollEntry;
use App\Models\PayrollRun;
use App\Services\Payroll\HolidayPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = createTestCompanyContext();
    $this->actingAs($this->user);
    $this->service = app(HolidayPayService::class);
});

test('has correct standard rate constant', function () {
    expect(HolidayPayService::STANDARD_RATE)->toBe(10.2);
});

test('has correct five weeks rate constant', function () {
    expect(HolidayPayService::FIVE_WEEKS_RATE)->toBe(12.0);
});

test('has correct over 60 addition constant', function () {
    expect(HolidayPayService::OVER_60_ADDITION)->toBe(2.3);
});

test('calculates feriepenger grunnlag correctly', function () {
    $run = PayrollRun::factory()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->create([
        'company_id' => $this->company->id,
        'payroll_run_id' => $run->id,
        'user_id' => $this->user->id,
        'grunnlonn' => 40000,
        'overtid_belop' => 5000,
        'bonus' => 2000,
        'tillegg' => 1000,
    ]);

    $grunnlag = $this->service->calculateFeriepengerGrunnlag($entry);

    expect($grunnlag)->toBe(48000.0); // 40000 + 5000 + 2000 + 1000
});

test('calculates standard holiday pay accrual', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'ferie_5_uker' => false,
        'over_60' => false,
    ]);

    $avsetning = $this->service->calculateFeriepengerAvsetning(50000, $settings);

    expect($avsetning)->toBe(5100.00); // 50000 * 10.2% = 5100
});

test('calculates 5 weeks holiday pay accrual', function () {
    $settings = EmployeePayrollSettings::factory()->withFiveWeeksHoliday()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
    ]);

    $avsetning = $this->service->calculateFeriepengerAvsetning(50000, $settings);

    expect($avsetning)->toBe(6000.00); // 50000 * 12% = 6000
});

test('calculates over 60 holiday pay accrual', function () {
    $settings = EmployeePayrollSettings::factory()->over60()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
    ]);

    $avsetning = $this->service->calculateFeriepengerAvsetning(50000, $settings);

    expect($avsetning)->toBe(6250.00); // 50000 * 12.5% = 6250
});

test('calculates over 60 with 5 weeks holiday pay accrual', function () {
    $settings = EmployeePayrollSettings::factory()
        ->withFiveWeeksHoliday()
        ->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'over_60' => true,
        ]);

    $avsetning = $this->service->calculateFeriepengerAvsetning(50000, $settings);

    expect($avsetning)->toBe(7150.00); // 50000 * 14.3% = 7150
});

test('getEffectiveRate returns standard rate', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'ferie_5_uker' => false,
        'over_60' => false,
    ]);

    $rate = $this->service->getEffectiveRate($settings);

    expect($rate)->toBe(10.2);
});

test('getEffectiveRate returns 5 weeks rate', function () {
    $settings = EmployeePayrollSettings::factory()->withFiveWeeksHoliday()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
    ]);

    $rate = $this->service->getEffectiveRate($settings);

    expect($rate)->toBe(12.0);
});

test('getEffectiveRate returns over 60 rate', function () {
    $settings = EmployeePayrollSettings::factory()->over60()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'ferie_5_uker' => false,
    ]);

    $rate = $this->service->getEffectiveRate($settings);

    expect($rate)->toBe(12.5); // 10.2 + 2.3
});

test('getEffectiveRate returns over 60 with 5 weeks rate', function () {
    $settings = EmployeePayrollSettings::factory()
        ->withFiveWeeksHoliday()
        ->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'over_60' => true,
        ]);

    $rate = $this->service->getEffectiveRate($settings);

    expect($rate)->toBe(14.3);
});

test('records accrual creates new balance', function () {
    $run = PayrollRun::factory()->create([
        'company_id' => $this->company->id,
        'year' => 2026,
    ]);
    $entry = PayrollEntry::factory()->create([
        'company_id' => $this->company->id,
        'payroll_run_id' => $run->id,
        'user_id' => $this->user->id,
        'feriepenger_grunnlag' => 50000,
        'feriepenger_avsetning' => 5100,
    ]);

    $balance = $this->service->recordAccrual($entry);

    expect($balance->opptjeningsaar)->toBe(2026);
    expect($balance->grunnlag)->toBe('50000.00');
    expect($balance->opptjent)->toBe('5100.00');
    expect($balance->gjenstaaende)->toBe('5100.00');
});

test('records accrual adds to existing balance', function () {
    $run = PayrollRun::factory()->create([
        'company_id' => $this->company->id,
        'year' => 2026,
    ]);

    // Create initial balance
    HolidayPayBalance::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'opptjeningsaar' => 2026,
        'grunnlag' => 100000,
        'opptjent' => 10200,
        'utbetalt' => 0,
        'gjenstaaende' => 10200,
    ]);

    $entry = PayrollEntry::factory()->create([
        'company_id' => $this->company->id,
        'payroll_run_id' => $run->id,
        'user_id' => $this->user->id,
        'feriepenger_grunnlag' => 50000,
        'feriepenger_avsetning' => 5100,
    ]);

    $balance = $this->service->recordAccrual($entry);

    expect($balance->grunnlag)->toBe('150000.00');
    expect($balance->opptjent)->toBe('15300.00');
    expect($balance->gjenstaaende)->toBe('15300.00');
});

test('processes holiday pay payout', function () {
    HolidayPayBalance::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'opptjeningsaar' => 2025,
        'grunnlag' => 600000,
        'opptjent' => 61200,
        'utbetalt' => 0,
        'gjenstaaende' => 61200,
    ]);

    $payout = $this->service->processHolidayPayPayout($this->user, 2025);

    expect($payout)->toBe(61200.0);

    $balance = HolidayPayBalance::where('user_id', $this->user->id)
        ->where('opptjeningsaar', 2025)
        ->first();

    expect($balance->utbetalt)->toBe('61200.00');
    expect($balance->gjenstaaende)->toBe('0.00');
});

test('processes holiday pay payout returns zero when no balance', function () {
    $payout = $this->service->processHolidayPayPayout($this->user, 2025);

    expect($payout)->toBe(0.0);
});

test('processes holiday pay payout returns zero when balance is zero', function () {
    HolidayPayBalance::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'opptjeningsaar' => 2025,
        'grunnlag' => 600000,
        'opptjent' => 61200,
        'utbetalt' => 61200,
        'gjenstaaende' => 0,
    ]);

    $payout = $this->service->processHolidayPayPayout($this->user, 2025);

    expect($payout)->toBe(0.0);
});

test('gets balance for user and year', function () {
    HolidayPayBalance::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'opptjeningsaar' => 2025,
        'grunnlag' => 600000,
        'opptjent' => 61200,
        'utbetalt' => 0,
        'gjenstaaende' => 61200,
    ]);

    $balance = $this->service->getBalance($this->user, 2025);

    expect($balance)->not->toBeNull();
    expect($balance->opptjeningsaar)->toBe(2025);
});

test('gets all balances for user', function () {
    HolidayPayBalance::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'opptjeningsaar' => 2025,
        'grunnlag' => 600000,
        'opptjent' => 61200,
        'utbetalt' => 0,
        'gjenstaaende' => 61200,
    ]);
    HolidayPayBalance::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'opptjeningsaar' => 2024,
        'grunnlag' => 550000,
        'opptjent' => 56100,
        'utbetalt' => 56100,
        'gjenstaaende' => 0,
    ]);

    $balances = $this->service->getAllBalances($this->user);

    expect($balances)->toHaveCount(2);
    expect($balances->first()->opptjeningsaar)->toBe(2025); // Ordered desc
});

test('gets total remaining holiday pay', function () {
    HolidayPayBalance::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'opptjeningsaar' => 2025,
        'grunnlag' => 600000,
        'opptjent' => 61200,
        'utbetalt' => 0,
        'gjenstaaende' => 61200,
    ]);
    HolidayPayBalance::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'opptjeningsaar' => 2024,
        'grunnlag' => 500000,
        'opptjent' => 51000,
        'utbetalt' => 20000,
        'gjenstaaende' => 31000,
    ]);

    $total = $this->service->getTotalRemaining($this->user);

    expect((float) $total)->toBe(92200.0); // 61200 + 31000
});

test('calculates expected payout for year', function () {
    HolidayPayBalance::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'opptjeningsaar' => 2025,
        'grunnlag' => 600000,
        'opptjent' => 61200,
        'utbetalt' => 0,
        'gjenstaaende' => 61200,
    ]);

    $expected = $this->service->calculateExpectedPayout($this->user, 2025);

    expect((float) $expected)->toBe(61200.0);
});

test('calculates expected payout returns zero when no balance', function () {
    $expected = $this->service->calculateExpectedPayout($this->user, 2025);

    expect($expected)->toBe(0.0);
});
