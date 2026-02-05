<?php

use App\Models\Activity;
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
        'current_company_id' => $this->company->id,
    ]);
    $this->regularUser = User::factory()->create([
        'is_admin' => false,
        'current_company_id' => $this->company->id,
    ]);
});

// Admin can do everything (before() returns true)
it('allows admin to viewAny activities', function () {
    expect($this->admin->can('viewAny', Activity::class))->toBeTrue();
});

it('allows admin to view an activity', function () {
    $activity = Activity::factory()->create(['created_by' => $this->admin->id]);
    expect($this->admin->can('view', $activity))->toBeTrue();
});

it('allows admin to create activities', function () {
    expect($this->admin->can('create', Activity::class))->toBeTrue();
});

it('allows admin to update an activity', function () {
    $activity = Activity::factory()->create(['created_by' => $this->admin->id]);
    expect($this->admin->can('update', $activity))->toBeTrue();
});

it('allows admin to delete an activity', function () {
    $activity = Activity::factory()->create(['created_by' => $this->admin->id]);
    expect($this->admin->can('delete', $activity))->toBeTrue();
});

// Regular user can also do everything (all methods return true)
it('allows regular user to viewAny activities', function () {
    expect($this->regularUser->can('viewAny', Activity::class))->toBeTrue();
});

it('allows regular user to view an activity', function () {
    $activity = Activity::factory()->create(['created_by' => $this->admin->id]);
    expect($this->regularUser->can('view', $activity))->toBeTrue();
});

it('allows regular user to create activities', function () {
    expect($this->regularUser->can('create', Activity::class))->toBeTrue();
});

it('allows regular user to update an activity', function () {
    $activity = Activity::factory()->create(['created_by' => $this->admin->id]);
    expect($this->regularUser->can('update', $activity))->toBeTrue();
});

it('allows regular user to delete an activity', function () {
    $activity = Activity::factory()->create(['created_by' => $this->admin->id]);
    expect($this->regularUser->can('delete', $activity))->toBeTrue();
});
