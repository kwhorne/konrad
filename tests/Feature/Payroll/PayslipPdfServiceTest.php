<?php

use App\Models\EmployeePayrollSettings;
use App\Models\PayrollEntry;
use App\Models\PayrollRun;
use App\Services\Payroll\PayslipPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = createTestCompanyContext();
});

test('generates password from last 5 digits of personnummer', function () {
    EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'personnummer' => '17054026641',
    ]);

    $service = app(PayslipPdfService::class);
    $password = $service->getPasswordForEmployee($this->user->id);

    expect($password)->toBe('26641');
});

test('returns null password when personnummer is missing', function () {
    EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'personnummer' => null,
    ]);

    $service = app(PayslipPdfService::class);
    $password = $service->getPasswordForEmployee($this->user->id);

    expect($password)->toBeNull();
});

test('returns personal email when set', function () {
    EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'personal_email' => 'personal@example.com',
    ]);

    $service = app(PayslipPdfService::class);
    $email = $service->getEmailForEmployee($this->user->id);

    expect($email)->toBe('personal@example.com');
});

test('falls back to user email when personal email not set', function () {
    EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'personal_email' => null,
    ]);

    $service = app(PayslipPdfService::class);
    $email = $service->getEmailForEmployee($this->user->id);

    expect($email)->toBe($this->user->email);
});

test('generates PDF payslip', function () {
    EmployeePayrollSettings::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'personnummer' => '17054026641',
    ]);

    $run = PayrollRun::factory()->create([
        'company_id' => $this->company->id,
        'status' => PayrollRun::STATUS_PAID,
    ]);

    $entry = PayrollEntry::factory()->create([
        'company_id' => $this->company->id,
        'payroll_run_id' => $run->id,
        'user_id' => $this->user->id,
        'bruttolonn' => 50000,
        'nettolonn' => 35000,
    ]);

    $service = app(PayslipPdfService::class);
    $pdfContent = $service->generatePayslip($entry, '26641');

    // PDF files start with %PDF
    expect($pdfContent)->toStartWith('%PDF');
});

test('generates correct filename', function () {
    $run = PayrollRun::factory()->create([
        'company_id' => $this->company->id,
        'year' => 2026,
        'month' => 2,
    ]);

    $entry = PayrollEntry::factory()->create([
        'company_id' => $this->company->id,
        'payroll_run_id' => $run->id,
        'user_id' => $this->user->id,
    ]);

    $service = app(PayslipPdfService::class);
    $filename = $service->getFilename($entry);

    expect($filename)->toContain('Lønnsslipp-2026-02')
        ->and($filename)->toContain($this->user->name)
        ->and($filename)->toEndWith('.pdf');
});
