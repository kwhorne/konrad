<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockReservation extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'product_id',
        'stock_location_id',
        'quantity',
        'reference_type',
        'reference_id',
        'status',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Aktiv',
            'fulfilled' => 'Oppfylt',
            'cancelled' => 'Kansellert',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'blue',
            'fulfilled' => 'green',
            'cancelled' => 'zinc',
            default => 'zinc',
        };
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFulfilled($query)
    {
        return $query->where('status', 'fulfilled');
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForLocation($query, int $locationId)
    {
        return $query->where('stock_location_id', $locationId);
    }

    public function scopeForReference($query, string $type, int $id)
    {
        return $query->where('reference_type', $type)->where('reference_id', $id);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('created_at');
    }
}
