<?php

namespace App\Services;

use App\Models\Product;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

class WorkOrderService
{
    /**
     * Save a work order line (create or update).
     *
     * @param  array<string, mixed>  $data
     */
    public function saveLine(WorkOrder $workOrder, array $data, ?int $lineId = null): WorkOrderLine
    {
        $lineData = [
            'work_order_id' => $workOrder->id,
            'line_type' => $data['line_type'],
            'product_id' => $data['line_type'] === 'product' ? ($data['product_id'] ?: null) : null,
            'description' => $data['description'] ?? null,
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'discount_percent' => $data['discount_percent'] ?? 0,
            'performed_at' => $data['line_type'] === 'time' ? ($data['performed_at'] ?: null) : null,
            'performed_by' => $data['line_type'] === 'time' ? ($data['performed_by'] ?: null) : null,
        ];

        if ($lineId) {
            $line = WorkOrderLine::findOrFail($lineId);
            $lineData['sort_order'] = $line->sort_order;
            $line->update($lineData);

            return $line->fresh();
        }

        $lineData['sort_order'] = WorkOrderLine::where('work_order_id', $workOrder->id)->count();

        return WorkOrderLine::create($lineData);
    }

    /**
     * Delete a work order line.
     */
    public function deleteLine(WorkOrderLine $line): void
    {
        $line->delete();
    }

    /**
     * Populate line data from a product.
     *
     * @return array<string, mixed>
     */
    public function populateFromProduct(Product $product): array
    {
        return [
            'description' => $product->name,
            'unit_price' => $product->price,
        ];
    }

    /**
     * Get default values for a time entry.
     *
     * @return array<string, mixed>
     */
    public function getTimeEntryDefaults(?int $userId = null): array
    {
        return [
            'line_type' => 'time',
            'performed_at' => date('Y-m-d'),
            'performed_by' => $userId ?? auth()->id(),
        ];
    }

    /**
     * Get default values for a product entry.
     *
     * @return array<string, mixed>
     */
    public function getProductEntryDefaults(): array
    {
        return [
            'line_type' => 'product',
            'performed_at' => null,
            'performed_by' => null,
        ];
    }

    /**
     * Calculate total hours from work order lines.
     */
    public function calculateTotalHours(WorkOrder $workOrder): float
    {
        return $workOrder->lines
            ->where('line_type', 'time')
            ->sum('quantity');
    }

    /**
     * Calculate total amount from work order lines.
     */
    public function calculateTotalAmount(WorkOrder $workOrder): float
    {
        return $workOrder->lines->sum(fn ($line) => $line->line_total);
    }

    /**
     * Calculate budget variance (positive = under budget, negative = over budget).
     */
    public function calculateBudgetVariance(WorkOrder $workOrder): ?float
    {
        if ($workOrder->budget === null) {
            return null;
        }

        return (float) $workOrder->budget - $this->calculateTotalAmount($workOrder);
    }

    /**
     * Check if work order is overdue.
     */
    public function isOverdue(WorkOrder $workOrder): bool
    {
        if (! $workOrder->due_date || $workOrder->completed_at) {
            return false;
        }

        return $workOrder->due_date->isPast();
    }

    /**
     * Mark work order as completed.
     */
    public function markAsCompleted(WorkOrder $workOrder): WorkOrder
    {
        $workOrder->update([
            'completed_at' => now(),
        ]);

        return $workOrder->fresh();
    }

    /**
     * Reopen a completed work order.
     */
    public function reopen(WorkOrder $workOrder): WorkOrder
    {
        $workOrder->update([
            'completed_at' => null,
        ]);

        return $workOrder->fresh();
    }

    /**
     * Assign work order to a user.
     */
    public function assignTo(WorkOrder $workOrder, ?int $userId): WorkOrder
    {
        $workOrder->update([
            'assigned_to' => $userId,
        ]);

        return $workOrder->fresh();
    }

    /**
     * Get time entries summary grouped by user.
     *
     * @return array<int, array{user_id: int|null, user_name: string, total_hours: float, total_amount: float}>
     */
    public function getTimeEntriesByUser(WorkOrder $workOrder): array
    {
        return $workOrder->lines
            ->where('line_type', 'time')
            ->groupBy('performed_by')
            ->map(function ($lines, $userId) {
                $firstLine = $lines->first();

                return [
                    'user_id' => $userId,
                    'user_name' => $firstLine->performedByUser?->name ?? 'Ukjent',
                    'total_hours' => $lines->sum('quantity'),
                    'total_amount' => $lines->sum(fn ($line) => $line->line_total),
                ];
            })
            ->values()
            ->toArray();
    }
}
