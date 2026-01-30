<?php

use App\Models\ShareClass;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Basic Creation
test('share class can be created', function () {
    $shareClass = ShareClass::factory()->create([
        'name' => 'Ordinære aksjer',
        'code' => 'ORD',
        'par_value' => 1.00,
        'total_shares' => 10000,
    ]);

    expect($shareClass)->toBeInstanceOf(ShareClass::class);
    expect($shareClass->name)->toBe('Ordinære aksjer');
    expect($shareClass->code)->toBe('ORD');
    expect((float) $shareClass->par_value)->toBe(1.0);
    expect($shareClass->total_shares)->toBe(10000);
});

// Display Name
test('share class display name combines code and name', function () {
    $shareClass = ShareClass::factory()->create([
        'code' => 'A',
        'name' => 'A-aksjer',
    ]);

    expect($shareClass->getDisplayName())->toBe('A - A-aksjer');
});

// Formatted Par Value
test('share class formatted par value includes currency', function () {
    $shareClass = ShareClass::factory()->create(['par_value' => 100.00]);

    expect($shareClass->getFormattedParValue())->toBe('100,00 NOK');
});

// Total Capital
test('share class calculates total capital', function () {
    $shareClass = ShareClass::factory()->create([
        'par_value' => 10.00,
        'total_shares' => 1000,
    ]);

    expect($shareClass->getTotalCapital())->toBe(10000.0);
});

test('share class formatted total capital includes currency', function () {
    $shareClass = ShareClass::factory()->create([
        'par_value' => 10.00,
        'total_shares' => 1000,
    ]);

    expect($shareClass->getFormattedTotalCapital())->toBe('10 000,00 NOK');
});

// Rights Description
test('share class rights description shows voting and dividend rights', function () {
    $shareClass = ShareClass::factory()->create([
        'has_voting_rights' => true,
        'has_dividend_rights' => true,
        'voting_weight' => 1.00,
    ]);

    expect($shareClass->getRightsDescription())->toBe('Stemmerett, Utbytterett');
});

test('share class rights description shows voting weight when not 1', function () {
    $shareClass = ShareClass::factory()->create([
        'has_voting_rights' => true,
        'has_dividend_rights' => false,
        'voting_weight' => 2.00,
    ]);

    expect($shareClass->getRightsDescription())->toBe('Stemmerett (2.00x)');
});

test('share class with no rights shows appropriate message', function () {
    $shareClass = ShareClass::factory()->withoutVotingRights()->withoutDividendRights()->create();

    expect($shareClass->getRightsDescription())->toBe('Ingen særskilte rettigheter');
});

// Voting Power
test('share class calculates total voting power', function () {
    $shareClass = ShareClass::factory()->create([
        'has_voting_rights' => true,
        'total_shares' => 1000,
        'voting_weight' => 2.00,
    ]);

    expect($shareClass->getTotalVotingPower())->toBe(2000.0);
});

test('share class without voting rights has zero voting power', function () {
    $shareClass = ShareClass::factory()->withoutVotingRights()->create([
        'total_shares' => 1000,
    ]);

    expect($shareClass->getTotalVotingPower())->toBe(0.0);
});

// Scopes
test('active scope filters correctly', function () {
    ShareClass::factory()->count(3)->create(['is_active' => true]);
    ShareClass::factory()->count(2)->inactive()->create();

    expect(ShareClass::active()->count())->toBe(3);
});

test('withVotingRights scope filters correctly', function () {
    ShareClass::factory()->count(2)->create(['has_voting_rights' => true]);
    ShareClass::factory()->count(3)->withoutVotingRights()->create();

    expect(ShareClass::withVotingRights()->count())->toBe(2);
});

test('withDividendRights scope filters correctly', function () {
    ShareClass::factory()->count(2)->create(['has_dividend_rights' => true]);
    ShareClass::factory()->count(3)->withoutDividendRights()->create();

    expect(ShareClass::withDividendRights()->count())->toBe(2);
});

// Boolean Casts
test('share class has_voting_rights is boolean', function () {
    $shareClass = ShareClass::factory()->create(['has_voting_rights' => true]);

    expect($shareClass->has_voting_rights)->toBeTrue();
    expect($shareClass->has_voting_rights)->toBeBool();
});

test('share class has_dividend_rights is boolean', function () {
    $shareClass = ShareClass::factory()->create(['has_dividend_rights' => true]);

    expect($shareClass->has_dividend_rights)->toBeTrue();
    expect($shareClass->has_dividend_rights)->toBeBool();
});

test('share class is_active is boolean', function () {
    $shareClass = ShareClass::factory()->create(['is_active' => true]);

    expect($shareClass->is_active)->toBeTrue();
    expect($shareClass->is_active)->toBeBool();
});

// Factory States
test('share class factory withoutVotingRights state works', function () {
    $shareClass = ShareClass::factory()->withoutVotingRights()->create();

    expect($shareClass->has_voting_rights)->toBeFalse();
    expect((float) $shareClass->voting_weight)->toBe(0.0);
});

test('share class factory withoutDividendRights state works', function () {
    $shareClass = ShareClass::factory()->withoutDividendRights()->create();

    expect($shareClass->has_dividend_rights)->toBeFalse();
});

test('share class factory inactive state works', function () {
    $shareClass = ShareClass::factory()->inactive()->create();

    expect($shareClass->is_active)->toBeFalse();
});
