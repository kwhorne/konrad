<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountLine extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'stock_count_id',
        'product_id',
        'expected_quantity',
        'counted_quantity',
        'variance_quantity',
        'unit_cost',
        'expected_value',
        'counted_value',
        'variance_value',
        'variance_reason',
        'is_counted',
        'counted_by',
        'counted_at',
        'sort_order',
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:2',
        'counted_quantity' => 'decimal:2',
        'variance_quantity' => 'decimal:2',
        'unit_cost' => 'decimal:4',
        'expected_value' => 'decimal:2',
        'counted_value' => 'decimal:2',
        'variance_value' => 'decimal:2',
        'is_counted' => 'boolean',
        'counted_at' => 'datetime',
    ];

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function recordCount(float $countedQuantity, ?string $varianceReason = null): void
    {
        $this->counted_quantity = $countedQuantity;
        $this->variance_quantity = $countedQuantity - $this->expected_quantity;
        $this->counted_value = $countedQuantity * $this->unit_cost;
        $this->variance_value = $this->variance_quantity * $this->unit_cost;
        $this->variance_reason = $varianceReason;
        $this->is_counted = true;
        $this->counted_by = auth()->id();
        $this->counted_at = now();
        $this->save();

        $this->stockCount->recalculateTotals();
    }

    public function getVariancePercentageAttribute(): ?float
    {
        if (! $this->is_counted || $this->expected_quantity == 0) {
            return null;
        }

        return round(($this->variance_quantity / $this->expected_quantity) * 100, 1);
    }

    public function getHasVarianceAttribute(): bool
    {
        return $this->is_counted && $this->variance_quantity != 0;
    }

    public function getVarianceTypeAttribute(): ?string
    {
        if (! $this->is_counted) {
            return null;
        }

        if ($this->variance_quantity > 0) {
            return 'overskudd';
        } elseif ($this->variance_quantity < 0) {
            return 'manko';
        }

        return 'ok';
    }
}
