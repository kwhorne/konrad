<?php

use App\Models\Account;
use App\Models\Company;
use App\Models\DeferredTaxItem;
use App\Models\Department;
use App\Models\ShareClass;
use App\Models\Shareholder;
use App\Models\ShareTransaction;
use App\Models\StockLocation;
use App\Models\TaxAdjustment;
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

// viewAny tests
it('allows admin to viewAny for economy models', function (string $modelClass) {
    expect($this->admin->can('viewAny', $modelClass))->toBeTrue();
})->with([
    'Account' => Account::class,
    'DeferredTaxItem' => DeferredTaxItem::class,
    'Department' => Department::class,
    'ShareClass' => ShareClass::class,
    'Shareholder' => Shareholder::class,
    'ShareTransaction' => ShareTransaction::class,
    'StockLocation' => StockLocation::class,
    'TaxAdjustment' => TaxAdjustment::class,
]);

it('allows economy user to viewAny for economy models', function (string $modelClass) {
    expect($this->economyUser->can('viewAny', $modelClass))->toBeTrue();
})->with([
    'Account' => Account::class,
    'DeferredTaxItem' => DeferredTaxItem::class,
    'Department' => Department::class,
    'ShareClass' => ShareClass::class,
    'Shareholder' => Shareholder::class,
    'ShareTransaction' => ShareTransaction::class,
    'StockLocation' => StockLocation::class,
    'TaxAdjustment' => TaxAdjustment::class,
]);

it('denies regular user to viewAny for economy models', function (string $modelClass) {
    expect($this->regularUser->can('viewAny', $modelClass))->toBeFalse();
})->with([
    'Account' => Account::class,
    'DeferredTaxItem' => DeferredTaxItem::class,
    'Department' => Department::class,
    'ShareClass' => ShareClass::class,
    'Shareholder' => Shareholder::class,
    'ShareTransaction' => ShareTransaction::class,
    'StockLocation' => StockLocation::class,
    'TaxAdjustment' => TaxAdjustment::class,
]);

// create tests
it('allows admin to create economy models', function (string $modelClass) {
    expect($this->admin->can('create', $modelClass))->toBeTrue();
})->with([
    'Account' => Account::class,
    'DeferredTaxItem' => DeferredTaxItem::class,
    'Department' => Department::class,
    'ShareClass' => ShareClass::class,
    'Shareholder' => Shareholder::class,
    'ShareTransaction' => ShareTransaction::class,
    'StockLocation' => StockLocation::class,
    'TaxAdjustment' => TaxAdjustment::class,
]);

it('allows economy user to create economy models', function (string $modelClass) {
    expect($this->economyUser->can('create', $modelClass))->toBeTrue();
})->with([
    'Account' => Account::class,
    'DeferredTaxItem' => DeferredTaxItem::class,
    'Department' => Department::class,
    'ShareClass' => ShareClass::class,
    'Shareholder' => Shareholder::class,
    'ShareTransaction' => ShareTransaction::class,
    'StockLocation' => StockLocation::class,
    'TaxAdjustment' => TaxAdjustment::class,
]);

it('denies regular user to create economy models', function (string $modelClass) {
    expect($this->regularUser->can('create', $modelClass))->toBeFalse();
})->with([
    'Account' => Account::class,
    'DeferredTaxItem' => DeferredTaxItem::class,
    'Department' => Department::class,
    'ShareClass' => ShareClass::class,
    'Shareholder' => Shareholder::class,
    'ShareTransaction' => ShareTransaction::class,
    'StockLocation' => StockLocation::class,
    'TaxAdjustment' => TaxAdjustment::class,
]);

// view instance tests (all models with view policy method)
it('allows admin to view economy model instance', function (string $modelClass, array $extraAttributes) {
    $model = $modelClass::factory()->create(array_merge(['company_id' => $this->company->id], $extraAttributes));
    expect($this->admin->can('view', $model))->toBeTrue();
})->with([
    'Account' => [Account::class, []],
    'DeferredTaxItem' => [DeferredTaxItem::class, []],
    'Department' => [Department::class, []],
    'ShareClass' => [ShareClass::class, []],
    'Shareholder' => [Shareholder::class, []],
    'ShareTransaction' => [ShareTransaction::class, []],
    'StockLocation' => [StockLocation::class, []],
    'TaxAdjustment' => [TaxAdjustment::class, []],
]);

it('allows economy user to view economy model instance', function (string $modelClass, array $extraAttributes) {
    $model = $modelClass::factory()->create(array_merge(['company_id' => $this->company->id], $extraAttributes));
    expect($this->economyUser->can('view', $model))->toBeTrue();
})->with([
    'Account' => [Account::class, []],
    'DeferredTaxItem' => [DeferredTaxItem::class, []],
    'Department' => [Department::class, []],
    'ShareClass' => [ShareClass::class, []],
    'Shareholder' => [Shareholder::class, []],
    'ShareTransaction' => [ShareTransaction::class, []],
    'StockLocation' => [StockLocation::class, []],
    'TaxAdjustment' => [TaxAdjustment::class, []],
]);

