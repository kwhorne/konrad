<?php

use App\Models\Company;
use App\Models\PurchaseOrder;
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
it('allows admin to perform all purchase order actions', function (string $ability) {
    $purchaseOrder = PurchaseOrder::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can($ability, $purchaseOrder))->toBeTrue();
})->with(['view', 'update', 'delete', 'approve', 'submitForApproval', 'markAsSent', 'cancel']);

it('allows admin to viewAny purchase orders', function () {
    expect($this->admin->can('viewAny', PurchaseOrder::class))->toBeTrue();
});

it('allows admin to create purchase orders', function () {
    expect($this->admin->can('create', PurchaseOrder::class))->toBeTrue();
});

// Economy user can do everything
it('allows economy user to perform all purchase order actions', function (string $ability) {
    $purchaseOrder = PurchaseOrder::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can($ability, $purchaseOrder))->toBeTrue();
})->with(['view', 'update', 'delete', 'approve', 'submitForApproval', 'markAsSent', 'cancel']);

it('allows economy user to viewAny purchase orders', function () {
    expect($this->economyUser->can('viewAny', PurchaseOrder::class))->toBeTrue();
});

it('allows economy user to create purchase orders', function () {
    expect($this->economyUser->can('create', PurchaseOrder::class))->toBeTrue();
});

// Regular user denied for all
it('denies regular user to perform all purchase order actions', function (string $ability) {
    $purchaseOrder = PurchaseOrder::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can($ability, $purchaseOrder))->toBeFalse();
})->with(['view', 'update', 'delete', 'approve', 'submitForApproval', 'markAsSent', 'cancel']);

it('denies regular user to viewAny purchase orders', function () {
    expect($this->regularUser->can('viewAny', PurchaseOrder::class))->toBeFalse();
});

it('denies regular user to create purchase orders', function () {
    expect($this->regularUser->can('create', PurchaseOrder::class))->toBeFalse();
});
