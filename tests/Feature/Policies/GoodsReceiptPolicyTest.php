<?php

use App\Models\Company;
use App\Models\GoodsReceipt;
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
it('allows admin to perform all goods receipt actions', function (string $ability) {
    $goodsReceipt = GoodsReceipt::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can($ability, $goodsReceipt))->toBeTrue();
})->with(['view', 'update', 'delete', 'post', 'reverse']);

it('allows admin to viewAny goods receipts', function () {
    expect($this->admin->can('viewAny', GoodsReceipt::class))->toBeTrue();
});

it('allows admin to create goods receipts', function () {
    expect($this->admin->can('create', GoodsReceipt::class))->toBeTrue();
});

// Economy user can do everything
it('allows economy user to perform all goods receipt actions', function (string $ability) {
    $goodsReceipt = GoodsReceipt::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can($ability, $goodsReceipt))->toBeTrue();
})->with(['view', 'update', 'delete', 'post', 'reverse']);

it('allows economy user to viewAny goods receipts', function () {
    expect($this->economyUser->can('viewAny', GoodsReceipt::class))->toBeTrue();
});

it('allows economy user to create goods receipts', function () {
    expect($this->economyUser->can('create', GoodsReceipt::class))->toBeTrue();
});

// Regular user denied for all
it('denies regular user to perform all goods receipt actions', function (string $ability) {
    $goodsReceipt = GoodsReceipt::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can($ability, $goodsReceipt))->toBeFalse();
})->with(['view', 'update', 'delete', 'post', 'reverse']);

it('denies regular user to viewAny goods receipts', function () {
    expect($this->regularUser->can('viewAny', GoodsReceipt::class))->toBeFalse();
});

it('denies regular user to create goods receipts', function () {
    expect($this->regularUser->can('create', GoodsReceipt::class))->toBeFalse();
});
