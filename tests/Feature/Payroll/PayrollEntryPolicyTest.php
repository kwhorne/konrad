<?php

use App\Models\Company;
use App\Models\PayrollEntry;
use App\Models\PayrollRun;
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
it('allows admin to view any payroll entries', function () {
    expect($this->admin->can('viewAny', PayrollEntry::class))->toBeTrue();
});

it('allows payroll user to view any payroll entries', function () {
    expect($this->payrollUser->can('viewAny', PayrollEntry::class))->toBeTrue();
});

it('denies regular user to view any payroll entries', function () {
    expect($this->regularUser->can('viewAny', PayrollEntry::class))->toBeFalse();
});

// view tests
it('allows admin to view payroll entry', function () {
    $run = PayrollRun::factory()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->admin->can('view', $entry))->toBeTrue();
});

it('allows payroll user to view payroll entry', function () {
    $run = PayrollRun::factory()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->payrollUser->can('view', $entry))->toBeTrue();
});

it('denies regular user to view payroll entry', function () {
    $run = PayrollRun::factory()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->regularUser->can('view', $entry))->toBeFalse();
});

// create tests
it('allows admin to create payroll entries', function () {
    expect($this->admin->can('create', PayrollEntry::class))->toBeTrue();
});

it('allows payroll user to create payroll entries', function () {
    expect($this->payrollUser->can('create', PayrollEntry::class))->toBeTrue();
});

it('denies regular user to create payroll entries', function () {
    expect($this->regularUser->can('create', PayrollEntry::class))->toBeFalse();
});

// update tests
it('allows admin to update any payroll entry', function () {
    $run = PayrollRun::factory()->approved()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->admin->can('update', $entry))->toBeTrue();
});

it('allows payroll user to update entry on editable run', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->payrollUser->can('update', $entry))->toBeTrue();
});

it('denies payroll user to update entry on approved run', function () {
    $run = PayrollRun::factory()->approved()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->payrollUser->can('update', $entry))->toBeFalse();
});

it('denies payroll user to update entry on paid run', function () {
    $run = PayrollRun::factory()->paid()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->payrollUser->can('update', $entry))->toBeFalse();
});

it('denies regular user to update payroll entry', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->regularUser->can('update', $entry))->toBeFalse();
});

// delete tests
it('allows admin to delete any payroll entry', function () {
    $run = PayrollRun::factory()->paid()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->admin->can('delete', $entry))->toBeTrue();
});

it('allows payroll user to delete entry on editable run', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->payrollUser->can('delete', $entry))->toBeTrue();
});

it('denies payroll user to delete entry on approved run', function () {
    $run = PayrollRun::factory()->approved()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->payrollUser->can('delete', $entry))->toBeFalse();
});

it('denies regular user to delete payroll entry', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->regularUser->can('delete', $entry))->toBeFalse();
});

// downloadPayslip tests
it('allows admin to download payslip', function () {
    $run = PayrollRun::factory()->paid()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->admin->can('downloadPayslip', $entry))->toBeTrue();
});

it('allows payroll user to download payslip', function () {
    $run = PayrollRun::factory()->paid()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->payrollUser->can('downloadPayslip', $entry))->toBeTrue();
});

it('denies regular user to download payslip', function () {
    $run = PayrollRun::factory()->paid()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->regularUser->can('downloadPayslip', $entry))->toBeFalse();
});

// sendPayslip tests
it('allows admin to send payslip', function () {
    $run = PayrollRun::factory()->paid()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->admin->can('sendPayslip', $entry))->toBeTrue();
});

it('allows payroll user to send payslip', function () {
    $run = PayrollRun::factory()->paid()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->payrollUser->can('sendPayslip', $entry))->toBeTrue();
});

it('denies regular user to send payslip', function () {
    $run = PayrollRun::factory()->paid()->create(['company_id' => $this->company->id]);
    $entry = PayrollEntry::factory()->forRun($run)->create();
    expect($this->regularUser->can('sendPayslip', $entry))->toBeFalse();
});
