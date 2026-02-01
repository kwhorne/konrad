<?php

use App\Models\Company;
use App\Models\InventorySettings;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockLocation;
use App\Models\StockReservation;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->company = Company::factory()->create();
    $this->product = Product::factory()->for($this->company)->create([
        'is_stocked' => true,
        'cost_price' => 100,
    ]);
    $this->location = StockLocation::factory()->for($this->company)->create();

    $this->stockService = app(StockService::class);
});

describe('receipt', function () {
    it('creates stock transaction and increases quantity', function () {
        $transaction = $this->stockService->receipt(
            product: $this->product,
            stockLocation: $this->location,
            quantity: 10,
            unitCost: 50.00
        );

        expect($transaction)->toBeInstanceOf(StockTransaction::class)
            ->and($transaction->transaction_type)->toBe('receipt')
            ->and((float) $transaction->quantity)->toEqual(10.0)
            ->and((float) $transaction->unit_cost)->toEqual(50.0)
            ->and((float) $transaction->quantity_before)->toEqual(0.0)
            ->and((float) $transaction->quantity_after)->toEqual(10.0);

        $stockLevel = StockLevel::where('product_id', $this->product->id)
            ->where('stock_location_id', $this->location->id)
            ->first();

        expect((float) $stockLevel->quantity_on_hand)->toEqual(10.0)
            ->and((float) $stockLevel->average_cost)->toEqual(50.0);
    });

    it('calculates weighted average cost correctly', function () {
        // First receipt: 10 units at 50 each = 500 total
        $this->stockService->receipt($this->product, $this->location, 10, 50.00);

        // Second receipt: 10 units at 100 each = 1000 total
        $this->stockService->receipt($this->product, $this->location, 10, 100.00);

        // Total: 20 units, 1500 value = 75 average
        $stockLevel = $this->stockService->getStockLevel($this->product, $this->location);

        expect((float) $stockLevel->quantity_on_hand)->toEqual(20.0)
            ->and((float) $stockLevel->average_cost)->toEqual(75.0);
    });

    it('throws exception for zero or negative quantity', function () {
        $this->stockService->receipt($this->product, $this->location, 0, 50.00);
    })->throws(InvalidArgumentException::class, 'Receipt quantity must be positive.');

    it('stores reference type and id when provided', function () {
        $transaction = $this->stockService->receipt(
            product: $this->product,
            stockLocation: $this->location,
            quantity: 10,
            unitCost: 50.00,
            referenceType: 'App\\Models\\GoodsReceiptLine',
            referenceId: 123,
            notes: 'Test receipt'
        );

        expect($transaction->reference_type)->toBe('App\\Models\\GoodsReceiptLine')
            ->and($transaction->reference_id)->toBe(123)
            ->and($transaction->notes)->toBe('Test receipt');
    });
});

describe('issue', function () {
    beforeEach(function () {
        // Set up initial stock
        $this->stockService->receipt($this->product, $this->location, 100, 50.00);
    });

    it('creates stock transaction and decreases quantity', function () {
        $transaction = $this->stockService->issue($this->product, $this->location, 30);

        expect($transaction->transaction_type)->toBe('issue')
            ->and((float) $transaction->quantity)->toEqual(-30.0)
            ->and((float) $transaction->quantity_before)->toEqual(100.0)
            ->and((float) $transaction->quantity_after)->toEqual(70.0);

        $stockLevel = $this->stockService->getStockLevel($this->product, $this->location);
        expect((float) $stockLevel->quantity_on_hand)->toEqual(70.0);
    });

    it('uses existing average cost for issued stock', function () {
        $transaction = $this->stockService->issue($this->product, $this->location, 30);

        expect((float) $transaction->unit_cost)->toEqual(50.0)
            ->and((float) $transaction->total_cost)->toEqual(1500.0);
    });

    it('throws exception when insufficient stock', function () {
        $this->stockService->issue($this->product, $this->location, 150);
    })->throws(InvalidArgumentException::class, 'Insufficient available stock');

    it('allows negative stock when setting enabled', function () {
        InventorySettings::create([
            'company_id' => $this->company->id,
            'allow_negative_stock' => true,
        ]);

        // Reload the product to refresh company relationship
        $this->product->refresh();

        $transaction = $this->stockService->issue($this->product, $this->location, 150);

        $stockLevel = $this->stockService->getStockLevel($this->product, $this->location);
        expect((float) $stockLevel->quantity_on_hand)->toEqual(-50.0);
    });

    it('respects reserved quantity when checking availability', function () {
        // Reserve 80 of 100 units
        $this->stockService->reserve($this->product, $this->location, 80, 'App\\Models\\OrderLine', 1);

        // Should fail - only 20 available
        $this->stockService->issue($this->product, $this->location, 30);
    })->throws(InvalidArgumentException::class, 'Insufficient available stock');
});

