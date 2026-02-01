<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'product_group_id',
        'product_type_id',
        'unit_id',
        'price',
        'cost_price',
        'is_active',
        'sort_order',
        'is_stocked',
        'reorder_point',
        'reorder_quantity',
        'default_stock_location_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'is_stocked' => 'boolean',
        'reorder_point' => 'decimal:2',
        'reorder_quantity' => 'decimal:2',
    ];

    public function productGroup(): BelongsTo
    {
        return $this->belongsTo(ProductGroup::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function defaultStockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'default_stock_location_id');
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function scopeStocked($query)
    {
        return $query->where('is_stocked', true);
    }

    public function scopeBelowReorderPoint($query)
    {
        return $query->stocked()
            ->whereNotNull('reorder_point')
            ->whereHas('stockLevels', function ($q) {
                $q->whereRaw('quantity_on_hand - quantity_reserved <= products.reorder_point');
            });
    }

    public function getTotalStockAttribute(): float
    {
        return $this->stockLevels->sum('quantity_on_hand');
    }

    public function getTotalAvailableAttribute(): float
    {
        return $this->stockLevels->sum(function ($level) {
            return $level->quantity_on_hand - $level->quantity_reserved;
        });
    }

    public function getAverageCostAttribute(): ?float
    {
        $level = $this->stockLevels->first();

        return $level?->average_cost;
    }
}
