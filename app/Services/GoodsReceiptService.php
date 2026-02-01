<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptLine;
use App\Models\PurchaseOrder;
use App\Models\StockLocation;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GoodsReceiptService
{
    public function __construct(
        protected StockService $stockService,
        protected PurchaseOrderService $purchaseOrderService
    ) {}

    /**
     * Create a goods receipt from a purchase order.
     *
     * @param  array<int, float>  $lineQuantities  Array of [po_line_id => quantity_received]
     */
    public function createFromPurchaseOrder(
        PurchaseOrder $po,
        array $lineQuantities,
        ?StockLocation $stockLocation = null,
        ?string $supplierDeliveryNote = null,
        ?string $notes = null
    ): GoodsReceipt {
        if (! $po->can_receive) {
            throw new InvalidArgumentException('This purchase order cannot receive goods.');
        }

        $location = $stockLocation ?? $po->stockLocation;
        if (! $location) {
            throw new InvalidArgumentException('A stock location must be specified.');
        }

        return DB::transaction(function () use ($po, $lineQuantities, $location, $supplierDeliveryNote, $notes) {
            $receipt = GoodsReceipt::create([
                'company_id' => $po->company_id,
                'purchase_order_id' => $po->id,
                'contact_id' => $po->contact_id,
                'stock_location_id' => $location->id,
                'receipt_date' => now(),
                'supplier_delivery_note' => $supplierDeliveryNote,
                'notes' => $notes,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($lineQuantities as $poLineId => $quantityReceived) {
                if ($quantityReceived <= 0) {
                    continue;
                }

                $poLine = $po->lines()->find($poLineId);
                if (! $poLine) {
                    continue;
                }

                $receipt->lines()->create([
                    'company_id' => $po->company_id,
                    'purchase_order_line_id' => $poLine->id,
                    'product_id' => $poLine->product_id,
                    'description' => $poLine->description,
                    'quantity_ordered' => $poLine->quantity,
                    'quantity_received' => $quantityReceived,
                    'unit_cost' => $poLine->unit_price,
                    'sort_order' => $poLine->sort_order,
                ]);
            }

            if ($receipt->lines()->count() === 0) {
                throw new InvalidArgumentException('No valid quantities provided for goods receipt.');
            }

            return $receipt;
        });
    }

    /**
     * Create a standalone goods receipt (without purchase order).
     */
    public function createStandalone(
        int $companyId,
        int $contactId,
        StockLocation $stockLocation,
        array $lines,
        ?string $supplierDeliveryNote = null,
        ?string $notes = null
    ): GoodsReceipt {
        return DB::transaction(function () use ($companyId, $contactId, $stockLocation, $lines, $supplierDeliveryNote, $notes) {
            $receipt = GoodsReceipt::create([
                'company_id' => $companyId,
                'purchase_order_id' => null,
                'contact_id' => $contactId,
                'stock_location_id' => $stockLocation->id,
                'receipt_date' => now(),
                'supplier_delivery_note' => $supplierDeliveryNote,
                'notes' => $notes,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            $sortOrder = 0;
            foreach ($lines as $lineData) {
                $receipt->lines()->create([
                    'company_id' => $companyId,
                    'purchase_order_line_id' => null,
                    'product_id' => $lineData['product_id'],
                    'description' => $lineData['description'],
                    'quantity_ordered' => 0,
                    'quantity_received' => $lineData['quantity_received'],
                    'unit_cost' => $lineData['unit_cost'],
                    'sort_order' => ++$sortOrder,
                ]);
            }

            return $receipt;
        });
    }

    /**
     * Post a goods receipt (creates stock transactions).
     */
    public function post(GoodsReceipt $receipt): bool
    {
        if ($receipt->status !== 'draft') {
            throw new InvalidArgumentException('Only draft goods receipts can be posted.');
        }

        if ($receipt->lines()->count() === 0) {
            throw new InvalidArgumentException('Cannot post a goods receipt without lines.');
        }

        return DB::transaction(function () use ($receipt) {
            $stockLocation = $receipt->stockLocation;

            foreach ($receipt->lines as $line) {
                $product = $line->product;

                if (! $product || ! $product->is_stocked) {
                    continue;
                }

                // Create stock transaction for each line
                $this->stockService->receipt(
                    product: $product,
                    stockLocation: $stockLocation,
                    quantity: (float) $line->quantity_received,
                    unitCost: (float) $line->unit_cost,
                    referenceType: GoodsReceiptLine::class,
                    referenceId: $line->id,
                    notes: "Varemottak {$receipt->receipt_number}"
                );
            }

            // Update the receipt status
            $receipt->update([
                'status' => 'posted',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            // Update PO received quantities if linked
            if ($receipt->purchase_order_id) {
                $this->purchaseOrderService->updateReceivedQuantities($receipt->purchaseOrder);
            }

            return true;
        });
    }

    /**
     * Reverse a posted goods receipt.
     */
    public function reverse(GoodsReceipt $receipt): bool
    {
        if ($receipt->status !== 'posted') {
            throw new InvalidArgumentException('Only posted goods receipts can be reversed.');
        }

        return DB::transaction(function () use ($receipt) {
            $stockLocation = $receipt->stockLocation;

            foreach ($receipt->lines as $line) {
                $product = $line->product;

                if (! $product || ! $product->is_stocked) {
                    continue;
                }

                // Create negative stock adjustment to reverse
                $this->stockService->adjust(
                    product: $product,
                    stockLocation: $stockLocation,
                    quantity: -((float) $line->quantity_received),
                    unitCost: (float) $line->unit_cost,
                    notes: "Reversering av varemottak {$receipt->receipt_number}"
                );
            }

            // Update status to cancelled
            $receipt->update([
                'status' => 'cancelled',
            ]);

            // Update PO received quantities if linked
            if ($receipt->purchase_order_id) {
                $this->purchaseOrderService->updateReceivedQuantities($receipt->purchaseOrder);
            }

            return true;
        });
    }

    /**
     * Update a draft goods receipt.
     */
    public function update(GoodsReceipt $receipt, array $data): GoodsReceipt
    {
        if ($receipt->status !== 'draft') {
            throw new InvalidArgumentException('Only draft goods receipts can be updated.');
        }

        $receipt->update([
            'receipt_date' => $data['receipt_date'] ?? $receipt->receipt_date,
            'supplier_delivery_note' => $data['supplier_delivery_note'] ?? $receipt->supplier_delivery_note,
            'notes' => $data['notes'] ?? $receipt->notes,
        ]);

        return $receipt->fresh();
    }

    /**
     * Update a line on a draft goods receipt.
     */
    public function updateLine(GoodsReceiptLine $line, float $quantityReceived): GoodsReceiptLine
    {
        $receipt = $line->goodsReceipt;

        if ($receipt->status !== 'draft') {
            throw new InvalidArgumentException('Only draft goods receipt lines can be updated.');
        }

        if ($quantityReceived < 0) {
            throw new InvalidArgumentException('Quantity received cannot be negative.');
        }

        $line->update(['quantity_received' => $quantityReceived]);

        return $line->fresh();
    }

    /**
     * Remove a line from a draft goods receipt.
     */
    public function removeLine(GoodsReceiptLine $line): bool
    {
        $receipt = $line->goodsReceipt;

        if ($receipt->status !== 'draft') {
            throw new InvalidArgumentException('Only draft goods receipt lines can be removed.');
        }

        $line->delete();

        return true;
    }

    /**
     * Delete a draft goods receipt.
     */
    public function delete(GoodsReceipt $receipt): bool
    {
        if ($receipt->status !== 'draft') {
            throw new InvalidArgumentException('Only draft goods receipts can be deleted.');
        }

        return DB::transaction(function () use ($receipt) {
            $receipt->lines()->delete();
            $receipt->delete();

            return true;
        });
    }

    /**
     * Get outstanding quantities for a PO that can still be received.
     *
     * @return array<int, array{line: \App\Models\PurchaseOrderLine, outstanding: float}>
     */
    public function getOutstandingForPo(PurchaseOrder $po): array
    {
        $outstanding = [];

        foreach ($po->lines as $line) {
            $quantityOutstanding = (float) $line->quantity - (float) $line->quantity_received;
            if ($quantityOutstanding > 0) {
                $outstanding[$line->id] = [
                    'line' => $line,
                    'outstanding' => $quantityOutstanding,
                ];
            }
        }

        return $outstanding;
    }

    /**
     * Check if a PO has any outstanding quantities to receive.
     */
    public function hasOutstandingQuantities(PurchaseOrder $po): bool
    {
        return $po->lines()->whereRaw('quantity > quantity_received')->exists();
    }
}
