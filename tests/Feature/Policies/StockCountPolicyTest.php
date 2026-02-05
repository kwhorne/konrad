<?php

use App\Models\Company;
use App\Models\StockCount;
use App\Models\StockLocation;
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

function createStockCount(object $testCase): StockCount
{
    $stockLocation = StockLocation::factory()->create(['company_id' => $testCase->company->id]);

    return StockCount::create([
        'company_id' => $testCase->company->id,
        'stock_location_id' => $stockLocation->id,
        'count_date' => now(),
        'description' => 'Test stock count',
        'status' => 'draft',
        'created_by' => $testCase->admin->id,
    ]);
}

// Admin can do everything
it('allows admin to perform all stock count actions', function (string $ability) {
    $stockCount = createStockCount($this);
    expect($this->admin->can($ability, $stockCount))->toBeTrue();
})->with(['view', 'update', 'delete', 'start', 'complete', 'post', 'cancel']);

it('allows admin to viewAny stock counts', function () {
    expect($this->admin->can('viewAny', StockCount::class))->toBeTrue();
});

it('allows admin to create stock counts', function () {
    expect($this->admin->can('create', StockCount::class))->toBeTrue();
});

// Economy user can do everything
it('allows economy user to perform all stock count actions', function (string $ability) {
    $stockCount = createStockCount($this);
    expect($this->economyUser->can($ability, $stockCount))->toBeTrue();
})->with(['view', 'update', 'delete', 'start', 'complete', 'post', 'cancel']);

it('allows economy user to viewAny stock counts', function () {
    expect($this->economyUser->can('viewAny', StockCount::class))->toBeTrue();
});

it('allows economy user to create stock counts', function () {
    expect($this->economyUser->can('create', StockCount::class))->toBeTrue();
});

// Regular user denied for all
it('denies regular user to perform all stock count actions', function (string $ability) {
    $stockCount = createStockCount($this);
    expect($this->regularUser->can($ability, $stockCount))->toBeFalse();
})->with(['view', 'update', 'delete', 'start', 'complete', 'post', 'cancel']);

it('denies regular user to viewAny stock counts', function () {
    expect($this->regularUser->can('viewAny', StockCount::class))->toBeFalse();
});

it('denies regular user to create stock counts', function () {
    expect($this->regularUser->can('create', StockCount::class))->toBeFalse();
});
