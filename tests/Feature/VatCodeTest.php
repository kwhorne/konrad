<?php

use App\Models\User;
use App\Models\VatCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// Basic Creation
test('vat code can be created', function () {
    $vatCode = VatCode::factory()->create([
        'code' => '31',
        'name' => 'Innenlandsk omsetning 25%',
        'rate' => 25,
    ]);

    expect($vatCode)->toBeInstanceOf(VatCode::class);
    expect($vatCode->code)->toBe('31');
    expect((float) $vatCode->rate)->toBe(25.0);
});

// Category Name
test('vat code category name returns correct norwegian translation', function (string $category, string $expectedName) {
    $vatCode = VatCode::factory()->create(['category' => $category]);

    expect($vatCode->category_name)->toBe($expectedName);
})->with([
    ['salg_norge', 'Salg av varer og tjenester i Norge'],
    ['kjop_norge', 'Kjøp av varer og tjenester i Norge'],
    ['import', 'Kjøp av tjenester fra utlandet (import)'],
    ['export', 'Utførsel av varer og tjenester'],
    ['other', 'Andre forhold'],
]);

// Direction Name
test('vat code direction name returns correct norwegian translation', function (string $direction, string $expectedName) {
    $vatCode = VatCode::factory()->create(['direction' => $direction]);

    expect($vatCode->direction_name)->toBe($expectedName);
})->with([
    ['output', 'Utgående'],
    ['input', 'Inngående'],
]);

// Scopes
test('active scope filters correctly', function () {
    VatCode::factory()->count(3)->create(['is_active' => true]);
    VatCode::factory()->count(2)->inactive()->create();

    expect(VatCode::active()->count())->toBe(3);
});

test('byCategory scope filters correctly', function () {
    VatCode::factory()->count(2)->create(['category' => 'salg_norge']);
    VatCode::factory()->count(3)->create(['category' => 'kjop_norge']);

    expect(VatCode::byCategory('salg_norge')->count())->toBe(2);
    expect(VatCode::byCategory('kjop_norge')->count())->toBe(3);
});

test('ordered scope sorts by sort_order and code', function () {
    VatCode::factory()->create(['code' => '33', 'sort_order' => 2]);
    VatCode::factory()->create(['code' => '31', 'sort_order' => 1]);
    VatCode::factory()->create(['code' => '32', 'sort_order' => 1]);

    $codes = VatCode::ordered()->pluck('code')->toArray();

    expect($codes)->toBe(['31', '32', '33']);
});

// Boolean Casts
test('vat code affects_base is boolean', function () {
    $vatCode = VatCode::factory()->create(['affects_base' => true]);

    expect($vatCode->affects_base)->toBeTrue();
    expect($vatCode->affects_base)->toBeBool();
});

test('vat code affects_tax is boolean', function () {
    $vatCode = VatCode::factory()->create(['affects_tax' => true]);

    expect($vatCode->affects_tax)->toBeTrue();
    expect($vatCode->affects_tax)->toBeBool();
});

test('vat code is_active is boolean', function () {
    $vatCode = VatCode::factory()->create(['is_active' => true]);

    expect($vatCode->is_active)->toBeTrue();
    expect($vatCode->is_active)->toBeBool();
});

// Factory States
test('vat code factory output state works', function () {
    $vatCode = VatCode::factory()->output()->create();

    expect($vatCode->direction)->toBe('output');
    expect($vatCode->category)->toBe('salg_norge');
    expect($vatCode->sign)->toBe(1);
});

test('vat code factory input state works', function () {
    $vatCode = VatCode::factory()->input()->create();

    expect($vatCode->direction)->toBe('input');
    expect($vatCode->category)->toBe('kjop_norge');
    expect($vatCode->sign)->toBe(-1);
});

test('vat code factory inactive state works', function () {
    $vatCode = VatCode::factory()->inactive()->create();

    expect($vatCode->is_active)->toBeFalse();
});
