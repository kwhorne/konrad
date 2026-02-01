<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockLocation;
use App\Models\StockReservation;
use App\Models\StockTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    /**
     * Receive stock into inventory (increases quantity, updates weighted average cost).
     */
    public function receipt(
        Product $product,
        StockLocation $stockLocation,
        float $quantity,
        float $unitCost,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): StockTransaction {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Receipt quantity must be positive.');
        }

        return DB::transaction(function () use ($product, $stockLocation, $quantity, $unitCost, $referenceType, $referenceId, $notes) {
            $stockLevel = $this->getOrCreateStockLevel($product, $stockLocation);
            $quantityBefore = $stockLevel->quantity_on_hand;

            // Calculate new weighted average cost
            $existingValue = $stockLevel->quantity_on_hand * ($stockLevel->average_cost ?? 0);
            $newValue = $quantity * $unitCost;
            $newTotalQuantity = $stockLevel->quantity_on_hand + $quantity;

            $newAverageCost = $newTotalQuantity > 0
                ? ($existingValue + $newValue) / $newTotalQuantity
                : $unitCost;

            // Update stock level
            $stockLevel->update([
                'quantity_on_hand' => $newTotalQuantity,
                'average_cost' => $newAverageCost,
            ]);

            // Create transaction record
            return StockTransaction::create([
                'product_id' => $product->id,
                'stock_location_id' => $stockLocation->id,
                'transaction_type' => 'receipt',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $newTotalQuantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'transaction_date' => now(),
                'is_posted' => true,
                'posted_at' => now(),
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Issue stock from inventory (decreases quantity, uses existing average cost).
     */
    public function issue(
        Product $product,
        StockLocation $stockLocation,
        float $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): StockTransaction {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Issue quantity must be positive.');
        }

        return DB::transaction(function () use ($product, $stockLocation, $quantity, $referenceType, $referenceId, $notes) {
            $stockLevel = $this->getStockLevel($product, $stockLocation);

            if (! $stockLevel) {
                throw new InvalidArgumentException('No stock level found for this product/location.');
            }

            $availableQuantity = $stockLevel->quantity_on_hand - $stockLevel->quantity_reserved;

            // Check if we allow negative stock
            $inventorySettings = $product->company->inventorySettings;
            $allowNegative = $inventorySettings?->allow_negative_stock ?? false;

            if (! $allowNegative && $quantity > $availableQuantity) {
                throw new InvalidArgumentException(
                    "Insufficient available stock. Available: {$availableQuantity}, Requested: {$quantity}"
                );
            }

            $quantityBefore = $stockLevel->quantity_on_hand;
            $unitCost = $stockLevel->average_cost ?? 0;

            // Update stock level
            $stockLevel->update([
                'quantity_on_hand' => $stockLevel->quantity_on_hand - $quantity,
            ]);

            // Create transaction record
            return StockTransaction::create([
                'product_id' => $product->id,
                'stock_location_id' => $stockLocation->id,
                'transaction_type' => 'issue',
                'quantity' => -$quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $stockLevel->quantity_on_hand,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'transaction_date' => now(),
                'is_posted' => true,
                'posted_at' => now(),
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Reserve stock for an order (does not change quantity_on_hand, only quantity_reserved).
     */
    public function reserve(
        Product $product,
        StockLocation $stockLocation,
        float $quantity,
        string $referenceType,
        int $referenceId
    ): StockReservation {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Reserve quantity must be positive.');
        }

        return DB::transaction(function () use ($product, $stockLocation, $quantity, $referenceType, $referenceId) {
            $stockLevel = $this->getOrCreateStockLevel($product, $stockLocation);

            $availableQuantity = $stockLevel->quantity_on_hand - $stockLevel->quantity_reserved;

            if ($quantity > $availableQuantity) {
                throw new InvalidArgumentException(
                    "Insufficient available stock for reservation. Available: {$availableQuantity}, Requested: {$quantity}"
                );
            }

            // Update reserved quantity
            $stockLevel->increment('quantity_reserved', $quantity);

            // Create reservation record
            return StockReservation::create([
                'product_id' => $product->id,
                'stock_location_id' => $stockLocation->id,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Release a reservation (reduces quantity_reserved).
     */
    public function releaseReservation(
        Product $product,
        StockLocation $stockLocation,
        string $referenceType,
        int $referenceId,
        string $newStatus = 'cancelled'
    ): void {
        DB::transaction(function () use ($product, $stockLocation, $referenceType, $referenceId, $newStatus) {
            $reservation = StockReservation::where('product_id', $product->id)
                ->where('stock_location_id', $stockLocation->id)
                ->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->where('status', 'active')
                ->first();

            if (! $reservation) {
                return;
            }

            $stockLevel = $this->getStockLevel($product, $stockLocation);

            if ($stockLevel) {
                $stockLevel->decrement('quantity_reserved', $reservation->quantity);
            }

            $reservation->update(['status' => $newStatus]);
        });
    }

    /**
     * Fulfill a reservation (converts reservation to issue).
     */
    public function fulfillReservation(
        Product $product,
        StockLocation $stockLocation,
        string $referenceType,
        int $referenceId,
        ?string $notes = null
    ): StockTransaction {
        return DB::transaction(function () use ($product, $stockLocation, $referenceType, $referenceId, $notes) {
            $reservation = StockReservation::where('product_id', $product->id)
                ->where('stock_location_id', $stockLocation->id)
                ->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->where('status', 'active')
                ->first();

            if (! $reservation) {
                throw new InvalidArgumentException('No active reservation found.');
            }

            $stockLevel = $this->getStockLevel($product, $stockLocation);

            if (! $stockLevel) {
                throw new InvalidArgumentException('No stock level found.');
            }

            $quantity = $reservation->quantity;
            $quantityBefore = $stockLevel->quantity_on_hand;
            $unitCost = $stockLevel->average_cost ?? 0;

            // Reduce both on_hand and reserved
            $stockLevel->update([
                'quantity_on_hand' => $stockLevel->quantity_on_hand - $quantity,
                'quantity_reserved' => $stockLevel->quantity_reserved - $quantity,
            ]);

            // Mark reservation as fulfilled
            $reservation->update(['status' => 'fulfilled']);

            // Create transaction record
            return StockTransaction::create([
                'product_id' => $product->id,
                'stock_location_id' => $stockLocation->id,
                'transaction_type' => 'issue',
                'quantity' => -$quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $stockLevel->quantity_on_hand,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes ?? 'Fulfilled from reservation',
                'transaction_date' => now(),
                'is_posted' => true,
                'posted_at' => now(),
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Transfer stock between locations.
     *
     * @return array{out: StockTransaction, in: StockTransaction}
     */
    public function transfer(
        Product $product,
        StockLocation $fromLocation,
        StockLocation $toLocation,
        float $quantity,
        ?string $notes = null
    ): array {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Transfer quantity must be positive.');
        }

        if ($fromLocation->id === $toLocation->id) {
            throw new InvalidArgumentException('Cannot transfer to the same location.');
        }

        return DB::transaction(function () use ($product, $fromLocation, $toLocation, $quantity, $notes) {
            $fromLevel = $this->getStockLevel($product, $fromLocation);

            if (! $fromLevel) {
                throw new InvalidArgumentException('No stock level at source location.');
            }

            $availableQuantity = $fromLevel->quantity_on_hand - $fromLevel->quantity_reserved;

            if ($quantity > $availableQuantity) {
                throw new InvalidArgumentException(
                    "Insufficient available stock for transfer. Available: {$availableQuantity}, Requested: {$quantity}"
                );
            }

            $unitCost = $fromLevel->average_cost ?? 0;
            $fromQuantityBefore = $fromLevel->quantity_on_hand;

            // Decrease from source
            $fromLevel->update([
                'quantity_on_hand' => $fromLevel->quantity_on_hand - $quantity,
            ]);

            // Increase at destination
            $toLevel = $this->getOrCreateStockLevel($product, $toLocation);
            $toQuantityBefore = $toLevel->quantity_on_hand;

            // Calculate new weighted average for destination
            $existingValue = $toLevel->quantity_on_hand * ($toLevel->average_cost ?? 0);
            $transferValue = $quantity * $unitCost;
            $newTotalQuantity = $toLevel->quantity_on_hand + $quantity;
            $newAverageCost = $newTotalQuantity > 0
                ? ($existingValue + $transferValue) / $newTotalQuantity
                : $unitCost;

            $toLevel->update([
                'quantity_on_hand' => $newTotalQuantity,
                'average_cost' => $newAverageCost,
            ]);

            // Create transfer out transaction
            $outTransaction = StockTransaction::create([
                'product_id' => $product->id,
                'stock_location_id' => $fromLocation->id,
                'to_stock_location_id' => $toLocation->id,
                'transaction_type' => 'transfer_out',
                'quantity' => -$quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'quantity_before' => $fromQuantityBefore,
                'quantity_after' => $fromLevel->quantity_on_hand,
                'notes' => $notes,
                'transaction_date' => now(),
                'is_posted' => true,
                'posted_at' => now(),
                'created_by' => auth()->id(),
            ]);

            // Create transfer in transaction
            $inTransaction = StockTransaction::create([
                'product_id' => $product->id,
                'stock_location_id' => $toLocation->id,
                'to_stock_location_id' => $fromLocation->id,
                'transaction_type' => 'transfer_in',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'quantity_before' => $toQuantityBefore,
                'quantity_after' => $newTotalQuantity,
                'notes' => $notes,
                'transaction_date' => now(),
                'is_posted' => true,
                'posted_at' => now(),
                'created_by' => auth()->id(),
            ]);

            return ['out' => $outTransaction, 'in' => $inTransaction];
        });
    }

    /**
     * Adjust stock (positive = increase, negative = decrease).
     */
    public function adjust(
        Product $product,
        StockLocation $stockLocation,
        float $quantity,
        ?float $unitCost = null,
        ?string $notes = null
    ): StockTransaction {
        if ($quantity == 0) {
            throw new InvalidArgumentException('Adjustment quantity cannot be zero.');
        }

        return DB::transaction(function () use ($product, $stockLocation, $quantity, $unitCost, $notes) {
            $stockLevel = $this->getOrCreateStockLevel($product, $stockLocation);
            $quantityBefore = $stockLevel->quantity_on_hand;

            $transactionType = $quantity > 0 ? 'adjustment_in' : 'adjustment_out';
            $effectiveCost = $unitCost ?? $stockLevel->average_cost ?? 0;

            if ($quantity > 0) {
                // Positive adjustment - recalculate average cost
                $existingValue = $stockLevel->quantity_on_hand * ($stockLevel->average_cost ?? 0);
                $newValue = $quantity * $effectiveCost;
                $newTotalQuantity = $stockLevel->quantity_on_hand + $quantity;
                $newAverageCost = $newTotalQuantity > 0
                    ? ($existingValue + $newValue) / $newTotalQuantity
                    : $effectiveCost;

                $stockLevel->update([
                    'quantity_on_hand' => $newTotalQuantity,
                    'average_cost' => $newAverageCost,
                ]);
            } else {
                // Negative adjustment
                $newQuantity = $stockLevel->quantity_on_hand + $quantity; // quantity is negative

                $inventorySettings = $product->company->inventorySettings;
                $allowNegative = $inventorySettings?->allow_negative_stock ?? false;

                if (! $allowNegative && $newQuantity < 0) {
                    throw new InvalidArgumentException(
                        "Adjustment would result in negative stock. Current: {$stockLevel->quantity_on_hand}, Adjustment: {$quantity}"
                    );
                }

                $stockLevel->update([
                    'quantity_on_hand' => $newQuantity,
                ]);
            }

            return StockTransaction::create([
                'product_id' => $product->id,
                'stock_location_id' => $stockLocation->id,
                'transaction_type' => $transactionType,
                'quantity' => $quantity,
                'unit_cost' => $effectiveCost,
                'total_cost' => abs($quantity) * $effectiveCost,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $stockLevel->quantity_on_hand,
                'notes' => $notes,
                'transaction_date' => now(),
                'is_posted' => true,
                'posted_at' => now(),
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Get stock level for a product at a location.
     */
    public function getStockLevel(Product $product, StockLocation $stockLocation): ?StockLevel
    {
        return StockLevel::where('product_id', $product->id)
            ->where('stock_location_id', $stockLocation->id)
            ->first();
    }

    /**
     * Get or create stock level for a product at a location.
     */
    public function getOrCreateStockLevel(Product $product, StockLocation $stockLocation): StockLevel
    {
        return StockLevel::firstOrCreate(
            [
                'product_id' => $product->id,
                'stock_location_id' => $stockLocation->id,
            ],
            [
                'company_id' => $product->company_id,
                'quantity_on_hand' => 0,
                'quantity_reserved' => 0,
                'average_cost' => $product->cost_price ?? 0,
            ]
        );
    }

    /**
     * Get total stock across all locations for a product.
     */
    public function getTotalStock(Product $product): float
    {
        return StockLevel::where('product_id', $product->id)
            ->sum('quantity_on_hand');
    }

    /**
     * Get total available stock across all locations for a product.
     */
    public function getTotalAvailableStock(Product $product): float
    {
        return StockLevel::where('product_id', $product->id)
            ->selectRaw('SUM(quantity_on_hand - quantity_reserved) as available')
            ->value('available') ?? 0;
    }

    /**
     * Get products below their reorder point.
     */
    public function getProductsBelowReorderPoint(): Collection
    {
        return Product::stocked()
            ->whereNotNull('reorder_point')
            ->whereHas('stockLevels', function ($query) {
                $query->whereRaw('quantity_on_hand - quantity_reserved <= products.reorder_point');
            })
            ->with('stockLevels')
            ->get();
    }

    /**
     * Get stock value for a product at a location.
     */
    public function getStockValue(Product $product, StockLocation $stockLocation): float
    {
        $level = $this->getStockLevel($product, $stockLocation);

        if (! $level) {
            return 0;
        }

        return $level->quantity_on_hand * ($level->average_cost ?? 0);
    }

    /**
     * Get total stock value across all locations for a product.
     */
    public function getTotalStockValue(Product $product): float
    {
        return (float) StockLevel::where('product_id', $product->id)
            ->get()
            ->sum(function ($level) {
                return $level->quantity_on_hand * ($level->average_cost ?? 0);
            });
    }

    /**
     * Get available stock (on hand minus reserved) at a specific location.
     */
    public function getAvailableStock(Product $product, StockLocation $stockLocation): float
    {
        $level = $this->getStockLevel($product, $stockLocation);

        if (! $level) {
            return 0;
        }

        return (float) ($level->quantity_on_hand - $level->quantity_reserved);
    }
}
