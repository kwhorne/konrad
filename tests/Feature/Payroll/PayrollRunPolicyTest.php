<?php

use App\Models\Company;
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
it('allows admin to view any payroll runs', function () {
    expect($this->admin->can('viewAny', PayrollRun::class))->toBeTrue();
});

it('allows payroll user to view any payroll runs', function () {
    expect($this->payrollUser->can('viewAny', PayrollRun::class))->toBeTrue();
});

it('denies regular user to view any payroll runs', function () {
    expect($this->regularUser->can('viewAny', PayrollRun::class))->toBeFalse();
});

// view tests
it('allows admin to view payroll run', function () {
    $run = PayrollRun::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('view', $run))->toBeTrue();
});

it('allows payroll user to view payroll run', function () {
    $run = PayrollRun::factory()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('view', $run))->toBeTrue();
});

it('denies regular user to view payroll run', function () {
    $run = PayrollRun::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('view', $run))->toBeFalse();
});

// create tests
it('allows admin to create payroll runs', function () {
    expect($this->admin->can('create', PayrollRun::class))->toBeTrue();
});

it('allows payroll user to create payroll runs', function () {
    expect($this->payrollUser->can('create', PayrollRun::class))->toBeTrue();
});

it('denies regular user to create payroll runs', function () {
    expect($this->regularUser->can('create', PayrollRun::class))->toBeFalse();
});

// update tests
it('allows admin to update any payroll run', function () {
    $run = PayrollRun::factory()->approved()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('update', $run))->toBeTrue();
});

it('allows payroll user to update editable payroll run', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('update', $run))->toBeTrue();
});

it('denies payroll user to update approved payroll run', function () {
    $run = PayrollRun::factory()->approved()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('update', $run))->toBeFalse();
});

it('denies payroll user to update paid payroll run', function () {
    $run = PayrollRun::factory()->paid()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('update', $run))->toBeFalse();
});

it('denies regular user to update payroll run', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('update', $run))->toBeFalse();
});

// delete tests
it('allows admin to delete any payroll run', function () {
    $run = PayrollRun::factory()->paid()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('delete', $run))->toBeTrue();
});

it('allows payroll user to delete editable payroll run', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('delete', $run))->toBeTrue();
});

it('denies payroll user to delete approved payroll run', function () {
    $run = PayrollRun::factory()->approved()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('delete', $run))->toBeFalse();
});

it('denies regular user to delete payroll run', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('delete', $run))->toBeFalse();
});

// calculate tests
it('allows admin to calculate any payroll run', function () {
    $run = PayrollRun::factory()->approved()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('calculate', $run))->toBeTrue();
});

it('allows payroll user to calculate draft payroll run', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('calculate', $run))->toBeTrue();
});

it('denies payroll user to calculate non-draft payroll run', function () {
    $run = PayrollRun::factory()->calculated()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('calculate', $run))->toBeFalse();
});

it('denies regular user to calculate payroll run', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('calculate', $run))->toBeFalse();
});

// approve tests
it('allows admin to approve any payroll run', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('approve', $run))->toBeTrue();
});

it('allows payroll user to approve calculated payroll run', function () {
    $run = PayrollRun::factory()->calculated()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('approve', $run))->toBeTrue();
});

it('denies payroll user to approve draft payroll run', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('approve', $run))->toBeFalse();
});

it('denies payroll user to approve paid payroll run', function () {
    $run = PayrollRun::factory()->paid()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('approve', $run))->toBeFalse();
});

it('denies regular user to approve payroll run', function () {
    $run = PayrollRun::factory()->calculated()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('approve', $run))->toBeFalse();
});

// markAsPaid tests
it('allows admin to mark any payroll run as paid', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('markAsPaid', $run))->toBeTrue();
});

it('allows payroll user to mark approved payroll run as paid', function () {
    $run = PayrollRun::factory()->approved()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('markAsPaid', $run))->toBeTrue();
});

it('denies payroll user to mark draft payroll run as paid', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('markAsPaid', $run))->toBeFalse();
});

it('denies payroll user to mark calculated payroll run as paid', function () {
    $run = PayrollRun::factory()->calculated()->create(['company_id' => $this->company->id]);
    expect($this->payrollUser->can('markAsPaid', $run))->toBeFalse();
});

it('denies regular user to mark payroll run as paid', function () {
    $run = PayrollRun::factory()->approved()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('markAsPaid', $run))->toBeFalse();
});
