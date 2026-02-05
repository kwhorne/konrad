<?php

use App\Models\Company;
use App\Models\Dividend;
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

// Admin can do everything
it('allows admin to viewAny dividends', function () {
    expect($this->admin->can('viewAny', Dividend::class))->toBeTrue();
});

it('allows admin to view a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('view', $dividend))->toBeTrue();
});

it('allows admin to create dividends', function () {
    expect($this->admin->can('create', Dividend::class))->toBeTrue();
});

it('allows admin to update a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('update', $dividend))->toBeTrue();
});

it('allows admin to delete a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('delete', $dividend))->toBeTrue();
});

it('allows admin to approve a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('approve', $dividend))->toBeTrue();
});

it('allows admin to markAsPaid a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('markAsPaid', $dividend))->toBeTrue();
});

it('allows admin to cancel a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('cancel', $dividend))->toBeTrue();
});

// Economy user - basic permissions
it('allows economy user to viewAny dividends', function () {
    expect($this->economyUser->can('viewAny', Dividend::class))->toBeTrue();
});

it('allows economy user to view a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can('view', $dividend))->toBeTrue();
});

it('allows economy user to create dividends', function () {
    expect($this->economyUser->can('create', Dividend::class))->toBeTrue();
});

it('allows economy user to approve a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can('approve', $dividend))->toBeTrue();
});

it('allows economy user to markAsPaid a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can('markAsPaid', $dividend))->toBeTrue();
});

// Economy user - update/delete (depends on !isPaid)
it('allows economy user to update dividend when not paid', function (string $status) {
    $dividend = Dividend::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($dividend->isPaid())->toBeFalse();
    expect($this->economyUser->can('update', $dividend))->toBeTrue();
})->with(['declared', 'approved', 'cancelled']);

it('denies economy user to update dividend when paid', function () {
    $dividend = Dividend::factory()->paid()->create(['company_id' => $this->company->id]);
    expect($dividend->isPaid())->toBeTrue();
    expect($this->economyUser->can('update', $dividend))->toBeFalse();
});

it('allows economy user to delete dividend when not paid', function (string $status) {
    $dividend = Dividend::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($dividend->isPaid())->toBeFalse();
    expect($this->economyUser->can('delete', $dividend))->toBeTrue();
})->with(['declared', 'approved', 'cancelled']);

it('denies economy user to delete dividend when paid', function () {
    $dividend = Dividend::factory()->paid()->create(['company_id' => $this->company->id]);
    expect($dividend->isPaid())->toBeTrue();
    expect($this->economyUser->can('delete', $dividend))->toBeFalse();
});

// Economy user - cancel (depends on canBeCancelled)
it('allows economy user to cancel dividend when canBeCancelled', function (string $status) {
    $dividend = Dividend::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($dividend->canBeCancelled())->toBeTrue();
    expect($this->economyUser->can('cancel', $dividend))->toBeTrue();
})->with(['declared', 'approved']);

it('denies economy user to cancel dividend when cannot be cancelled', function (string $status) {
    $dividend = Dividend::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($dividend->canBeCancelled())->toBeFalse();
    expect($this->economyUser->can('cancel', $dividend))->toBeFalse();
})->with(['paid', 'cancelled']);

// Regular user denied for all
it('denies regular user to viewAny dividends', function () {
    expect($this->regularUser->can('viewAny', Dividend::class))->toBeFalse();
});

it('denies regular user to view a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('view', $dividend))->toBeFalse();
});

it('denies regular user to create dividends', function () {
    expect($this->regularUser->can('create', Dividend::class))->toBeFalse();
});

it('denies regular user to update a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('update', $dividend))->toBeFalse();
});

it('denies regular user to delete a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('delete', $dividend))->toBeFalse();
});

it('denies regular user to approve a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('approve', $dividend))->toBeFalse();
});

it('denies regular user to markAsPaid a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('markAsPaid', $dividend))->toBeFalse();
});

it('denies regular user to cancel a dividend', function () {
    $dividend = Dividend::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('cancel', $dividend))->toBeFalse();
});
