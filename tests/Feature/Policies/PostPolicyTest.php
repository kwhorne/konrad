<?php

use App\Models\Company;
use App\Models\Post;
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
it('allows admin to viewAny posts', function () {
    expect($this->admin->can('viewAny', Post::class))->toBeTrue();
});

it('allows admin to view a post', function () {
    $post = Post::factory()->create(['author_id' => $this->admin->id]);
    expect($this->admin->can('view', $post))->toBeTrue();
});

it('allows admin to create posts', function () {
    expect($this->admin->can('create', Post::class))->toBeTrue();
});

it('allows admin to update a post', function () {
    $post = Post::factory()->create(['author_id' => $this->admin->id]);
    expect($this->admin->can('update', $post))->toBeTrue();
});

it('allows admin to delete a post', function () {
    $post = Post::factory()->create(['author_id' => $this->admin->id]);
    expect($this->admin->can('delete', $post))->toBeTrue();
});

// Regular user denied for all (all methods return false)
it('denies regular user to viewAny posts', function () {
    expect($this->regularUser->can('viewAny', Post::class))->toBeFalse();
});

it('denies regular user to view a post', function () {
    $post = Post::factory()->create(['author_id' => $this->admin->id]);
    expect($this->regularUser->can('view', $post))->toBeFalse();
});

it('denies regular user to create posts', function () {
    expect($this->regularUser->can('create', Post::class))->toBeFalse();
});

it('denies regular user to update a post', function () {
    $post = Post::factory()->create(['author_id' => $this->admin->id]);
    expect($this->regularUser->can('update', $post))->toBeFalse();
});

it('denies regular user to delete a post', function () {
    $post = Post::factory()->create(['author_id' => $this->admin->id]);
    expect($this->regularUser->can('delete', $post))->toBeFalse();
});
