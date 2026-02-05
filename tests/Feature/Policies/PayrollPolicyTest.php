<?php

use App\Models\Company;
use App\Models\EmployeePayrollSettings;
use App\Models\PayType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $owner = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($owner)->create();
    app()->instance('current.company', $this->company);

    $this->admin = User::factory()->create([
        'is_admin' => true,
        'is_payroll' => false,
        'current_company_id' => $this->company->id,
    ]);
    $this->payrollUser = User::factory()->create([
        'is_admin' => false,
        'is_payroll' => true,
        'current_company_id' => $this->company->id,
    ]);
    $this->regularUser = User::factory()->create([
        'is_admin' => false,
        'is_payroll' => false,
        'current_company_id' => $this->company->id,
    ]);
});

// viewAny tests
it('allows admin to viewAny for payroll models', function (string $modelClass) {
    expect($this->admin->can('viewAny', $modelClass))->toBeTrue();
})->with([
    'PayType' => PayType::class,
    'EmployeePayrollSettings' => EmployeePayrollSettings::class,
]);

it('allows payroll user to viewAny for payroll models', function (string $modelClass) {
    expect($this->payrollUser->can('viewAny', $modelClass))->toBeTrue();
})->with([
    'PayType' => PayType::class,
    'EmployeePayrollSettings' => EmployeePayrollSettings::class,
]);

it('denies regular user to viewAny for payroll models', function (string $modelClass) {
    expect($this->regularUser->can('viewAny', $modelClass))->toBeFalse();
})->with([
    'PayType' => PayType::class,
    'EmployeePayrollSettings' => EmployeePayrollSettings::class,
]);

// create tests
it('allows admin to create payroll models', function (string $modelClass) {
    expect($this->admin->can('create', $modelClass))->toBeTrue();
})->with([
    'PayType' => PayType::class,
    'EmployeePayrollSettings' => EmployeePayrollSettings::class,
]);

it('allows payroll user to create payroll models', function (string $modelClass) {
    expect($this->payrollUser->can('create', $modelClass))->toBeTrue();
})->with([
    'PayType' => PayType::class,
    'EmployeePayrollSettings' => EmployeePayrollSettings::class,
]);

it('denies regular user to create payroll models', function (string $modelClass) {
    expect($this->regularUser->can('create', $modelClass))->toBeFalse();
})->with([
    'PayType' => PayType::class,
    'EmployeePayrollSettings' => EmployeePayrollSettings::class,
]);

// view instance tests (EmployeePayrollSettings has factory)
it('allows admin to view an employee payroll settings instance', function () {
    $settings = EmployeePayrollSettings::factory()->create();
    expect($this->admin->can('view', $settings))->toBeTrue();
});

it('allows payroll user to view an employee payroll settings instance', function () {
    $settings = EmployeePayrollSettings::factory()->create();
    expect($this->payrollUser->can('view', $settings))->toBeTrue();
});

it('denies regular user to view an employee payroll settings instance', function () {
    $settings = EmployeePayrollSettings::factory()->create();
    expect($this->regularUser->can('view', $settings))->toBeFalse();
});

// update instance tests (EmployeePayrollSettings has factory)
it('allows admin to update an employee payroll settings instance', function () {
    $settings = EmployeePayrollSettings::factory()->create();
    expect($this->admin->can('update', $settings))->toBeTrue();
});

it('allows payroll user to update an employee payroll settings instance', function () {
    $settings = EmployeePayrollSettings::factory()->create();
    expect($this->payrollUser->can('update', $settings))->toBeTrue();
});

it('denies regular user to update an employee payroll settings instance', function () {
    $settings = EmployeePayrollSettings::factory()->create();
    expect($this->regularUser->can('update', $settings))->toBeFalse();
});

// delete instance tests (EmployeePayrollSettings has factory)
it('allows admin to delete an employee payroll settings instance', function () {
    $settings = EmployeePayrollSettings::factory()->create();
    expect($this->admin->can('delete', $settings))->toBeTrue();
});

it('allows payroll user to delete an employee payroll settings instance', function () {
    $settings = EmployeePayrollSettings::factory()->create();
    expect($this->payrollUser->can('delete', $settings))->toBeTrue();
});

