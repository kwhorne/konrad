<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptLine;
use App\Models\InventorySettings;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\StockLocation;
use App\Services\GoodsReceiptService;
use Illuminate\Database\Seeder;

class InventoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 2; // Gets Elektro AS

        // Check if already seeded
        if (PurchaseOrder::where('company_id', $companyId)->exists()) {
            $this->command->info('Inventory demo data already exists, skipping.');

            return;
        }

        // Create suppliers
        $suppliers = $this->createSuppliers($companyId);

        // Create stock locations
        $locations = $this->createStockLocations($companyId);

        // Mark products as stocked and update cost prices
        $stockedProducts = $this->markProductsAsStocked($companyId);

        // Create inventory settings
        $this->createInventorySettings($companyId, $locations['hovedlager']);

        // Create purchase orders
        $purchaseOrders = $this->createPurchaseOrders($companyId, $suppliers, $locations['hovedlager'], $stockedProducts);

        // Create goods receipts and post some
        $this->createGoodsReceipts($companyId, $purchaseOrders, $locations['hovedlager']);

        $this->command->info('Inventory demo data created successfully!');
    }

    private function createSuppliers(int $companyId): array
    {
        $suppliers = [];

        $suppliers['elektrogrossisten'] = Contact::firstOrCreate(
            ['company_id' => $companyId, 'organization_number' => '912345678'],
            [
                'type' => 'supplier',
                'company_name' => 'Elektrogrossisten AS',
                'address' => 'Industriveien 15',
                'postal_code' => '0580',
                'city' => 'Oslo',
                'email' => 'ordre@elektrogrossisten.no',
                'phone' => '22 33 44 55',
                'is_active' => true,
                'created_by' => 1,
            ]
        );

        $suppliers['elko'] = Contact::firstOrCreate(
            ['company_id' => $companyId, 'organization_number' => '923456789'],
            [
                'type' => 'supplier',
                'company_name' => 'ELKO Norge AS',
                'address' => 'Elkoparken 1',
                'postal_code' => '1471',
                'city' => 'Lorenskog',
                'email' => 'ordre@elko.no',
                'phone' => '67 97 00 00',
                'is_active' => true,
                'created_by' => 1,
            ]
        );

        $suppliers['schneider'] = Contact::firstOrCreate(
            ['company_id' => $companyId, 'organization_number' => '934567890'],
            [
                'type' => 'supplier',
                'company_name' => 'Schneider Electric Norge',
                'address' => 'Karihaugveien 89',
                'postal_code' => '1086',
                'city' => 'Oslo',
                'email' => 'no-support@se.com',
                'phone' => '22 07 65 00',
                'is_active' => true,
                'created_by' => 1,
            ]
        );

        return $suppliers;
    }

    private function createStockLocations(int $companyId): array
    {
        $locations = [];

        // Main warehouse
        $locations['hovedlager'] = StockLocation::firstOrCreate(
            ['company_id' => $companyId, 'code' => 'HL'],
            [
                'name' => 'Hovedlager',
                'description' => 'Hovedlager pa Skoyen',
                'address' => 'Hoffsveien 10, 0275 Oslo',
                'location_type' => 'warehouse',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        // Zones within warehouse
        $locations['elektro'] = StockLocation::firstOrCreate(
            ['company_id' => $companyId, 'code' => 'HL-E'],
            [
                'parent_id' => $locations['hovedlager']->id,
                'name' => 'Elektro-sone',
                'description' => 'Elektrisk materiell',
                'location_type' => 'zone',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $locations['belysning'] = StockLocation::firstOrCreate(
            ['company_id' => $companyId, 'code' => 'HL-B'],
            [
                'parent_id' => $locations['hovedlager']->id,
                'name' => 'Belysning',
                'description' => 'Lamper og lyskilder',
                'location_type' => 'zone',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // Service van
        $locations['bil1'] = StockLocation::firstOrCreate(
            ['company_id' => $companyId, 'code' => 'BIL-1'],
            [
                'name' => 'Servicebil 1',
                'description' => 'Olas servicebil',
                'location_type' => 'warehouse',
                'is_active' => true,
                'sort_order' => 10,
            ]
        );

        return $locations;
    }

    private function markProductsAsStocked(int $companyId): array
    {
        // Mark material products as stocked
        $stockedProducts = Product::where('company_id', $companyId)
            ->where('sku', 'like', 'MAT-%')
            ->get();

        foreach ($stockedProducts as $product) {
            $product->update([
                'is_stocked' => true,
                'reorder_point' => match ($product->sku) {
                    'MAT-STIKK-D' => 20,
                    'MAT-BRYTER-E' => 15,
                    'MAT-DOWN-10W' => 10,
                    'MAT-KABEL-3X25' => 100,
                    'MAT-SIKR-12' => 2,
                    default => 10,
                },
                'reorder_quantity' => match ($product->sku) {
                    'MAT-STIKK-D' => 50,
                    'MAT-BRYTER-E' => 30,
                    'MAT-DOWN-10W' => 20,
                    'MAT-KABEL-3X25' => 200,
                    'MAT-SIKR-12' => 5,
                    default => 20,
                },
            ]);
        }

        return $stockedProducts->keyBy('sku')->toArray();
    }

    private function createInventorySettings(int $companyId, StockLocation $defaultLocation): void
    {
        InventorySettings::firstOrCreate(
            ['company_id' => $companyId],
            [
                'default_stock_location_id' => $defaultLocation->id,
                'auto_reserve_on_order' => true,
                'allow_negative_stock' => false,
            ]
        );
    }

    private function createPurchaseOrders(int $companyId, array $suppliers, StockLocation $location, array $products): array
    {
        $orders = [];

        // PO 1 - Received order
        $po1 = PurchaseOrder::create([
            'company_id' => $companyId,
            'contact_id' => $suppliers['elektrogrossisten']->id,
            'stock_location_id' => $location->id,
            'status' => 'received',
            'order_date' => now()->subDays(14),
            'expected_date' => now()->subDays(7),
            'supplier_reference' => 'EG-2026-1234',
            'notes' => 'Forste bestilling av basisvarer',
            'created_by' => 1,
        ]);

        $this->addPurchaseOrderLines($po1, [
            ['sku' => 'MAT-STIKK-D', 'qty' => 50, 'price' => 125.00, 'received' => 50],
            ['sku' => 'MAT-BRYTER-E', 'qty' => 30, 'price' => 65.00, 'received' => 30],
            ['sku' => 'MAT-DOWN-10W', 'qty' => 20, 'price' => 195.00, 'received' => 20],
        ], $products);
        $orders['received'] = $po1;

        // PO 2 - Partially received
        $po2 = PurchaseOrder::create([
            'company_id' => $companyId,
            'contact_id' => $suppliers['elko']->id,
            'stock_location_id' => $location->id,
            'status' => 'partially_received',
            'order_date' => now()->subDays(7),
            'expected_date' => now()->addDays(3),
            'supplier_reference' => 'ELKO-56789',
            'notes' => 'Storbestilling kabel',
            'created_by' => 1,
        ]);

        $this->addPurchaseOrderLines($po2, [
            ['sku' => 'MAT-KABEL-3X25', 'qty' => 500, 'price' => 18.50, 'received' => 200],
            ['sku' => 'MAT-SIKR-12', 'qty' => 10, 'price' => 1650.00, 'received' => 5],
        ], $products);
        $orders['partial'] = $po2;

        // PO 3 - Sent, awaiting delivery
        $po3 = PurchaseOrder::create([
            'company_id' => $companyId,
            'contact_id' => $suppliers['schneider']->id,
            'stock_location_id' => $location->id,
            'status' => 'sent',
            'order_date' => now()->subDays(2),
            'expected_date' => now()->addDays(5),
            'supplier_reference' => null,
            'notes' => 'Sikringsskap til Boligbyggelaget-prosjektet',
            'sent_at' => now()->subDays(1),
            'created_by' => 1,
        ]);

        $this->addPurchaseOrderLines($po3, [
            ['sku' => 'MAT-SIKR-12', 'qty' => 3, 'price' => 1750.00, 'received' => 0],
            ['sku' => 'MAT-BRYTER-E', 'qty' => 50, 'price' => 62.00, 'received' => 0],
        ], $products);
        $orders['sent'] = $po3;

        // PO 4 - Draft
        $po4 = PurchaseOrder::create([
            'company_id' => $companyId,
            'contact_id' => $suppliers['elektrogrossisten']->id,
            'stock_location_id' => $location->id,
            'status' => 'draft',
            'order_date' => now(),
            'expected_date' => now()->addDays(10),
            'created_by' => 1,
        ]);

        $this->addPurchaseOrderLines($po4, [
            ['sku' => 'MAT-STIKK-D', 'qty' => 100, 'price' => 120.00, 'received' => 0],
            ['sku' => 'MAT-DOWN-10W', 'qty' => 50, 'price' => 190.00, 'received' => 0],
        ], $products);
        $orders['draft'] = $po4;

        return $orders;
    }

    private function addPurchaseOrderLines(PurchaseOrder $po, array $lineData, array $products): void
    {
        $sortOrder = 1;
        foreach ($lineData as $data) {
            $product = Product::where('sku', $data['sku'])->first();
            if (! $product) {
                continue;
            }

            PurchaseOrderLine::create([
                'company_id' => $po->company_id,
                'purchase_order_id' => $po->id,
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => $data['qty'],
                'unit' => 'stk',
                'unit_price' => $data['price'],
                'discount_percent' => 0,
                'vat_rate_id' => null,
                'vat_percent' => 25,
                'quantity_received' => $data['received'],
                'sort_order' => $sortOrder++,
            ]);
        }

        $po->recalculateTotals();
    }

    private function createGoodsReceipts(int $companyId, array $purchaseOrders, StockLocation $location): void
    {
        $service = app(GoodsReceiptService::class);

        // Receipt 1 - Posted (for PO 1)
        $receipt1 = GoodsReceipt::create([
            'company_id' => $companyId,
            'purchase_order_id' => $purchaseOrders['received']->id,
            'contact_id' => $purchaseOrders['received']->contact_id,
            'stock_location_id' => $location->id,
            'receipt_date' => now()->subDays(7),
            'supplier_delivery_note' => 'PKS-12345',
            'notes' => 'Komplett leveranse',
            'status' => 'draft',
            'created_by' => 1,
        ]);

        foreach ($purchaseOrders['received']->lines as $line) {
            GoodsReceiptLine::create([
                'company_id' => $companyId,
                'goods_receipt_id' => $receipt1->id,
                'purchase_order_line_id' => $line->id,
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity_ordered' => $line->quantity,
                'quantity_received' => $line->quantity,
                'unit_cost' => $line->unit_price,
                'sort_order' => $line->sort_order,
            ]);
        }

        // Post the receipt to create stock
        $service->post($receipt1);

        // Receipt 2 - Posted (partial for PO 2)
        $receipt2 = GoodsReceipt::create([
            'company_id' => $companyId,
            'purchase_order_id' => $purchaseOrders['partial']->id,
            'contact_id' => $purchaseOrders['partial']->contact_id,
            'stock_location_id' => $location->id,
            'receipt_date' => now()->subDays(3),
            'supplier_delivery_note' => 'ELKO-DL-789',
            'notes' => 'Delleveranse 1 av 2',
            'status' => 'draft',
            'created_by' => 1,
        ]);

        $sortOrder = 1;
        foreach ($purchaseOrders['partial']->lines as $line) {
            GoodsReceiptLine::create([
                'company_id' => $companyId,
                'goods_receipt_id' => $receipt2->id,
                'purchase_order_line_id' => $line->id,
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity_ordered' => $line->quantity,
                'quantity_received' => $line->quantity_received,
                'unit_cost' => $line->unit_price,
                'sort_order' => $sortOrder++,
            ]);
        }

        $service->post($receipt2);

        // Receipt 3 - Draft (standalone, not linked to PO)
        $receipt3 = GoodsReceipt::create([
            'company_id' => $companyId,
            'purchase_order_id' => null,
            'contact_id' => Contact::where('company_id', $companyId)->where('type', 'supplier')->first()->id,
            'stock_location_id' => $location->id,
            'receipt_date' => now(),
            'supplier_delivery_note' => 'RETUR-001',
            'notes' => 'Retur fra kunde - ubrukte varer',
            'status' => 'draft',
            'created_by' => 1,
        ]);

        $product = Product::where('sku', 'MAT-STIKK-D')->first();
        GoodsReceiptLine::create([
            'company_id' => $companyId,
            'goods_receipt_id' => $receipt3->id,
            'product_id' => $product->id,
            'description' => $product->name.' (retur)',
            'quantity_ordered' => 0,
            'quantity_received' => 5,
            'unit_cost' => 125.00,
            'sort_order' => 1,
        ]);
    }
}
