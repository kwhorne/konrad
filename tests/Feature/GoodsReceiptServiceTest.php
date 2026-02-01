<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\StockLevel;
use App\Models\StockLocation;
use App\Models\User;
use App\Services\GoodsReceiptService;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->company = Company::factory()->create();
    $this->supplier = Contact::factory()->supplier()->for($this->company)->create();
    $this->location = StockLocation::factory()->for($this->company)->create();
    $this->product = Product::factory()->for($this->company)->create([
        'is_stocked' => true,
        'cost_price' => 100,
    ]);

    $this->goodsReceiptService = app(GoodsReceiptService::class);
    $this->purchaseOrderService = app(PurchaseOrderService::class);
});

describe('createFromPurchaseOrder', function () {
    beforeEach(function () {
        $this->po = PurchaseOrder::factory()
            ->for($this->company)
            ->for($this->supplier, 'contact')
            ->for($this->location, 'stockLocation')
            ->sent()
            ->create();

        $this->poLine = PurchaseOrderLine::factory()
            ->for($this->po)
            ->withProduct($this->product)
            ->create([
                'company_id' => $this->company->id,
                'quantity' => 100,
                'unit_price' => 50,
            ]);
    });

    it('creates a goods receipt from a purchase order', function () {
        $receipt = $this->goodsReceiptService->createFromPurchaseOrder(
            po: $this->po,
            lineQuantities: [$this->poLine->id => 50],
            supplierDeliveryNote: 'DN-001',
            notes: 'Test receipt'
        );

        expect($receipt)->toBeInstanceOf(GoodsReceipt::class)
            ->and($receipt->purchase_order_id)->toBe($this->po->id)
            ->and($receipt->contact_id)->toBe($this->supplier->id)
            ->and($receipt->stock_location_id)->toBe($this->location->id)
            ->and($receipt->status)->toBe('draft')
            ->and($receipt->supplier_delivery_note)->toBe('DN-001')
            ->and($receipt->lines)->toHaveCount(1);

        $line = $receipt->lines->first();
        expect($line->purchase_order_line_id)->toBe($this->poLine->id)
            ->and((float) $line->quantity_received)->toEqual(50.0);
    });

    it('throws exception when PO cannot receive goods', function () {
        $draftPo = PurchaseOrder::factory()
            ->for($this->company)
            ->for($this->supplier, 'contact')
            ->for($this->location, 'stockLocation')
            ->draft()
            ->create();

        $this->goodsReceiptService->createFromPurchaseOrder($draftPo, []);
    })->throws(InvalidArgumentException::class, 'This purchase order cannot receive goods');

    it('throws exception when no quantities provided', function () {
        $this->goodsReceiptService->createFromPurchaseOrder($this->po, []);
    })->throws(InvalidArgumentException::class, 'No valid quantities provided');
});

describe('post', function () {
    beforeEach(function () {
        $this->po = PurchaseOrder::factory()
            ->for($this->company)
            ->for($this->supplier, 'contact')
            ->for($this->location, 'stockLocation')
            ->sent()
            ->create();

        $this->poLine = PurchaseOrderLine::factory()
            ->for($this->po)
            ->withProduct($this->product)
            ->create([
                'company_id' => $this->company->id,
                'quantity' => 100,
                'unit_price' => 50,
            ]);

        $this->receipt = $this->goodsReceiptService->createFromPurchaseOrder(
            po: $this->po,
            lineQuantities: [$this->poLine->id => 50],
        );
    });

    it('posts a goods receipt and creates stock transactions', function () {
        $result = $this->goodsReceiptService->post($this->receipt);

        expect($result)->toBeTrue();

        $this->receipt->refresh();
        expect($this->receipt->status)->toBe('posted')
            ->and($this->receipt->posted_at)->not->toBeNull()
            ->and($this->receipt->posted_by)->toBe($this->user->id);

        // Check stock was created
        $stockLevel = StockLevel::where('product_id', $this->product->id)
            ->where('stock_location_id', $this->location->id)
            ->first();

        expect($stockLevel)->not->toBeNull()
            ->and((float) $stockLevel->quantity_on_hand)->toEqual(50.0)
            ->and((float) $stockLevel->average_cost)->toEqual(50.0);
    });

    it('updates PO received quantities after posting', function () {
        $this->goodsReceiptService->post($this->receipt);

        $this->poLine->refresh();
        expect((float) $this->poLine->quantity_received)->toEqual(50.0);
    });

    it('throws exception when posting already posted receipt', function () {
        $this->goodsReceiptService->post($this->receipt);
        $this->goodsReceiptService->post($this->receipt);
    })->throws(InvalidArgumentException::class, 'Only draft goods receipts can be posted');
});

