<?php

use App\Models\Company;
use App\Models\ShareholderReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $owner = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($owner)->create();
    app()->instance('current.company', $this->company);

    $this->admin = User::factory()->create([
        'is_admin' => true,
        'is_economy' => false,
        'current_company_id' => $this->company->id,
    ]);
    $this->economyUser = User::factory()->create([
        'is_admin' => false,
        'is_economy' => true,
        'current_company_id' => $this->company->id,
    ]);
    $this->regularUser = User::factory()->create([
        'is_admin' => false,
        'is_economy' => false,
        'current_company_id' => $this->company->id,
    ]);
});

function createShareholderReport(object $testCase, string $status = 'draft'): ShareholderReport
{
    return ShareholderReport::create([
        'company_id' => $testCase->company->id,
        'year' => 2024,
        'report_date' => '2024-12-31',
        'share_capital' => 100000,
        'total_shares' => 1000,
        'number_of_shareholders' => 2,
        'status' => $status,
        'created_by' => $testCase->admin->id,
    ]);
}

// Admin can do everything
it('allows admin to viewAny shareholder reports', function () {
    expect($this->admin->can('viewAny', ShareholderReport::class))->toBeTrue();
});

it('allows admin to view a shareholder report', function () {
    $report = createShareholderReport($this);
    expect($this->admin->can('view', $report))->toBeTrue();
});

it('allows admin to create shareholder reports', function () {
    expect($this->admin->can('create', ShareholderReport::class))->toBeTrue();
});

it('allows admin to update a shareholder report', function () {
    $report = createShareholderReport($this);
    expect($this->admin->can('update', $report))->toBeTrue();
});

it('allows admin to delete a shareholder report', function () {
    $report = createShareholderReport($this);
    expect($this->admin->can('delete', $report))->toBeTrue();
});

it('allows admin to markAsReady a shareholder report', function () {
    $report = createShareholderReport($this);
    expect($this->admin->can('markAsReady', $report))->toBeTrue();
});

it('allows admin to markAsDraft a shareholder report', function () {
    $report = createShareholderReport($this);
    expect($this->admin->can('markAsDraft', $report))->toBeTrue();
});

it('allows admin to submitToAltinn a shareholder report', function () {
    $report = createShareholderReport($this);
    expect($this->admin->can('submitToAltinn', $report))->toBeTrue();
});

// Economy user - basic permissions
it('allows economy user to viewAny shareholder reports', function () {
    expect($this->economyUser->can('viewAny', ShareholderReport::class))->toBeTrue();
});

it('allows economy user to view a shareholder report', function () {
    $report = createShareholderReport($this);
    expect($this->economyUser->can('view', $report))->toBeTrue();
});

it('allows economy user to create shareholder reports', function () {
    expect($this->economyUser->can('create', ShareholderReport::class))->toBeTrue();
});

// Economy user - update (depends on canBeEdited)
it('allows economy user to update shareholder report when canBeEdited is true', function (string $status) {
    $report = createShareholderReport($this, $status);
    expect($report->canBeEdited())->toBeTrue();
    expect($this->economyUser->can('update', $report))->toBeTrue();
})->with(['draft', 'ready']);

it('denies economy user to update shareholder report when canBeEdited is false', function () {
    $report = createShareholderReport($this, 'submitted');
    expect($report->canBeEdited())->toBeFalse();
    expect($this->economyUser->can('update', $report))->toBeFalse();
});

// Economy user - delete (depends on !isSubmitted)
it('allows economy user to delete shareholder report when not submitted', function (string $status) {
    $report = createShareholderReport($this, $status);
    expect($report->isSubmitted())->toBeFalse();
    expect($this->economyUser->can('delete', $report))->toBeTrue();
})->with(['draft', 'ready']);

it('denies economy user to delete shareholder report when submitted', function () {
    $report = createShareholderReport($this, 'submitted');
    expect($report->isSubmitted())->toBeTrue();
    expect($this->economyUser->can('delete', $report))->toBeFalse();
});

// Economy user - markAsReady (depends on isDraft)
it('allows economy user to markAsReady shareholder report when draft', function () {
    $report = createShareholderReport($this, 'draft');
    expect($report->isDraft())->toBeTrue();
    expect($this->economyUser->can('markAsReady', $report))->toBeTrue();
});

it('denies economy user to markAsReady shareholder report when not draft', function (string $status) {
    $report = createShareholderReport($this, $status);
    expect($report->isDraft())->toBeFalse();
    expect($this->economyUser->can('markAsReady', $report))->toBeFalse();
})->with(['ready', 'submitted']);

// Economy user - markAsDraft (depends on !isSubmitted)
it('allows economy user to markAsDraft shareholder report when not submitted', function (string $status) {
    $report = createShareholderReport($this, $status);
    expect($report->isSubmitted())->toBeFalse();
    expect($this->economyUser->can('markAsDraft', $report))->toBeTrue();
})->with(['draft', 'ready']);

it('denies economy user to markAsDraft shareholder report when submitted', function () {
    $report = createShareholderReport($this, 'submitted');
    expect($report->isSubmitted())->toBeTrue();
    expect($this->economyUser->can('markAsDraft', $report))->toBeFalse();
});

// Economy user - submitToAltinn (depends on canBeSubmitted)
it('allows economy user to submitToAltinn shareholder report when canBeSubmitted', function () {
    $report = createShareholderReport($this, 'ready');
    expect($report->canBeSubmitted())->toBeTrue();
    expect($this->economyUser->can('submitToAltinn', $report))->toBeTrue();
});

it('denies economy user to submitToAltinn shareholder report when cannot be submitted', function (string $status) {
    $report = createShareholderReport($this, $status);
    expect($report->canBeSubmitted())->toBeFalse();
    expect($this->economyUser->can('submitToAltinn', $report))->toBeFalse();
})->with(['draft', 'submitted']);

// Regular user denied for all
it('denies regular user to viewAny shareholder reports', function () {
    expect($this->regularUser->can('viewAny', ShareholderReport::class))->toBeFalse();
});

it('denies regular user to view a shareholder report', function () {
    $report = createShareholderReport($this);
    expect($this->regularUser->can('view', $report))->toBeFalse();
});

it('denies regular user to create shareholder reports', function () {
    expect($this->regularUser->can('create', ShareholderReport::class))->toBeFalse();
});

it('denies regular user to update a shareholder report', function () {
    $report = createShareholderReport($this);
    expect($this->regularUser->can('update', $report))->toBeFalse();
});

it('denies regular user to delete a shareholder report', function () {
    $report = createShareholderReport($this);
    expect($this->regularUser->can('delete', $report))->toBeFalse();
});

it('denies regular user to markAsReady a shareholder report', function () {
    $report = createShareholderReport($this);
    expect($this->regularUser->can('markAsReady', $report))->toBeFalse();
});

it('denies regular user to markAsDraft a shareholder report', function () {
    $report = createShareholderReport($this);
    expect($this->regularUser->can('markAsDraft', $report))->toBeFalse();
});

it('denies regular user to submitToAltinn a shareholder report', function () {
    $report = createShareholderReport($this, 'ready');
    expect($this->regularUser->can('submitToAltinn', $report))->toBeFalse();
});