describe('reserve', function () {
    beforeEach(function () {
        $this->stockService->receipt($this->product, $this->location, 100, 50.00);
    });

    it('creates reservation and increases reserved quantity', function () {
        $reservation = $this->stockService->reserve(
            $this->product,
            $this->location,
            30,
            'App\\Models\\OrderLine',
            1
        );

        expect($reservation)->toBeInstanceOf(StockReservation::class)
            ->and((float) $reservation->quantity)->toEqual(30.0)
            ->and($reservation->status)->toBe('active');

        $stockLevel = $this->stockService->getStockLevel($this->product, $this->location);
        expect((float) $stockLevel->quantity_reserved)->toEqual(30.0)
            ->and((float) $stockLevel->quantity_on_hand)->toEqual(100.0); // Unchanged
    });

    it('throws exception when insufficient available stock', function () {
        $this->stockService->reserve($this->product, $this->location, 150, 'App\\Models\\OrderLine', 1);
    })->throws(InvalidArgumentException::class, 'Insufficient available stock for reservation');
});

describe('releaseReservation', function () {
    beforeEach(function () {
        $this->stockService->receipt($this->product, $this->location, 100, 50.00);
        $this->stockService->reserve($this->product, $this->location, 30, 'App\\Models\\OrderLine', 1);
    });

    it('releases reservation and decreases reserved quantity', function () {
        $this->stockService->releaseReservation(
            $this->product,
            $this->location,
            'App\\Models\\OrderLine',
            1
        );

        $stockLevel = $this->stockService->getStockLevel($this->product, $this->location);
        expect((float) $stockLevel->quantity_reserved)->toEqual(0.0);

        $reservation = StockReservation::where('reference_type', 'App\\Models\\OrderLine')
            ->where('reference_id', 1)
            ->first();
        expect($reservation->status)->toBe('cancelled');
    });

    it('does nothing when reservation not found', function () {
        $this->stockService->releaseReservation(
            $this->product,
            $this->location,
            'App\\Models\\OrderLine',
            999
        );

        $stockLevel = $this->stockService->getStockLevel($this->product, $this->location);
        expect((float) $stockLevel->quantity_reserved)->toEqual(30.0);
    });
});

describe('fulfillReservation', function () {
    beforeEach(function () {
        $this->stockService->receipt($this->product, $this->location, 100, 50.00);
        $this->stockService->reserve($this->product, $this->location, 30, 'App\\Models\\OrderLine', 1);
    });

    it('converts reservation to issue transaction', function () {
        $transaction = $this->stockService->fulfillReservation(
            $this->product,
            $this->location,
            'App\\Models\\OrderLine',
            1
        );

        expect($transaction->transaction_type)->toBe('issue')
            ->and((float) $transaction->quantity)->toEqual(-30.0);

        $stockLevel = $this->stockService->getStockLevel($this->product, $this->location);
        expect((float) $stockLevel->quantity_on_hand)->toEqual(70.0)
            ->and((float) $stockLevel->quantity_reserved)->toEqual(0.0);

        $reservation = StockReservation::where('reference_type', 'App\\Models\\OrderLine')
            ->where('reference_id', 1)
            ->first();
        expect($reservation->status)->toBe('fulfilled');
    });

    it('throws exception when no active reservation found', function () {
        $this->stockService->fulfillReservation(
            $this->product,
            $this->location,
            'App\\Models\\OrderLine',
            999
        );
    })->throws(InvalidArgumentException::class, 'No active reservation found');
});

describe('transfer', function () {
    beforeEach(function () {
        $this->stockService->receipt($this->product, $this->location, 100, 50.00);
        $this->toLocation = StockLocation::factory()->for($this->company)->create();
    });

    it('moves stock between locations', function () {
        $result = $this->stockService->transfer(
            $this->product,
            $this->location,
            $this->toLocation,
            30
        );

        expect($result)->toHaveKeys(['out', 'in'])
            ->and($result['out']->transaction_type)->toBe('transfer_out')
            ->and((float) $result['out']->quantity)->toEqual(-30.0)
            ->and($result['in']->transaction_type)->toBe('transfer_in')
            ->and((float) $result['in']->quantity)->toEqual(30.0);

        $fromLevel = $this->stockService->getStockLevel($this->product, $this->location);
        $toLevel = $this->stockService->getStockLevel($this->product, $this->toLocation);

        expect((float) $fromLevel->quantity_on_hand)->toEqual(70.0)
            ->and((float) $toLevel->quantity_on_hand)->toEqual(30.0)
            ->and((float) $toLevel->average_cost)->toEqual(50.0);
    });

    it('throws exception when transferring to same location', function () {
        $this->stockService->transfer($this->product, $this->location, $this->location, 30);
    })->throws(InvalidArgumentException::class, 'Cannot transfer to the same location');

    it('throws exception when insufficient available stock', function () {
        $this->stockService->reserve($this->product, $this->location, 80, 'App\\Models\\OrderLine', 1);

        $this->stockService->transfer($this->product, $this->location, $this->toLocation, 30);
    })->throws(InvalidArgumentException::class, 'Insufficient available stock for transfer');
});

