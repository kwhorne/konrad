<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockCount;
use App\Models\StockCountLine;
use App\Models\StockLevel;
use App\Models\StockLocation;
use Illuminate\Support\Facades\DB;

class StockCountService
{
    public function __construct(
        private StockService $stockService
    ) {}

    /**
     * Create a new stock count for a location with all stocked products.
     */
    public function createCount(
        StockLocation $stockLocation,
        ?string $description = null,
        ?array $productIds = null
    ): StockCount {
        return DB::transaction(function () use ($stockLocation, $description, $productIds) {
            $companyId = auth()->user()->current_company_id;

            $stockCount = StockCount::create([
                'company_id' => $companyId,
                'stock_location_id' => $stockLocation->id,
                'count_date' => now(),
                'description' => $description ?? 'Varetelling '.$stockLocation->name,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // Get products to count
            $query = Product::where('company_id', $companyId)
                ->where('is_stocked', true)
                ->active();

            if ($productIds) {
                $query->whereIn('id', $productIds);
            }

            $products = $query->ordered()->get();
            $sortOrder = 1;

            foreach ($products as $product) {
                // Get current stock level
                $stockLevel = StockLevel::where('company_id', $companyId)
                    ->where('product_id', $product->id)
                    ->where('stock_location_id', $stockLocation->id)
                    ->first();

                $expectedQuantity = $stockLevel?->quantity_on_hand ?? 0;
                $unitCost = $stockLevel?->average_cost ?? $product->cost_price ?? 0;

                StockCountLine::create([
                    'company_id' => $companyId,
                    'stock_count_id' => $stockCount->id,
                    'product_id' => $product->id,
                    'expected_quantity' => $expectedQuantity,
                    'unit_cost' => $unitCost,
                    'expected_value' => $expectedQuantity * $unitCost,
                    'sort_order' => $sortOrder++,
                ]);
            }

            $stockCount->recalculateTotals();

            return $stockCount;
        });
    }

    /**
     * Start a count session (change status to in_progress).
     */
    public function start(StockCount $stockCount): void
    {
        if (! $stockCount->can_start) {
            throw new \InvalidArgumentException('Kan ikke starte denne tellingen.');
        }

        $stockCount->update(['status' => 'in_progress']);
    }

    /**
     * Record a count for a specific line.
     */
    public function recordLineCount(
        StockCountLine $line,
        float $countedQuantity,
        ?string $varianceReason = null
    ): void {
        if (! $line->stockCount->can_edit) {
            throw new \InvalidArgumentException('Kan ikke redigere denne tellingen.');
        }

        $line->recordCount($countedQuantity, $varianceReason);
    }

    /**
     * Complete the count (all lines must be counted).
     */
    public function complete(StockCount $stockCount): void
    {
        if (! $stockCount->can_complete) {
            throw new \InvalidArgumentException('Kan ikke fullføre tellingen. Alle linjer må være talt.');
        }

        $stockCount->update([
            'status' => 'completed',
            'completed_by' => auth()->id(),
            'completed_at' => now(),
        ]);
    }

    /**
     * Post the count - create stock adjustments for all variances.
     */
    public function post(StockCount $stockCount): void
    {
        if (! $stockCount->can_post) {
            throw new \InvalidArgumentException('Kan ikke bokføre denne tellingen.');
        }

        DB::transaction(function () use ($stockCount) {
            foreach ($stockCount->lines as $line) {
                if ($line->variance_quantity != 0) {
                    $this->stockService->adjust(
                        product: $line->product,
                        stockLocation: $stockCount->stockLocation,
                        quantity: $line->variance_quantity,
                        unitCost: $line->unit_cost,
                        notes: sprintf(
                            'Varetelling %s: %s (forventet: %s, talt: %s)',
                            $stockCount->count_number,
                            $line->variance_reason ?? 'Telledifferanse',
                            number_format($line->expected_quantity, 2, ',', ' '),
                            number_format($line->counted_quantity, 2, ',', ' ')
                        )
                    );
                }
            }

            $stockCount->update([
                'status' => 'posted',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);
        });
    }

    /**
     * Cancel a count.
     */
    public function cancel(StockCount $stockCount): void
    {
        if (! $stockCount->can_cancel) {
            throw new \InvalidArgumentException('Kan ikke kansellere denne tellingen.');
        }

        $stockCount->update(['status' => 'cancelled']);
    }

    /**
     * Get summary statistics for a count.
     */
    public function getSummary(StockCount $stockCount): array
    {
        $lines = $stockCount->lines;
        $countedLines = $lines->where('is_counted', true);

        return [
            'total_lines' => $lines->count(),
            'counted_lines' => $countedLines->count(),
            'remaining_lines' => $lines->count() - $countedLines->count(),
            'lines_with_variance' => $countedLines->where('variance_quantity', '!=', 0)->count(),
            'lines_with_shortage' => $countedLines->where('variance_quantity', '<', 0)->count(),
            'lines_with_surplus' => $countedLines->where('variance_quantity', '>', 0)->count(),
            'total_expected_value' => $stockCount->total_expected_value,
            'total_counted_value' => $stockCount->total_counted_value,
            'total_variance_value' => $stockCount->total_variance_value,
            'variance_percentage' => $stockCount->total_expected_value > 0
                ? round(($stockCount->total_variance_value / $stockCount->total_expected_value) * 100, 2)
                : 0,
        ];
    }
}