it('denies regular user to view economy model instance', function (string $modelClass, array $extraAttributes) {
    $model = $modelClass::factory()->create(array_merge(['company_id' => $this->company->id], $extraAttributes));
    expect($this->regularUser->can('view', $model))->toBeFalse();
})->with([
    'Account' => [Account::class, []],
    'DeferredTaxItem' => [DeferredTaxItem::class, []],
    'Department' => [Department::class, []],
    'ShareClass' => [ShareClass::class, []],
    'Shareholder' => [Shareholder::class, []],
    'ShareTransaction' => [ShareTransaction::class, []],
    'StockLocation' => [StockLocation::class, []],
    'TaxAdjustment' => [TaxAdjustment::class, []],
]);

// update instance tests (models with update policy method, excluding Account and ShareTransaction)
it('allows admin to update economy model instance', function (string $modelClass) {
    $model = $modelClass::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('update', $model))->toBeTrue();
})->with([
    'DeferredTaxItem' => DeferredTaxItem::class,
    'Department' => Department::class,
    'ShareClass' => ShareClass::class,
    'Shareholder' => Shareholder::class,
    'StockLocation' => StockLocation::class,
    'TaxAdjustment' => TaxAdjustment::class,
]);

it('allows economy user to update economy model instance', function (string $modelClass) {
    $model = $modelClass::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can('update', $model))->toBeTrue();
})->with([
    'DeferredTaxItem' => DeferredTaxItem::class,
    'Department' => Department::class,
    'ShareClass' => ShareClass::class,
    'Shareholder' => Shareholder::class,
    'StockLocation' => StockLocation::class,
    'TaxAdjustment' => TaxAdjustment::class,
]);

it('denies regular user to update economy model instance', function (string $modelClass) {
    $model = $modelClass::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('update', $model))->toBeFalse();
})->with([
    'DeferredTaxItem' => DeferredTaxItem::class,
    'Department' => Department::class,
    'ShareClass' => ShareClass::class,
    'Shareholder' => Shareholder::class,
    'StockLocation' => StockLocation::class,
    'TaxAdjustment' => TaxAdjustment::class,
]);

// delete instance tests (models with delete policy method, excluding Account and ShareTransaction)
it('allows admin to delete economy model instance', function (string $modelClass) {
    $model = $modelClass::factory()->create(['company_id' => $this->company->id]);
    expect($this->admin->can('delete', $model))->toBeTrue();
})->with([
    'DeferredTaxItem' => DeferredTaxItem::class,
    'Department' => Department::class,
    'ShareClass' => ShareClass::class,
    'Shareholder' => Shareholder::class,
    'StockLocation' => StockLocation::class,
    'TaxAdjustment' => TaxAdjustment::class,
]);

it('allows economy user to delete economy model instance', function (string $modelClass) {
    $model = $modelClass::factory()->create(['company_id' => $this->company->id]);
    expect($this->economyUser->can('delete', $model))->toBeTrue();
})->with([
    'DeferredTaxItem' => DeferredTaxItem::class,
    'Department' => Department::class,
    'ShareClass' => ShareClass::class,
    'Shareholder' => Shareholder::class,
    'StockLocation' => StockLocation::class,
    'TaxAdjustment' => TaxAdjustment::class,
]);

it('denies regular user to delete economy model instance', function (string $modelClass) {
    $model = $modelClass::factory()->create(['company_id' => $this->company->id]);
    expect($this->regularUser->can('delete', $model))->toBeFalse();
})->with([
    'DeferredTaxItem' => DeferredTaxItem::class,
    'Department' => Department::class,
    'ShareClass' => ShareClass::class,
    'Shareholder' => Shareholder::class,
    'StockLocation' => StockLocation::class,
    'TaxAdjustment' => TaxAdjustment::class,
]);

// AccountPolicy specific tests - is_system checks
it('allows admin to update system account', function () {
    $account = Account::factory()->create(['company_id' => $this->company->id, 'is_system' => true]);
    expect($this->admin->can('update', $account))->toBeTrue();
});

it('allows economy user to update non-system account', function () {
    $account = Account::factory()->create(['company_id' => $this->company->id, 'is_system' => false]);
    expect($this->economyUser->can('update', $account))->toBeTrue();
});

it('denies economy user to update system account', function () {
    $account = Account::factory()->create(['company_id' => $this->company->id, 'is_system' => true]);
    expect($this->economyUser->can('update', $account))->toBeFalse();
});

it('allows admin to delete system account', function () {
    $account = Account::factory()->create(['company_id' => $this->company->id, 'is_system' => true]);
    expect($this->admin->can('delete', $account))->toBeTrue();
});

it('allows economy user to delete non-system account', function () {
    $account = Account::factory()->create(['company_id' => $this->company->id, 'is_system' => false]);
    expect($this->economyUser->can('delete', $account))->toBeTrue();
});

it('denies economy user to delete system account', function () {
    $account = Account::factory()->create(['company_id' => $this->company->id, 'is_system' => true]);
    expect($this->economyUser->can('delete', $account))->toBeFalse();
});

it('denies regular user to update non-system account', function () {
    $account = Account::factory()->create(['company_id' => $this->company->id, 'is_system' => false]);
    expect($this->regularUser->can('update', $account))->toBeFalse();
});

it('denies regular user to delete non-system account', function () {
    $account = Account::factory()->create(['company_id' => $this->company->id, 'is_system' => false]);
    expect($this->regularUser->can('delete', $account))->toBeFalse();
});
