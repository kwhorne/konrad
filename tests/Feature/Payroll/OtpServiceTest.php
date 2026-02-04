<?php

use App\Models\EmployeePayrollSettings;
use App\Services\Payroll\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = createTestCompanyContext();
    $this->service = app(OtpService::class);
});

test('has correct G-belop constant', function () {
    expect(OtpService::G_BELOP)->toBe(130160);
});

test('has correct OTP max G constant', function () {
    expect(OtpService::OTP_MAX_G)->toBe(12);
});

test('has correct minimum OTP percentage', function () {
    expect(OtpService::MIN_OTP_PROSENT)->toBe(2.0);
});

test('has correct maximum OTP percentage', function () {
    expect(OtpService::MAX_OTP_PROSENT)->toBe(7.0);
});

test('calculates OTP correctly with minimum rate', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'otp_enabled' => true,
        'otp_prosent' => 2.0,
    ]);

    $otp = $this->service->calculateOtp(600000, $settings);

    expect($otp)->toBe(12000.00); // 600000 * 2% = 12000
});

test('calculates OTP correctly with 5% rate', function () {
    $settings = EmployeePayrollSettings::factory()->withOtp(5.0)->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
    ]);

    $otp = $this->service->calculateOtp(600000, $settings);

    expect($otp)->toBe(30000.00); // 600000 * 5% = 30000
});

test('caps OTP basis at 12G', function () {
    $settings = EmployeePayrollSettings::factory()->withOtp(2.0)->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
    ]);

    // Salary exceeds 12G (1,561,920)
    $otp = $this->service->calculateOtp(2000000, $settings);

    $maxBasis = OtpService::G_BELOP * OtpService::OTP_MAX_G; // 1,561,920
    $expectedOtp = $maxBasis * 0.02; // 31,238.40

    expect($otp)->toBe($expectedOtp);
});

test('returns zero when OTP is disabled', function () {
    $settings = EmployeePayrollSettings::factory()->withoutOtp()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
    ]);

    $otp = $this->service->calculateOtp(600000, $settings);

    expect($otp)->toBe(0.0);
});

test('calculates monthly OTP correctly', function () {
    $settings = EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'otp_enabled' => true,
        'otp_prosent' => 2.0,
    ]);

    $monthlyOtp = $this->service->calculateMonthlyOtp(50000, $settings);

    expect($monthlyOtp)->toBe(1000.00); // 50000 * 2% = 1000
});

test('calculates monthly OTP with capping for high salary', function () {
    $settings = EmployeePayrollSettings::factory()->withOtp(2.0)->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
    ]);

    // Monthly salary of 150000 = yearly 1,800,000 which exceeds 12G
    $monthlyOtp = $this->service->calculateMonthlyOtp(150000, $settings);

    $maxBasis = OtpService::G_BELOP * OtpService::OTP_MAX_G; // 1,561,920
    $monthlyBasis = $maxBasis / 12;
    $expectedOtp = round($monthlyBasis * 0.02, 2); // 2602.93

    expect($monthlyOtp)->toBe($expectedOtp);
});

test('getOtpBasis returns full amount below cap', function () {
    $basis = $this->service->getOtpBasis(600000);

    expect($basis)->toBe(600000.0);
});

test('getOtpBasis returns capped amount above 12G', function () {
    $basis = $this->service->getOtpBasis(2000000);

    expect($basis)->toBe(1561920.0); // 12 * 130160
});

test('exceedsOtpCap returns false for salary below cap', function () {
    expect($this->service->exceedsOtpCap(1000000))->toBeFalse();
});

test('exceedsOtpCap returns true for salary above cap', function () {
    expect($this->service->exceedsOtpCap(2000000))->toBeTrue();
});

test('exceedsOtpCap returns false for salary exactly at cap', function () {
    $maxBasis = OtpService::G_BELOP * OtpService::OTP_MAX_G;

    expect($this->service->exceedsOtpCap($maxBasis))->toBeFalse();
});

test('getGBelop returns current G value', function () {
    expect($this->service->getGBelop())->toBe(130160);
});

test('getMaxOtpBasis returns 12G', function () {
    expect($this->service->getMaxOtpBasis())->toBe(1561920.0);
});

test('calculateTotalOtpCost sums correctly for multiple employees', function () {
    $employees = [
        ['aarslonn' => 600000, 'otp_prosent' => 2.0],
        ['aarslonn' => 700000, 'otp_prosent' => 5.0],
        ['aarslonn' => 500000, 'otp_prosent' => 2.0],
    ];

    $total = $this->service->calculateTotalOtpCost($employees);

    // (600000 * 2%) + (700000 * 5%) + (500000 * 2%) = 12000 + 35000 + 10000 = 57000
    expect($total)->toBe(57000.00);
});

test('calculateTotalOtpCost caps individual salaries', function () {
    $employees = [
        ['aarslonn' => 2000000, 'otp_prosent' => 2.0], // Exceeds cap
    ];

    $total = $this->service->calculateTotalOtpCost($employees);

    // Capped at 1,561,920 * 2% = 31,238.40
    expect($total)->toBe(31238.40);
});

test('calculateTotalOtpCost uses default rate when not specified', function () {
    $employees = [
        ['aarslonn' => 600000], // No otp_prosent specified
    ];

    $total = $this->service->calculateTotalOtpCost($employees);

    // Uses MIN_OTP_PROSENT (2%): 600000 * 2% = 12000
    expect($total)->toBe(12000.00);
});
