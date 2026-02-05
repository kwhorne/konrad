<?php

use App\Models\BankStatement;
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
it('allows admin to perform all bank statement actions', function (string $ability) {
    $statement = BankStatement::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can($ability, $statement))->toBeTrue();
})->with(['view', 'update', 'delete']);

it('allows admin to viewAny bank statements', function () {
    expect($this->admin->can('viewAny', BankStatement::class))->toBeTrue();
});

it('allows admin to create bank statements', function () {
    expect($this->admin->can('create', BankStatement::class))->toBeTrue();
});

// Economy user can do everything
it('allows economy user to perform all bank statement actions', function (string $ability) {
    $statement = BankStatement::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can($ability, $statement))->toBeTrue();
})->with(['view', 'update', 'delete']);

it('allows economy user to viewAny bank statements', function () {
    expect($this->economyUser->can('viewAny', BankStatement::class))->toBeTrue();
});

it('allows economy user to create bank statements', function () {
    expect($this->economyUser->can('create', BankStatement::class))->toBeTrue();
});

// Regular user denied for all
it('denies regular user to perform all bank statement actions', function (string $ability) {
    $statement = BankStatement::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can($ability, $statement))->toBeFalse();
})->with(['view', 'update', 'delete']);

it('denies regular user to viewAny bank statements', function () {
    expect($this->regularUser->can('viewAny', BankStatement::class))->toBeFalse();
});

it('denies regular user to create bank statements', function () {
    expect($this->regularUser->can('create', BankStatement::class))->toBeFalse();
});
