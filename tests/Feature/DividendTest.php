<?php

use App\Models\Dividend;
use App\Models\ShareClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// Basic Creation
test('dividend can be created', function () {
    $dividend = Dividend::factory()->create([
        'fiscal_year' => 2024,
        'amount_per_share' => 5.00,
        'total_amount' => 50000,
    ]);

    expect($dividend)->toBeInstanceOf(Dividend::class);
    expect($dividend->fiscal_year)->toBe(2024);
    expect((float) $dividend->amount_per_share)->toBe(5.0);
    expect((float) $dividend->total_amount)->toBe(50000.0);
});

// Dividend Type Labels
test('dividend type label returns correct norwegian translation', function (string $type, string $expectedLabel) {
    $dividend = Dividend::factory()->create(['dividend_type' => $type]);

    expect($dividend->getDividendTypeLabel())->toBe($expectedLabel);
})->with([
    ['ordinary', 'Ordinært utbytte'],
    ['extraordinary', 'Ekstraordinært utbytte'],
]);

// Dividend Type Badge Colors
test('dividend type badge color returns correct color', function (string $type, string $expectedColor) {
    $dividend = Dividend::factory()->create(['dividend_type' => $type]);

    expect($dividend->getDividendTypeBadgeColor())->toBe($expectedColor);
})->with([
    ['ordinary', 'primary'],
    ['extraordinary', 'warning'],
]);

// Status Labels
test('dividend status label returns correct norwegian translation', function (string $status, string $expectedLabel) {
    $dividend = Dividend::factory()->create(['status' => $status]);

    expect($dividend->getStatusLabel())->toBe($expectedLabel);
})->with([
    ['declared', 'Vedtatt'],
    ['approved', 'Godkjent'],
    ['paid', 'Utbetalt'],
    ['cancelled', 'Kansellert'],
]);

// Status Badge Colors
test('dividend status badge color returns correct color', function (string $status, string $expectedColor) {
    $dividend = Dividend::factory()->create(['status' => $status]);

    expect($dividend->getStatusBadgeColor())->toBe($expectedColor);
})->with([
    ['declared', 'warning'],
    ['approved', 'info'],
    ['paid', 'success'],
    ['cancelled', 'danger'],
]);

// Formatted Values
test('dividend formatted amount per share includes currency', function () {
    $dividend = Dividend::factory()->create(['amount_per_share' => 12.5000]);

    expect($dividend->getFormattedAmountPerShare())->toBe('12,5000 NOK');
});

test('dividend formatted total amount includes currency', function () {
    $dividend = Dividend::factory()->create(['total_amount' => 500000.00]);

    expect($dividend->getFormattedTotalAmount())->toBe('500 000,00 NOK');
});

// Relationships
test('dividend belongs to share class', function () {
    $shareClass = ShareClass::factory()->create();
    $dividend = Dividend::factory()->create(['share_class_id' => $shareClass->id]);

    expect($dividend->shareClass->id)->toBe($shareClass->id);
});

test('dividend belongs to creator', function () {
    $creator = User::factory()->create();
    $dividend = Dividend::factory()->create(['created_by' => $creator->id]);

    expect($dividend->creator->id)->toBe($creator->id);
});

// Status Methods
test('dividend isPaid returns true when status is paid', function () {
    $dividend = Dividend::factory()->paid()->create();

    expect($dividend->isPaid())->toBeTrue();
});

test('dividend isCancelled returns true when status is cancelled', function () {
    $dividend = Dividend::factory()->cancelled()->create();

    expect($dividend->isCancelled())->toBeTrue();
});

test('dividend canBePaid returns true for declared or approved status', function () {
    $declared = Dividend::factory()->create(['status' => 'declared']);
    $approved = Dividend::factory()->approved()->create();
    $paid = Dividend::factory()->paid()->create();

    expect($declared->canBePaid())->toBeTrue();
    expect($approved->canBePaid())->toBeTrue();
    expect($paid->canBePaid())->toBeFalse();
});

test('dividend canBeCancelled returns true for declared or approved status', function () {
    $declared = Dividend::factory()->create(['status' => 'declared']);
    $approved = Dividend::factory()->approved()->create();
    $paid = Dividend::factory()->paid()->create();

    expect($declared->canBeCancelled())->toBeTrue();
    expect($approved->canBeCancelled())->toBeTrue();
    expect($paid->canBeCancelled())->toBeFalse();
});

// Status Transitions
test('dividend can be marked as approved', function () {
    $dividend = Dividend::factory()->create(['status' => 'declared']);

    $dividend->markAsApproved();

    expect($dividend->status)->toBe('approved');
});

test('dividend can be marked as paid', function () {
    $dividend = Dividend::factory()->approved()->create();

    $dividend->markAsPaid();

    expect($dividend->status)->toBe('paid');
});

test('dividend can be cancelled', function () {
    $dividend = Dividend::factory()->approved()->create();

    $dividend->cancel();

    expect($dividend->status)->toBe('cancelled');
});

// Scopes
test('forYear scope filters correctly', function () {
    Dividend::factory()->count(2)->create(['fiscal_year' => 2024]);
    Dividend::factory()->count(3)->create(['fiscal_year' => 2023]);

    expect(Dividend::forYear(2024)->count())->toBe(2);
});

test('byStatus scope filters correctly', function () {
    Dividend::factory()->count(2)->create(['status' => 'declared']);
    Dividend::factory()->count(3)->paid()->create();

    expect(Dividend::byStatus('declared')->count())->toBe(2);
    expect(Dividend::byStatus('paid')->count())->toBe(3);
});

test('paid scope filters correctly', function () {
    Dividend::factory()->count(2)->create(['status' => 'declared']);
    Dividend::factory()->count(3)->paid()->create();

    expect(Dividend::paid()->count())->toBe(3);
});

test('pending scope filters correctly', function () {
    Dividend::factory()->count(2)->create(['status' => 'declared']);
    Dividend::factory()->count(1)->approved()->create();
    Dividend::factory()->count(3)->paid()->create();

    expect(Dividend::pending()->count())->toBe(3);
});

test('ordinary scope filters correctly', function () {
    Dividend::factory()->count(2)->ordinary()->create();
    Dividend::factory()->count(3)->extraordinary()->create();

    expect(Dividend::ordinary()->count())->toBe(2);
});

test('extraordinary scope filters correctly', function () {
    Dividend::factory()->count(2)->ordinary()->create();
    Dividend::factory()->count(3)->extraordinary()->create();

    expect(Dividend::extraordinary()->count())->toBe(3);
});

// Factory States
test('dividend factory ordinary state works', function () {
    $dividend = Dividend::factory()->ordinary()->create();

    expect($dividend->dividend_type)->toBe('ordinary');
});

test('dividend factory extraordinary state works', function () {
    $dividend = Dividend::factory()->extraordinary()->create();

    expect($dividend->dividend_type)->toBe('extraordinary');
});

test('dividend factory paid state works', function () {
    $dividend = Dividend::factory()->paid()->create();

    expect($dividend->status)->toBe('paid');
});

test('dividend factory approved state works', function () {
    $dividend = Dividend::factory()->approved()->create();

    expect($dividend->status)->toBe('approved');
});

test('dividend factory cancelled state works', function () {
    $dividend = Dividend::factory()->cancelled()->create();

    expect($dividend->status)->toBe('cancelled');
});
