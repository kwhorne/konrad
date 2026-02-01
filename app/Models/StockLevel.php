<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLevel extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'product_id',
        'stock_location_id',
        'quantity_on_hand',
        'quantity_reserved',
        'average_cost',
        'last_counted_at',
    ];

    protected $casts = [
        'quantity_on_hand' => 'decimal:2',
        'quantity_reserved' => 'decimal:2',
        'average_cost' => 'decimal:4',
        'last_counted_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class);
    }

    public function getQuantityAvailableAttribute(): float
    {
        return (float) $this->quantity_on_hand - (float) $this->quantity_reserved;
    }

    public function getTotalValueAttribute(): float
    {
        return (float) $this->quantity_on_hand * (float) $this->average_cost;
    }

    public function scopeWithStock($query)
    {
        return $query->where('quantity_on_hand', '>', 0);
    }

    public function scopeWithAvailableStock($query)
    {
        return $query->whereRaw('quantity_on_hand > quantity_reserved');
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForLocation($query, int $locationId)
    {
        return $query->where('stock_location_id', $locationId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('product_id')->orderBy('stock_location_id');
    }
}
