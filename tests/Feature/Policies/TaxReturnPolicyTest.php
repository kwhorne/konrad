<?php

use App\Models\Company;
use App\Models\TaxReturn;
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
it('allows admin to viewAny tax returns', function () {
    expect($this->admin->can('viewAny', TaxReturn::class))->toBeTrue();
});

it('allows admin to view a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('view', $taxReturn))->toBeTrue();
});

it('allows admin to create tax returns', function () {
    expect($this->admin->can('create', TaxReturn::class))->toBeTrue();
});

it('allows admin to update a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('update', $taxReturn))->toBeTrue();
});

it('allows admin to delete a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('delete', $taxReturn))->toBeTrue();
});

it('allows admin to calculate a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('calculate', $taxReturn))->toBeTrue();
});

it('allows admin to markAsReady a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('markAsReady', $taxReturn))->toBeTrue();
});

it('allows admin to markAsDraft a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('markAsDraft', $taxReturn))->toBeTrue();
});

it('allows admin to submitToAltinn a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('submitToAltinn', $taxReturn))->toBeTrue();
});

// Economy user - basic permissions
it('allows economy user to viewAny tax returns', function () {
    expect($this->economyUser->can('viewAny', TaxReturn::class))->toBeTrue();
});

it('allows economy user to view a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can('view', $taxReturn))->toBeTrue();
});

it('allows economy user to create tax returns', function () {
    expect($this->economyUser->can('create', TaxReturn::class))->toBeTrue();
});

// Economy user - update (depends on canBeEdited)
it('allows economy user to update tax return when canBeEdited is true', function (string $status) {
    $taxReturn = TaxReturn::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($taxReturn->canBeEdited())->toBeTrue();
    expect($this->economyUser->can('update', $taxReturn))->toBeTrue();
})->with(['draft', 'ready']);

it('denies economy user to update tax return when canBeEdited is false', function () {
    $taxReturn = TaxReturn::factory()->submitted()->create(['company_id' => $this->company->id]);
    expect($taxReturn->canBeEdited())->toBeFalse();
    expect($this->economyUser->can('update', $taxReturn))->toBeFalse();
});

// Economy user - calculate (depends on canBeEdited)
it('allows economy user to calculate tax return when canBeEdited is true', function (string $status) {
    $taxReturn = TaxReturn::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($taxReturn->canBeEdited())->toBeTrue();
    expect($this->economyUser->can('calculate', $taxReturn))->toBeTrue();
})->with(['draft', 'ready']);

it('denies economy user to calculate tax return when canBeEdited is false', function () {
    $taxReturn = TaxReturn::factory()->submitted()->create(['company_id' => $this->company->id]);
    expect($taxReturn->canBeEdited())->toBeFalse();
    expect($this->economyUser->can('calculate', $taxReturn))->toBeFalse();
});

// Economy user - delete (depends on !isSubmitted)
it('allows economy user to delete tax return when not submitted', function (string $status) {
    $taxReturn = TaxReturn::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($taxReturn->isSubmitted())->toBeFalse();
    expect($this->economyUser->can('delete', $taxReturn))->toBeTrue();
})->with(['draft', 'ready']);

it('denies economy user to delete tax return when submitted', function () {
    $taxReturn = TaxReturn::factory()->submitted()->create(['company_id' => $this->company->id]);
    expect($taxReturn->isSubmitted())->toBeTrue();
    expect($this->economyUser->can('delete', $taxReturn))->toBeFalse();
});

// Economy user - markAsReady (depends on isDraft)
it('allows economy user to markAsReady tax return when draft', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id, 'status' => 'draft']);
    expect($taxReturn->isDraft())->toBeTrue();
    expect($this->economyUser->can('markAsReady', $taxReturn))->toBeTrue();
});

it('denies economy user to markAsReady tax return when not draft', function (string $status) {
    $taxReturn = TaxReturn::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($taxReturn->isDraft())->toBeFalse();
    expect($this->economyUser->can('markAsReady', $taxReturn))->toBeFalse();
})->with(['ready', 'submitted']);

// Economy user - markAsDraft (depends on !isSubmitted)
it('allows economy user to markAsDraft tax return when not submitted', function (string $status) {
    $taxReturn = TaxReturn::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($taxReturn->isSubmitted())->toBeFalse();
    expect($this->economyUser->can('markAsDraft', $taxReturn))->toBeTrue();
})->with(['draft', 'ready']);

it('denies economy user to markAsDraft tax return when submitted', function () {
    $taxReturn = TaxReturn::factory()->submitted()->create(['company_id' => $this->company->id]);
    expect($taxReturn->isSubmitted())->toBeTrue();
    expect($this->economyUser->can('markAsDraft', $taxReturn))->toBeFalse();
});

// Economy user - submitToAltinn (depends on canBeSubmitted)
it('allows economy user to submitToAltinn tax return when canBeSubmitted', function () {
    $taxReturn = TaxReturn::factory()->ready()->create(['company_id' => $this->company->id]);
    expect($taxReturn->canBeSubmitted())->toBeTrue();
    expect($this->economyUser->can('submitToAltinn', $taxReturn))->toBeTrue();
});

it('denies economy user to submitToAltinn tax return when cannot be submitted', function (string $status) {
    $taxReturn = TaxReturn::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($taxReturn->canBeSubmitted())->toBeFalse();
    expect($this->economyUser->can('submitToAltinn', $taxReturn))->toBeFalse();
})->with(['draft', 'submitted']);

// Regular user denied for all
it('denies regular user to viewAny tax returns', function () {
    expect($this->regularUser->can('viewAny', TaxReturn::class))->toBeFalse();
});

it('denies regular user to view a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('view', $taxReturn))->toBeFalse();
});

it('denies regular user to create tax returns', function () {
    expect($this->regularUser->can('create', TaxReturn::class))->toBeFalse();
});

it('denies regular user to update a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('update', $taxReturn))->toBeFalse();
});

it('denies regular user to delete a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('delete', $taxReturn))->toBeFalse();
});

it('denies regular user to calculate a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('calculate', $taxReturn))->toBeFalse();
});

it('denies regular user to markAsReady a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('markAsReady', $taxReturn))->toBeFalse();
});

it('denies regular user to markAsDraft a tax return', function () {
    $taxReturn = TaxReturn::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('markAsDraft', $taxReturn))->toBeFalse();
});

it('denies regular user to submitToAltinn a tax return', function () {
    $taxReturn = TaxReturn::factory()->ready()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('submitToAltinn', $taxReturn))->toBeFalse();
});
