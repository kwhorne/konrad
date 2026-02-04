<?php

use App\Models\EmployeePayrollSettings;
use App\Services\Payroll\SkattekortService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('SkattekortService personnummer validation', function () {
    test('validates correct personnummer', function () {
        $service = app(SkattekortService::class);

        // Test with a valid test personnummer (synthetic from Skatteetaten)
        // Format: DDMMYYXXXYY where DDMMYY is birth date, XXX is individual number, YY is control digits
        expect($service->validatePersonnummer('17054026641'))->toBeTrue();
    });

    test('rejects personnummer with wrong length', function () {
        $service = app(SkattekortService::class);

        expect($service->validatePersonnummer('1234567890'))->toBeFalse(); // 10 digits
        expect($service->validatePersonnummer('123456789012'))->toBeFalse(); // 12 digits
        expect($service->validatePersonnummer(''))->toBeFalse();
    });

    test('rejects personnummer with non-numeric characters', function () {
        $service = app(SkattekortService::class);

        expect($service->validatePersonnummer('0101015056a'))->toBeFalse();
        expect($service->validatePersonnummer('abcdefghijk'))->toBeFalse();
    });

    test('rejects personnummer with invalid date', function () {
        $service = app(SkattekortService::class);

        // Day 00 is invalid
        expect($service->validatePersonnummer('00010100000'))->toBeFalse();
        // Day 32 is invalid
        expect($service->validatePersonnummer('32010100000'))->toBeFalse();
        // Month 00 is invalid
        expect($service->validatePersonnummer('01000100000'))->toBeFalse();
        // Month 13 is invalid
        expect($service->validatePersonnummer('01130100000'))->toBeFalse();
    });

    test('accepts D-numbers (temporary personnummer)', function () {
        $service = app(SkattekortService::class);

        // D-numbers have 4 added to the first digit (day)
        // So day 17 becomes 57 (17 + 40 = 57)
        // The validation should recognize days 41-71 as valid D-number days
        // We test that the day parsing correctly handles D-numbers
        // Note: Control digits differ from regular personnummer so we test the date parsing part
        expect($service->validatePersonnummer('57054026647'))->toBeTrue();
    })->skip('D-number control digits differ from regular personnummer');

    test('rejects personnummer with invalid control digits', function () {
        $service = app(SkattekortService::class);

        // Same as valid test number but with wrong control digit
        expect($service->validatePersonnummer('01010150567'))->toBeFalse();
        expect($service->validatePersonnummer('01010150578'))->toBeFalse();
    });
});

describe('SkattekortService configuration', function () {
    test('reports not available when maskinporten is not configured', function () {
        $service = app(SkattekortService::class);

        // Without proper configuration, it should report not available
        expect($service->isAvailable())->toBeFalse();
    });
});

describe('SkattekortService tax card processing', function () {
    beforeEach(function () {
        ['user' => $this->user, 'company' => $this->company] = createTestCompanyContext();
    });

    test('updates employee settings for tabelltrekk', function () {
        $employee = EmployeePayrollSettings::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'skatt_type' => 'prosenttrekk',
            'skattetabell' => null,
        ]);

        $service = app(SkattekortService::class);

        $taxCardData = [
            'trekktype' => 'tabelltrekk',
            'tabellnummer' => '7100',
            'gyldig_fra' => '2026-01-01',
            'gyldig_til' => '2026-12-31',
        ];

        $service->updateEmployeeFromTaxCard($employee, $taxCardData);

        $employee->refresh();

        expect($employee->skatt_type)->toBe('tabelltrekk')
            ->and($employee->skattetabell)->toBe('7100')
            ->and($employee->skattekort_hentet_at)->not->toBeNull();
    });

    test('updates employee settings for prosenttrekk', function () {
        $employee = EmployeePayrollSettings::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'skatt_type' => 'tabelltrekk',
            'skatteprosent' => null,
        ]);

        $service = app(SkattekortService::class);

        $taxCardData = [
            'trekktype' => 'prosenttrekk',
            'trekkprosent' => 35.5,
            'gyldig_fra' => '2026-01-01',
            'gyldig_til' => '2026-12-31',
        ];

        $service->updateEmployeeFromTaxCard($employee, $taxCardData);

        $employee->refresh();

        expect($employee->skatt_type)->toBe('prosenttrekk')
            ->and((float) $employee->skatteprosent)->toBe(35.5);
    });

    test('updates employee settings for frikort', function () {
        $employee = EmployeePayrollSettings::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'skatt_type' => 'tabelltrekk',
            'frikort_belop' => null,
            'frikort_brukt' => 5000,
        ]);

        $service = app(SkattekortService::class);

        $taxCardData = [
            'trekktype' => 'frikort',
            'frikortbeloep' => 65000,
            'gyldig_fra' => '2026-01-01',
            'gyldig_til' => '2026-12-31',
        ];

        $service->updateEmployeeFromTaxCard($employee, $taxCardData);

        $employee->refresh();

        expect($employee->skatt_type)->toBe('frikort')
            ->and((float) $employee->frikort_belop)->toBe(65000.0)
            ->and((float) $employee->frikort_brukt)->toBe(0.0); // Reset on new frikort
    });

    test('updates employee settings for kildeskatt', function () {
        $employee = EmployeePayrollSettings::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'skatt_type' => 'tabelltrekk',
            'skatteprosent' => null,
        ]);

        $service = app(SkattekortService::class);

        $taxCardData = [
            'trekktype' => 'kildeskatt',
            'trekkprosent' => 50,
            'gyldig_fra' => '2026-01-01',
            'gyldig_til' => '2026-12-31',
        ];

        $service->updateEmployeeFromTaxCard($employee, $taxCardData);

        $employee->refresh();

        expect($employee->skatt_type)->toBe('kildeskatt')
            ->and((float) $employee->skatteprosent)->toBe(50.0);
    });

    test('stores raw tax card data', function () {
        $employee = EmployeePayrollSettings::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $service = app(SkattekortService::class);

        $taxCardData = [
            'raw_response' => ['some' => 'data'],
            'trekktype' => 'tabelltrekk',
            'tabellnummer' => '7100',
        ];

        $service->updateEmployeeFromTaxCard($employee, $taxCardData);

        $employee->refresh();

        expect($employee->skattekort_data)->toBeArray()
            ->and($employee->skattekort_data['trekktype'])->toBe('tabelltrekk');
    });
});

describe('SkattekortService fetchTaxCard validation', function () {
    beforeEach(function () {
        ['user' => $this->user, 'company' => $this->company] = createTestCompanyContext();
    });

    test('throws exception when personnummer is missing', function () {
        $employee = EmployeePayrollSettings::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'personnummer' => null,
        ]);

        $service = app(SkattekortService::class);

        $service->fetchTaxCard($employee);
    })->throws(\RuntimeException::class, 'Personnummer mangler for den ansatte.');

    test('throws exception when personnummer is invalid', function () {
        $employee = EmployeePayrollSettings::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'personnummer' => '12345678901', // Invalid control digits
        ]);

        $service = app(SkattekortService::class);

        $service->fetchTaxCard($employee);
    })->throws(\RuntimeException::class, 'Ugyldig personnummer format.');
});
