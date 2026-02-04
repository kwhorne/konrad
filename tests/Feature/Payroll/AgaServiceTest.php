<?php

use App\Models\AgaZone;
use App\Services\Payroll\AgaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(AgaService::class);

    // Seed AGA zones for testing
    AgaZone::insert([
        ['code' => '1', 'name' => 'Sone I', 'rate' => 14.1, 'fribeloep' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ['code' => '1a', 'name' => 'Sone Ia', 'rate' => 10.6, 'fribeloep' => 500000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ['code' => '2', 'name' => 'Sone II', 'rate' => 10.6, 'fribeloep' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ['code' => '3', 'name' => 'Sone III', 'rate' => 6.4, 'fribeloep' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ['code' => '4', 'name' => 'Sone IV', 'rate' => 5.1, 'fribeloep' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ['code' => '4a', 'name' => 'Sone IVa', 'rate' => 7.9, 'fribeloep' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ['code' => '5', 'name' => 'Sone V (Svalbard)', 'rate' => 0.0, 'fribeloep' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
    ]);
});

test('calculates AGA correctly for zone 1', function () {
    $amount = 50000;

    $aga = $this->service->calculateArbeidsgiveravgift($amount, '1');

    expect($aga)->toBe(7050.00); // 50000 * 14.1% = 7050
});

test('calculates AGA correctly for zone 2', function () {
    $amount = 50000;

    $aga = $this->service->calculateArbeidsgiveravgift($amount, '2');

    expect($aga)->toBe(5300.00); // 50000 * 10.6% = 5300
});

test('calculates AGA correctly for zone 3', function () {
    $amount = 50000;

    $aga = $this->service->calculateArbeidsgiveravgift($amount, '3');

    expect($aga)->toBe(3200.00); // 50000 * 6.4% = 3200
});

test('calculates AGA correctly for zone 4', function () {
    $amount = 50000;

    $aga = $this->service->calculateArbeidsgiveravgift($amount, '4');

    expect($aga)->toBe(2550.00); // 50000 * 5.1% = 2550
});

test('calculates AGA correctly for zone 5 (Svalbard)', function () {
    $amount = 50000;

    $aga = $this->service->calculateArbeidsgiveravgift($amount, '5');

    expect($aga)->toBe(0.00); // 50000 * 0% = 0
});

test('returns zone 1 rate for unknown zone', function () {
    $amount = 50000;

    $aga = $this->service->calculateArbeidsgiveravgift($amount, 'unknown');

    expect($aga)->toBe(7050.00); // Defaults to 14.1%
});

test('returns correct zone rate', function () {
    expect($this->service->getZoneRate('1'))->toBe(14.1);
    expect($this->service->getZoneRate('2'))->toBe(10.6);
    expect($this->service->getZoneRate('3'))->toBe(6.4);
    expect($this->service->getZoneRate('4'))->toBe(5.1);
    expect($this->service->getZoneRate('5'))->toBe(0.0);
});

test('returns active zones in correct order', function () {
    $zones = $this->service->getActiveZones();

    expect($zones)->toHaveCount(7);
    expect($zones->first()->code)->toBe('1');
    expect($zones->last()->code)->toBe('5');
});

test('calculates AGA with fribeloep for zone 1a', function () {
    $amount = 100000;

    $result = $this->service->calculateAgaWithFribeloep($amount, '1a', 0);

    // All within fribeloep (500000), so taxed at reduced rate
    expect($result['aga'])->toBe(10600.00); // 100000 * 10.6%
    expect($result['fribeloep_used'])->toBe(100000.0);
    expect($result['saved_amount'])->toBe(3500.00); // 100000 * (14.1 - 10.6)%
});

test('calculates AGA with partially used fribeloep', function () {
    $amount = 100000;
    $previouslyUsed = 450000;

    $result = $this->service->calculateAgaWithFribeloep($amount, '1a', $previouslyUsed);

    // Only 50000 remaining fribeloep
    expect($result['fribeloep_used'])->toBe(50000.0);
    expect($result['saved_amount'])->toBe(1750.00); // 50000 * (14.1 - 10.6)%
});

test('calculates AGA when fribeloep is exhausted', function () {
    $amount = 100000;
    $previouslyUsed = 500000; // Fribeloep exhausted

    $result = $this->service->calculateAgaWithFribeloep($amount, '1a', $previouslyUsed);

    expect((float) $result['fribeloep_used'])->toBe(0.0);
    expect($result)->not->toHaveKey('saved_amount');
});

test('handles zero amount', function () {
    $aga = $this->service->calculateArbeidsgiveravgift(0, '1');

    expect($aga)->toBe(0.00);
});

test('handles decimal amounts correctly', function () {
    $amount = 50000.50;

    $aga = $this->service->calculateArbeidsgiveravgift($amount, '1');

    expect($aga)->toBe(7050.07); // 50000.50 * 14.1% = 7050.0705 rounded to 7050.07
});
