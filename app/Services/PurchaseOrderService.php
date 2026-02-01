<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseOrderService
{
    /**
     * Submit a purchase order for approval.
     */
    public function submitForApproval(PurchaseOrder $po): bool
    {
        if ($po->status !== 'draft') {
            throw new InvalidArgumentException('Only draft purchase orders can be submitted for approval.');
        }

        if ($po->lines()->count() === 0) {
            throw new InvalidArgumentException('Cannot submit a purchase order without lines.');
        }

        $po->update(['status' => 'pending_approval']);

        return true;
    }

    /**
     * Approve a purchase order.
     */
    public function approve(PurchaseOrder $po): bool
    {
        if (! in_array($po->status, ['pending_approval', 'draft'])) {
            throw new InvalidArgumentException('Only draft or pending approval purchase orders can be approved.');
        }

        if ($po->lines()->count() === 0) {
            throw new InvalidArgumentException('Cannot approve a purchase order without lines.');
        }

        $po->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Reject a purchase order.
     */
    public function reject(PurchaseOrder $po, ?string $reason = null): bool
    {
        if ($po->status !== 'pending_approval') {
            throw new InvalidArgumentException('Only pending approval purchase orders can be rejected.');
        }

        $notes = $po->internal_notes;
        if ($reason) {
            $notes = ($notes ? $notes."\n\n" : '').'Avvist: '.$reason;
        }

        $po->update([
            'status' => 'draft',
            'internal_notes' => $notes,
        ]);

        return true;
    }

    /**
     * Mark a purchase order as sent to supplier.
     */
    public function markAsSent(PurchaseOrder $po): bool
    {
        if (! in_array($po->status, ['approved', 'pending_approval'])) {
            throw new InvalidArgumentException('Only approved or pending approval purchase orders can be marked as sent.');
        }

        // If pending, auto-approve when sending
        $updates = [
            'status' => 'sent',
            'sent_at' => now(),
        ];

        if ($po->status === 'pending_approval') {
            $updates['approved_by'] = auth()->id();
            $updates['approved_at'] = now();
        }

        $po->update($updates);

        return true;
    }

    /**
     * Cancel a purchase order.
     */
    public function cancel(PurchaseOrder $po): bool
    {
        if (in_array($po->status, ['received', 'cancelled'])) {
            throw new InvalidArgumentException('Cannot cancel a received or already cancelled purchase order.');
        }

        // Check if any goods have been received
        $hasReceipts = $po->lines()->where('quantity_received', '>', 0)->exists();
        if ($hasReceipts) {
            throw new InvalidArgumentException('Cannot cancel a purchase order with received goods. Consider partial cancellation instead.');
        }

        $po->update(['status' => 'cancelled']);

        return true;
    }

    /**
     * Update received quantities on PO lines from goods receipts.
     */
    public function updateReceivedQuantities(PurchaseOrder $po): void
    {
        DB::transaction(function () use ($po) {
            foreach ($po->lines as $line) {
                $totalReceived = $line->goodsReceiptLines()
                    ->whereHas('goodsReceipt', function ($q) {
                        $q->where('status', 'posted');
                    })
                    ->sum('quantity_received');

                $line->update(['quantity_received' => $totalReceived]);
            }

            $po->updateReceiptStatus();
        });
    }

    /**
     * Add a line to the purchase order.
     */
    public function addLine(
        PurchaseOrder $po,
        array $data
    ): PurchaseOrderLine {
        if (! $po->can_edit) {
            throw new InvalidArgumentException('Cannot add lines to this purchase order.');
        }

        $maxSortOrder = $po->lines()->max('sort_order') ?? 0;

        $line = $po->lines()->create([
            'company_id' => $po->company_id,
            'product_id' => $data['product_id'],
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'] ?? 'stk',
            'unit_price' => $data['unit_price'],
            'discount_percent' => $data['discount_percent'] ?? 0,
            'vat_rate_id' => $data['vat_rate_id'] ?? null,
            'vat_percent' => $data['vat_percent'] ?? 25,
            'sort_order' => $maxSortOrder + 1,
        ]);

        $po->recalculateTotals();

        return $line;
    }

    /**
     * Update a line on the purchase order.
     */
    public function updateLine(PurchaseOrderLine $line, array $data): PurchaseOrderLine
    {
        $po = $line->purchaseOrder;

        if (! $po->can_edit) {
            throw new InvalidArgumentException('Cannot update lines on this purchase order.');
        }

        $line->update($data);
        $po->recalculateTotals();

        return $line->fresh();
    }

    /**
     * Remove a line from the purchase order.
     */
    public function removeLine(PurchaseOrderLine $line): bool
    {
        $po = $line->purchaseOrder;

        if (! $po->can_edit) {
            throw new InvalidArgumentException('Cannot remove lines from this purchase order.');
        }

        if ($line->quantity_received > 0) {
            throw new InvalidArgumentException('Cannot remove a line that has received goods.');
        }

        $line->delete();
        $po->recalculateTotals();

        return true;
    }

    /**
     * Create a new purchase order.
     */
    public function create(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $po = PurchaseOrder::create([
                'company_id' => $data['company_id'],
                'contact_id' => $data['contact_id'],
                'stock_location_id' => $data['stock_location_id'] ?? null,
                'status' => 'draft',
                'order_date' => $data['order_date'] ?? now(),
                'expected_date' => $data['expected_date'] ?? null,
                'supplier_reference' => $data['supplier_reference'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            if (! empty($data['lines'])) {
                foreach ($data['lines'] as $lineData) {
                    $this->addLine($po, $lineData);
                }
            }

            return $po;
        });
    }

    /**
     * Update a purchase order.
     */
    public function update(PurchaseOrder $po, array $data): PurchaseOrder
    {
        if (! $po->can_edit) {
            throw new InvalidArgumentException('Cannot update this purchase order.');
        }

        $po->update([
            'contact_id' => $data['contact_id'] ?? $po->contact_id,
            'stock_location_id' => $data['stock_location_id'] ?? $po->stock_location_id,
            'order_date' => $data['order_date'] ?? $po->order_date,
            'expected_date' => $data['expected_date'] ?? $po->expected_date,
            'supplier_reference' => $data['supplier_reference'] ?? $po->supplier_reference,
            'shipping_address' => $data['shipping_address'] ?? $po->shipping_address,
            'notes' => $data['notes'] ?? $po->notes,
            'internal_notes' => $data['internal_notes'] ?? $po->internal_notes,
        ]);

        return $po->fresh();
    }

    /**
     * Duplicate a purchase order.
     */
    public function duplicate(PurchaseOrder $po): PurchaseOrder
    {
        return DB::transaction(function () use ($po) {
            $newPo = PurchaseOrder::create([
                'company_id' => $po->company_id,
                'contact_id' => $po->contact_id,
                'stock_location_id' => $po->stock_location_id,
                'status' => 'draft',
                'order_date' => now(),
                'expected_date' => null,
                'supplier_reference' => null,
                'shipping_address' => $po->shipping_address,
                'notes' => $po->notes,
                'internal_notes' => null,
                'created_by' => auth()->id(),
            ]);

            foreach ($po->lines as $line) {
                $newPo->lines()->create([
                    'company_id' => $po->company_id,
                    'product_id' => $line->product_id,
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit' => $line->unit,
                    'unit_price' => $line->unit_price,
                    'discount_percent' => $line->discount_percent,
                    'vat_rate_id' => $line->vat_rate_id,
                    'vat_percent' => $line->vat_percent,
                    'sort_order' => $line->sort_order,
                ]);
            }

            $newPo->recalculateTotals();

            return $newPo;
        });
    }

    /**
     * Get outstanding lines (not fully received).
     */
    public function getOutstandingLines(PurchaseOrder $po): \Illuminate\Support\Collection
    {
        return $po->lines->filter(function ($line) {
            return $line->quantity_received < $line->quantity;
        });
    }
}