describe('reverse', function () {
    beforeEach(function () {
        $this->po = PurchaseOrder::factory()
            ->for($this->company)
            ->for($this->supplier, 'contact')
            ->for($this->location, 'stockLocation')
            ->sent()
            ->create();

        $this->poLine = PurchaseOrderLine::factory()
            ->for($this->po)
            ->withProduct($this->product)
            ->create([
                'company_id' => $this->company->id,
                'quantity' => 100,
                'unit_price' => 50,
            ]);

        $this->receipt = $this->goodsReceiptService->createFromPurchaseOrder(
            po: $this->po,
            lineQuantities: [$this->poLine->id => 50],
        );
        $this->goodsReceiptService->post($this->receipt);
    });

    it('reverses a posted goods receipt', function () {
        $result = $this->goodsReceiptService->reverse($this->receipt);

        expect($result)->toBeTrue();

        $this->receipt->refresh();
        expect($this->receipt->status)->toBe('cancelled');

        // Check stock was reversed
        $stockLevel = StockLevel::where('product_id', $this->product->id)
            ->where('stock_location_id', $this->location->id)
            ->first();

        expect((float) $stockLevel->quantity_on_hand)->toEqual(0.0);
    });

    it('updates PO received quantities after reversal', function () {
        $this->goodsReceiptService->reverse($this->receipt);

        $this->poLine->refresh();
        expect((float) $this->poLine->quantity_received)->toEqual(0.0);
    });
});

describe('standalone receipt', function () {
    it('creates a standalone goods receipt without PO', function () {
        $receipt = $this->goodsReceiptService->createStandalone(
            companyId: $this->company->id,
            contactId: $this->supplier->id,
            stockLocation: $this->location,
            lines: [
                [
                    'product_id' => $this->product->id,
                    'description' => $this->product->name,
                    'quantity_received' => 25,
                    'unit_cost' => 75,
                ],
            ],
            supplierDeliveryNote: 'DN-002'
        );

        expect($receipt->purchase_order_id)->toBeNull()
            ->and($receipt->lines)->toHaveCount(1)
            ->and((float) $receipt->lines->first()->quantity_received)->toEqual(25.0);
    });

    it('posts standalone receipt and creates stock', function () {
        $receipt = $this->goodsReceiptService->createStandalone(
            companyId: $this->company->id,
            contactId: $this->supplier->id,
            stockLocation: $this->location,
            lines: [
                [
                    'product_id' => $this->product->id,
                    'description' => $this->product->name,
                    'quantity_received' => 25,
                    'unit_cost' => 75,
                ],
            ]
        );

        $this->goodsReceiptService->post($receipt);

        $stockLevel = StockLevel::where('product_id', $this->product->id)
            ->where('stock_location_id', $this->location->id)
            ->first();

        expect((float) $stockLevel->quantity_on_hand)->toEqual(25.0)
            ->and((float) $stockLevel->average_cost)->toEqual(75.0);
    });
});

describe('delete', function () {
    it('deletes a draft goods receipt', function () {
        $receipt = $this->goodsReceiptService->createStandalone(
            companyId: $this->company->id,
            contactId: $this->supplier->id,
            stockLocation: $this->location,
            lines: [
                [
                    'product_id' => $this->product->id,
                    'description' => $this->product->name,
                    'quantity_received' => 25,
                    'unit_cost' => 75,
                ],
            ]
        );

        $receiptId = $receipt->id;
        $this->goodsReceiptService->delete($receipt);

        expect(GoodsReceipt::find($receiptId))->toBeNull();
    });

    it('throws exception when deleting posted receipt', function () {
        $receipt = $this->goodsReceiptService->createStandalone(
            companyId: $this->company->id,
            contactId: $this->supplier->id,
            stockLocation: $this->location,
            lines: [
                [
                    'product_id' => $this->product->id,
                    'description' => $this->product->name,
                    'quantity_received' => 25,
                    'unit_cost' => 75,
                ],
            ]
        );

        $this->goodsReceiptService->post($receipt);
        $this->goodsReceiptService->delete($receipt);
    })->throws(InvalidArgumentException::class, 'Only draft goods receipts can be deleted');
});
