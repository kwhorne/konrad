<?php

use App\Services\DocumentTotalsService;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->service = new DocumentTotalsService;
});

it('calculates totals with no lines', function () {
    $lines = new Collection;

    $totals = $this->service->calculate($lines);

    expect($totals)->toBe([
        'subtotal' => 0.0,
        'discount_total' => 0.0,
        'vat_total' => 0.0,
        'total' => 0.0,
    ]);
});

it('calculates totals with single line no discount', function () {
    $lines = new Collection([
        (object) [
            'quantity' => 2,
            'unit_price' => 100,
            'discount_percent' => 0,
            'vat_percent' => 25,
        ],
    ]);

    $totals = $this->service->calculate($lines);

    expect($totals['subtotal'])->toBe(200.0);
    expect($totals['discount_total'])->toBe(0.0);
    expect($totals['vat_total'])->toBe(50.0);
    expect($totals['total'])->toBe(250.0);
});

it('calculates totals with discount', function () {
    $lines = new Collection([
        (object) [
            'quantity' => 10,
            'unit_price' => 100,
            'discount_percent' => 10,
            'vat_percent' => 25,
        ],
    ]);

    $totals = $this->service->calculate($lines);

    expect($totals['subtotal'])->toBe(1000.0);
    expect($totals['discount_total'])->toBe(100.0);
    expect($totals['vat_total'])->toBe(225.0);
    expect($totals['total'])->toBe(1125.0);
});

it('calculates totals with multiple lines and different VAT rates', function () {
    $lines = new Collection([
        (object) [
            'quantity' => 1,
            'unit_price' => 1000,
            'discount_percent' => 0,
            'vat_percent' => 25,
        ],
        (object) [
            'quantity' => 2,
            'unit_price' => 500,
            'discount_percent' => 10,
            'vat_percent' => 15,
        ],
    ]);

    $totals = $this->service->calculate($lines);

    expect($totals['subtotal'])->toBe(2000.0);
    expect($totals['discount_total'])->toBe(100.0);
    expect($totals['vat_total'])->toBe(385.0);
    expect($totals['total'])->toBe(2285.0);
});

it('calculates totals from array', function () {
    $lines = [
        [
            'quantity' => 3,
            'unit_price' => 200,
            'discount_percent' => 5,
            'vat_percent' => 25,
        ],
    ];

    $totals = $this->service->calculateFromArray($lines);

    expect($totals['subtotal'])->toBe(600.0);
    expect($totals['discount_total'])->toBe(30.0);
    expect($totals['vat_total'])->toBe(142.5);
    expect($totals['total'])->toBe(712.5);
});

it('calculates VAT breakdown by rate', function () {
    $lines = new Collection([
        (object) [
            'quantity' => 1,
            'unit_price' => 100,
            'discount_percent' => 0,
            'vat_percent' => 25,
        ],
        (object) [
            'quantity' => 1,
            'unit_price' => 100,
            'discount_percent' => 0,
            'vat_percent' => 25,
        ],
        (object) [
            'quantity' => 1,
            'unit_price' => 100,
            'discount_percent' => 0,
            'vat_percent' => 15,
        ],
    ]);

    $breakdown = $this->service->getVatBreakdown($lines);

    expect($breakdown)->toHaveKeys(['25', '15']);
    expect($breakdown['25']['base'])->toBe(200.0);
    expect($breakdown['25']['vat'])->toBe(50.0);
    expect($breakdown['15']['base'])->toBe(100.0);
    expect($breakdown['15']['vat'])->toBe(15.0);
});

it('handles null discount_percent and vat_percent', function () {
    $lines = new Collection([
        (object) [
            'quantity' => 1,
            'unit_price' => 100,
            'discount_percent' => null,
            'vat_percent' => null,
        ],
    ]);

    $totals = $this->service->calculate($lines);

    expect($totals['subtotal'])->toBe(100.0);
    expect($totals['discount_total'])->toBe(0.0);
    expect($totals['vat_total'])->toBe(0.0);
    expect($totals['total'])->toBe(100.0);
});

it('handles decimal quantities and prices', function () {
    $lines = new Collection([
        (object) [
            'quantity' => 1.5,
            'unit_price' => 99.99,
            'discount_percent' => 0,
            'vat_percent' => 25,
        ],
    ]);

    $totals = $this->service->calculate($lines);

    expect($totals['subtotal'])->toBe(149.99);
    expect($totals['vat_total'])->toBe(37.5);
    expect($totals['total'])->toBe(187.48); // 149.99 + 37.4975 rounded = 187.48
});
