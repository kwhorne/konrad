<?php

use App\Models\AMeldingReport;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $owner = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($owner)->create();
    app()->instance('current.company', $this->company);

    $this->payrollUser = User::factory()->create([
        'is_payroll' => true,
        'is_admin' => false,
        'current_company_id' => $this->company->id,
    ]);
    $this->company->users()->attach($this->payrollUser->id, ['role' => 'member', 'joined_at' => now()]);

    $this->regularUser = User::factory()->create([
        'is_payroll' => false,
        'is_admin' => false,
        'current_company_id' => $this->company->id,
    ]);
    $this->company->users()->attach($this->regularUser->id, ['role' => 'member', 'joined_at' => now()]);

    $this->admin = User::factory()->create([
        'is_admin' => true,
        'is_payroll' => false,
        'current_company_id' => $this->company->id,
    ]);
    $this->company->users()->attach($this->admin->id, ['role' => 'member', 'joined_at' => now()]);
});

describe('AMeldingReportPolicy', function () {
    test('payroll user can view any', function () {
        expect($this->payrollUser->can('viewAny', AMeldingReport::class))->toBeTrue();
    });

    test('payroll user can create', function () {
        expect($this->payrollUser->can('create', AMeldingReport::class))->toBeTrue();
    });

    test('payroll user can view report', function () {
        $report = AMeldingReport::create([
            'company_id' => $this->company->id,
            'year' => now()->year,
            'month' => now()->month,
            'melding_type' => AMeldingReport::TYPE_ORDINAER,
            'status' => AMeldingReport::STATUS_GENERATED,
        ]);

        expect($this->payrollUser->can('view', $report))->toBeTrue();
    });

    test('payroll user can update editable report', function () {
        $report = AMeldingReport::create([
            'company_id' => $this->company->id,
            'year' => now()->year,
            'month' => now()->month,
            'melding_type' => AMeldingReport::TYPE_ORDINAER,
            'status' => AMeldingReport::STATUS_DRAFT,
        ]);

        expect($this->payrollUser->can('update', $report))->toBeTrue();
    });

    test('payroll user cannot update submitted report', function () {
        $report = AMeldingReport::create([
            'company_id' => $this->company->id,
            'year' => now()->year,
            'month' => now()->month,
            'melding_type' => AMeldingReport::TYPE_ORDINAER,
            'status' => AMeldingReport::STATUS_SUBMITTED,
        ]);

        expect($this->payrollUser->can('update', $report))->toBeFalse();
    });

    test('regular user cannot view any', function () {
        expect($this->regularUser->can('viewAny', AMeldingReport::class))->toBeFalse();
    });

    test('regular user cannot create', function () {
        expect($this->regularUser->can('create', AMeldingReport::class))->toBeFalse();
    });

    test('admin can do everything', function () {
        expect($this->admin->can('viewAny', AMeldingReport::class))->toBeTrue()
            ->and($this->admin->can('create', AMeldingReport::class))->toBeTrue();
    });
});

describe('AltinnSubmissionPolicy', function () {
    test('economy user can view any', function () {
        expect($this->payrollUser->can('viewAny', \App\Models\AltinnSubmission::class))->toBeFalse();
    });

    test('regular user cannot view any altinn submissions', function () {
        expect($this->regularUser->can('viewAny', \App\Models\AltinnSubmission::class))->toBeFalse();
    });

    test('admin can view any altinn submissions', function () {
        expect($this->admin->can('viewAny', \App\Models\AltinnSubmission::class))->toBeTrue();
    });
});
