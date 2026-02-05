<?php

use App\Models\AnnualAccount;
use App\Models\Company;
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
it('allows admin to viewAny annual accounts', function () {
    expect($this->admin->can('viewAny', AnnualAccount::class))->toBeTrue();
});

it('allows admin to view an annual account', function () {
    $annualAccount = AnnualAccount::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('view', $annualAccount))->toBeTrue();
});

it('allows admin to create annual accounts', function () {
    expect($this->admin->can('create', AnnualAccount::class))->toBeTrue();
});

it('allows admin to update an annual account', function () {
    $annualAccount = AnnualAccount::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('update', $annualAccount))->toBeTrue();
});

it('allows admin to delete an annual account', function () {
    $annualAccount = AnnualAccount::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('delete', $annualAccount))->toBeTrue();
});

it('allows admin to approve an annual account', function () {
    $annualAccount = AnnualAccount::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('approve', $annualAccount))->toBeTrue();
});

it('allows admin to markAsDraft an annual account', function () {
    $annualAccount = AnnualAccount::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('markAsDraft', $annualAccount))->toBeTrue();
});

it('allows admin to submitToAltinn an annual account', function () {
    $annualAccount = AnnualAccount::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('submitToAltinn', $annualAccount))->toBeTrue();
});

// Economy user - basic permissions
it('allows economy user to viewAny annual accounts', function () {
    expect($this->economyUser->can('viewAny', AnnualAccount::class))->toBeTrue();
});

it('allows economy user to view an annual account', function () {
    $annualAccount = AnnualAccount::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can('view', $annualAccount))->toBeTrue();
});

it('allows economy user to create annual accounts', function () {
    expect($this->economyUser->can('create', AnnualAccount::class))->toBeTrue();
});

// Economy user - update (depends on canBeEdited)
it('allows economy user to update annual account when canBeEdited is true', function (string $status) {
    $annualAccount = AnnualAccount::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($annualAccount->canBeEdited())->toBeTrue();
    expect($this->economyUser->can('update', $annualAccount))->toBeTrue();
})->with(['draft', 'approved', 'rejected']);

it('denies economy user to update annual account when canBeEdited is false', function (string $status) {
    $annualAccount = AnnualAccount::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($annualAccount->canBeEdited())->toBeFalse();
    expect($this->economyUser->can('update', $annualAccount))->toBeFalse();
})->with(['submitted', 'accepted']);

// Economy user - delete (depends on !isSubmitted)
it('allows economy user to delete annual account when not submitted', function (string $status) {
    $annualAccount = AnnualAccount::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($annualAccount->isSubmitted())->toBeFalse();
    expect($this->economyUser->can('delete', $annualAccount))->toBeTrue();
})->with(['draft', 'approved', 'rejected']);

it('denies economy user to delete annual account when submitted', function (string $status) {
    $annualAccount = AnnualAccount::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($annualAccount->isSubmitted())->toBeTrue();
    expect($this->economyUser->can('delete', $annualAccount))->toBeFalse();
})->with(['submitted', 'accepted']);

// Economy user - approve (depends on isDraft)
it('allows economy user to approve annual account when draft', function () {
    $annualAccount = AnnualAccount::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($annualAccount->isDraft())->toBeTrue();
    expect($this->economyUser->can('approve', $annualAccount))->toBeTrue();
});

it('denies economy user to approve annual account when not draft', function (string $status) {
    $annualAccount = AnnualAccount::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($annualAccount->isDraft())->toBeFalse();
    expect($this->economyUser->can('approve', $annualAccount))->toBeFalse();
})->with(['approved', 'submitted', 'accepted', 'rejected']);

// Economy user - markAsDraft (depends on !isSubmitted)
it('allows economy user to markAsDraft annual account when not submitted', function (string $status) {
    $annualAccount = AnnualAccount::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($annualAccount->isSubmitted())->toBeFalse();
    expect($this->economyUser->can('markAsDraft', $annualAccount))->toBeTrue();
})->with(['draft', 'approved', 'rejected']);

it('denies economy user to markAsDraft annual account when submitted', function (string $status) {
    $annualAccount = AnnualAccount::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($annualAccount->isSubmitted())->toBeTrue();
    expect($this->economyUser->can('markAsDraft', $annualAccount))->toBeFalse();
})->with(['submitted', 'accepted']);

// Economy user - submitToAltinn (depends on canBeSubmitted)
it('allows economy user to submitToAltinn annual account when canBeSubmitted', function () {
    $annualAccount = AnnualAccount::factory()->approved()->create(['company_id' => $this->company->id]);
    expect($annualAccount->canBeSubmitted())->toBeTrue();
    expect($this->economyUser->can('submitToAltinn', $annualAccount))->toBeTrue();
});

it('denies economy user to submitToAltinn annual account when cannot be submitted', function (string $status) {
    $annualAccount = AnnualAccount::factory()->create([
        'company_id' => $this->company->id,
        'status' => $status,
    ]);
    expect($annualAccount->canBeSubmitted())->toBeFalse();
    expect($this->economyUser->can('submitToAltinn', $annualAccount))->toBeFalse();
})->with(['draft', 'submitted', 'accepted', 'rejected']);

// Regular user denied for all
it('denies regular user to viewAny annual accounts', function () {
    expect($this->regularUser->can('viewAny', AnnualAccount::class))->toBeFalse();
});

it('denies regular user to view an annual account', function () {
    $annualAccount = AnnualAccount::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('view', $annualAccount))->toBeFalse();
});

it('denies regular user to create annual accounts', function () {
    expect($this->regularUser->can('create', AnnualAccount::class))->toBeFalse();
});

it('denies regular user to update an annual account', function () {
    $annualAccount = AnnualAccount::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('update', $annualAccount))->toBeFalse();
});

it('denies regular user to delete an annual account', function () {
    $annualAccount = AnnualAccount::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('delete', $annualAccount))->toBeFalse();
});

it('denies regular user to approve an annual account', function () {
    $annualAccount = AnnualAccount::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('approve', $annualAccount))->toBeFalse();
});

it('denies regular user to markAsDraft an annual account', function () {
    $annualAccount = AnnualAccount::factory()->draft()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('markAsDraft', $annualAccount))->toBeFalse();
});

it('denies regular user to submitToAltinn an annual account', function () {
    $annualAccount = AnnualAccount::factory()->approved()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('submitToAltinn', $annualAccount))->toBeFalse();
});
