<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderLine extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percent',
        'vat_rate_id',
        'vat_percent',
        'quantity_received',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'vat_percent' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (PurchaseOrderLine $line) {
            $line->purchaseOrder->recalculateTotals();
        });

        static::deleted(function (PurchaseOrderLine $line) {
            $line->purchaseOrder->recalculateTotals();
        });
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }

    public function goodsReceiptLines(): HasMany
    {
        return $this->hasMany(GoodsReceiptLine::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }

    public function getDiscountAmountAttribute(): float
    {
        return $this->subtotal * ((float) $this->discount_percent / 100);
    }

    public function getLineTotalAttribute(): float
    {
        return $this->subtotal - $this->discount_amount;
    }

    public function getVatAmountAttribute(): float
    {
        return $this->line_total * ((float) $this->vat_percent / 100);
    }

    public function getTotalWithVatAttribute(): float
    {
        return $this->line_total + $this->vat_amount;
    }

    public function getQuantityOutstandingAttribute(): float
    {
        return max(0, (float) $this->quantity - (float) $this->quantity_received);
    }

    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->quantity_received >= $this->quantity;
    }

    public function getIsPartiallyReceivedAttribute(): bool
    {
        return $this->quantity_received > 0 && $this->quantity_received < $this->quantity;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeWithOutstanding($query)
    {
        return $query->whereRaw('quantity > quantity_received');
    }
}
