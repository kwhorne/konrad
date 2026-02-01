<?php

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductType;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($this->user)->create();
    $this->user->update(['current_company_id' => $this->company->id]);
    app()->instance('current.company', $this->company);
    $this->actingAs($this->user);
});

// Basic Creation
test('product can be created', function () {
    $product = Product::factory()->create([
        'name' => 'Konsulenttime',
        'sku' => 'KONS-001',
        'price' => 1500.00,
    ]);

    expect($product)->toBeInstanceOf(Product::class);
    expect($product->name)->toBe('Konsulenttime');
    expect($product->sku)->toBe('KONS-001');
    expect((float) $product->price)->toBe(1500.0);
});

// Relationships
test('product belongs to product group', function () {
    $group = ProductGroup::factory()->create(['name' => 'Tjenester']);
    $product = Product::factory()->withGroup()->create(['product_group_id' => $group->id]);

    expect($product->productGroup->id)->toBe($group->id);
    expect($product->productGroup->name)->toBe('Tjenester');
});

test('product belongs to product type', function () {
    $type = ProductType::factory()->create(['name' => 'Tjeneste']);
    $product = Product::factory()->create(['product_type_id' => $type->id]);

    expect($product->productType->id)->toBe($type->id);
    expect($product->productType->name)->toBe('Tjeneste');
});

test('product belongs to unit', function () {
    $unit = Unit::factory()->create(['name' => 'timer', 'symbol' => 't']);
    $product = Product::factory()->create(['unit_id' => $unit->id]);

    expect($product->unit->id)->toBe($unit->id);
    expect($product->unit->name)->toBe('timer');
});

// Scopes
test('active scope filters correctly', function () {
    Product::factory()->count(3)->create(['is_active' => true]);
    Product::factory()->count(2)->inactive()->create();

    expect(Product::active()->count())->toBe(3);
});

test('ordered scope sorts by sort_order and name', function () {
    Product::factory()->create(['name' => 'Zebra', 'sort_order' => 1]);
    Product::factory()->create(['name' => 'Alpha', 'sort_order' => 1]);
    Product::factory()->create(['name' => 'Beta', 'sort_order' => 0]);

    $names = Product::ordered()->pluck('name')->toArray();

    expect($names[0])->toBe('Beta');
    expect($names[1])->toBe('Alpha');
    expect($names[2])->toBe('Zebra');
});

// Decimal Casts
test('product price is decimal', function () {
    $product = Product::factory()->create(['price' => 1234.56]);

    expect((float) $product->price)->toBe(1234.56);
});

test('product cost_price is decimal', function () {
    $product = Product::factory()->create(['cost_price' => 789.12]);

    expect((float) $product->cost_price)->toBe(789.12);
});

// Boolean Casts
test('product is_active is boolean', function () {
    $product = Product::factory()->create(['is_active' => true]);

    expect($product->is_active)->toBeTrue();
    expect($product->is_active)->toBeBool();
});

// Factory States
test('product factory inactive state works', function () {
    $product = Product::factory()->inactive()->create();

    expect($product->is_active)->toBeFalse();
});

test('product factory withGroup state works', function () {
    $product = Product::factory()->withGroup()->create();

    expect($product->product_group_id)->not->toBeNull();
    expect($product->productGroup)->toBeInstanceOf(ProductGroup::class);
});

// Profit Margin
test('product can calculate profit margin', function () {
    $product = Product::factory()->create([
        'price' => 1000,
        'cost_price' => 600,
    ]);

    $margin = $product->price - $product->cost_price;
    $marginPercent = ($margin / $product->price) * 100;

    expect((float) $margin)->toBe(400.0);
    expect($marginPercent)->toBe(40.0);
});

// SKU Uniqueness
test('product sku is unique', function () {
    Product::factory()->create(['sku' => 'UNIQUE-SKU']);

    expect(fn () => Product::factory()->create(['sku' => 'UNIQUE-SKU']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