it('denies regular user to delete an employee payroll settings instance', function () {
    $settings = EmployeePayrollSettings::factory()->create();
    expect($this->regularUser->can('delete', $settings))->toBeFalse();
});

// PayType instance tests (no factory - create manually)
it('allows admin to view a pay type instance', function () {
    $payType = PayType::create([
        'company_id' => $this->company->id,
        'code' => 'TEST01',
        'name' => 'Test Pay Type',
        'category' => PayType::CATEGORY_FASTLONN,
        'is_taxable' => true,
        'is_aga_basis' => true,
        'is_vacation_basis' => true,
        'is_otp_basis' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
    expect($this->admin->can('view', $payType))->toBeTrue();
});

it('allows payroll user to view a pay type instance', function () {
    $payType = PayType::create([
        'company_id' => $this->company->id,
        'code' => 'TEST01',
        'name' => 'Test Pay Type',
        'category' => PayType::CATEGORY_FASTLONN,
        'is_taxable' => true,
        'is_aga_basis' => true,
        'is_vacation_basis' => true,
        'is_otp_basis' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
    expect($this->payrollUser->can('view', $payType))->toBeTrue();
});

it('denies regular user to view a pay type instance', function () {
    $payType = PayType::create([
        'company_id' => $this->company->id,
        'code' => 'TEST01',
        'name' => 'Test Pay Type',
        'category' => PayType::CATEGORY_FASTLONN,
        'is_taxable' => true,
        'is_aga_basis' => true,
        'is_vacation_basis' => true,
        'is_otp_basis' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
    expect($this->regularUser->can('view', $payType))->toBeFalse();
});

it('allows admin to update a pay type instance', function () {
    $payType = PayType::create([
        'company_id' => $this->company->id,
        'code' => 'TEST01',
        'name' => 'Test Pay Type',
        'category' => PayType::CATEGORY_FASTLONN,
        'is_taxable' => true,
        'is_aga_basis' => true,
        'is_vacation_basis' => true,
        'is_otp_basis' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
    expect($this->admin->can('update', $payType))->toBeTrue();
});

it('allows payroll user to update a pay type instance', function () {
    $payType = PayType::create([
        'company_id' => $this->company->id,
        'code' => 'TEST01',
        'name' => 'Test Pay Type',
        'category' => PayType::CATEGORY_FASTLONN,
        'is_taxable' => true,
        'is_aga_basis' => true,
        'is_vacation_basis' => true,
        'is_otp_basis' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
    expect($this->payrollUser->can('update', $payType))->toBeTrue();
});

it('denies regular user to update a pay type instance', function () {
    $payType = PayType::create([
        'company_id' => $this->company->id,
        'code' => 'TEST01',
        'name' => 'Test Pay Type',
        'category' => PayType::CATEGORY_FASTLONN,
        'is_taxable' => true,
        'is_aga_basis' => true,
        'is_vacation_basis' => true,
        'is_otp_basis' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
    expect($this->regularUser->can('update', $payType))->toBeFalse();
});

it('allows admin to delete a pay type instance', function () {
    $payType = PayType::create([
        'company_id' => $this->company->id,
        'code' => 'TEST01',
        'name' => 'Test Pay Type',
        'category' => PayType::CATEGORY_FASTLONN,
        'is_taxable' => true,
        'is_aga_basis' => true,
        'is_vacation_basis' => true,
        'is_otp_basis' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
    expect($this->admin->can('delete', $payType))->toBeTrue();
});

it('allows payroll user to delete a pay type instance', function () {
    $payType = PayType::create([
        'company_id' => $this->company->id,
        'code' => 'TEST01',
        'name' => 'Test Pay Type',
        'category' => PayType::CATEGORY_FASTLONN,
        'is_taxable' => true,
        'is_aga_basis' => true,
        'is_vacation_basis' => true,
        'is_otp_basis' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
    expect($this->payrollUser->can('delete', $payType))->toBeTrue();
});

it('denies regular user to delete a pay type instance', function () {
    $payType = PayType::create([
        'company_id' => $this->company->id,
        'code' => 'TEST01',
        'name' => 'Test Pay Type',
        'category' => PayType::CATEGORY_FASTLONN,
        'is_taxable' => true,
        'is_aga_basis' => true,
        'is_vacation_basis' => true,
        'is_otp_basis' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
    expect($this->regularUser->can('delete', $payType))->toBeFalse();
});