describe('adjust', function () {
    beforeEach(function () {
        $this->stockService->receipt($this->product, $this->location, 100, 50.00);
    });

    it('increases stock with positive adjustment', function () {
        $transaction = $this->stockService->adjust(
            $this->product,
            $this->location,
            20,
            60.00,
            'Found extra units'
        );

        expect($transaction->transaction_type)->toBe('adjustment_in')
            ->and((float) $transaction->quantity)->toEqual(20.0);

        $stockLevel = $this->stockService->getStockLevel($this->product, $this->location);
        // (100*50 + 20*60) / 120 = 6200/120 = 51.67
        expect((float) $stockLevel->quantity_on_hand)->toEqual(120.0)
            ->and(round((float) $stockLevel->average_cost, 2))->toEqual(51.67);
    });

    it('decreases stock with negative adjustment', function () {
        $transaction = $this->stockService->adjust(
            $this->product,
            $this->location,
            -20,
            null,
            'Damaged units'
        );

        expect($transaction->transaction_type)->toBe('adjustment_out')
            ->and((float) $transaction->quantity)->toEqual(-20.0);

        $stockLevel = $this->stockService->getStockLevel($this->product, $this->location);
        expect((float) $stockLevel->quantity_on_hand)->toEqual(80.0)
            ->and((float) $stockLevel->average_cost)->toEqual(50.0); // Unchanged
    });

    it('throws exception for zero adjustment', function () {
        $this->stockService->adjust($this->product, $this->location, 0);
    })->throws(InvalidArgumentException::class, 'Adjustment quantity cannot be zero');

    it('throws exception when adjustment would result in negative stock', function () {
        $this->stockService->adjust($this->product, $this->location, -150);
    })->throws(InvalidArgumentException::class, 'Adjustment would result in negative stock');
});

describe('helper methods', function () {
    beforeEach(function () {
        $this->location2 = StockLocation::factory()->for($this->company)->create();
        $this->stockService->receipt($this->product, $this->location, 100, 50.00);
        $this->stockService->receipt($this->product, $this->location2, 50, 60.00);
    });

    it('getTotalStock returns sum across all locations', function () {
        $total = $this->stockService->getTotalStock($this->product);
        expect((float) $total)->toEqual(150.0);
    });

    it('getTotalAvailableStock excludes reserved quantity', function () {
        $this->stockService->reserve($this->product, $this->location, 30, 'App\\Models\\OrderLine', 1);

        $available = $this->stockService->getTotalAvailableStock($this->product);
        expect((float) $available)->toEqual(120.0);
    });

    it('getStockValue returns value at specific location', function () {
        $value = $this->stockService->getStockValue($this->product, $this->location);
        expect((float) $value)->toEqual(5000.0); // 100 * 50
    });

    it('getTotalStockValue returns total value across all locations', function () {
        $totalValue = $this->stockService->getTotalStockValue($this->product);
        expect((float) $totalValue)->toEqual(8000.0); // (100*50) + (50*60)
    });
});

describe('product attributes', function () {
    it('product has totalStock attribute', function () {
        $this->stockService->receipt($this->product, $this->location, 100, 50.00);

        $this->product->load('stockLevels');
        expect((float) $this->product->total_stock)->toEqual(100.0);
    });

    it('product has totalAvailable attribute', function () {
        $this->stockService->receipt($this->product, $this->location, 100, 50.00);
        $this->stockService->reserve($this->product, $this->location, 30, 'App\\Models\\OrderLine', 1);

        $this->product->load('stockLevels');
        expect((float) $this->product->total_available)->toEqual(70.0);
    });
});

describe('transaction numbering', function () {
    it('generates unique transaction numbers', function () {
        $tx1 = $this->stockService->receipt($this->product, $this->location, 10, 50.00);
        $tx2 = $this->stockService->receipt($this->product, $this->location, 10, 50.00);

        expect($tx1->transaction_number)->toMatch('/^ST-\d{4}-\d{5}$/')
            ->and($tx2->transaction_number)->toMatch('/^ST-\d{4}-\d{5}$/')
            ->and($tx1->transaction_number)->not->toBe($tx2->transaction_number);
    });
});
