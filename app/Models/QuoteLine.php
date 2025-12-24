<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'product_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percent',
        'vat_rate_id',
        'vat_percent',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'vat_percent' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (QuoteLine $line) {
            $line->quote->recalculateTotals();
        });

        static::deleted(function (QuoteLine $line) {
            $line->quote->recalculateTotals();
        });
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }

    public function getLineTotalAttribute(): float
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discount = $subtotal * ($this->discount_percent / 100);

        return $subtotal - $discount;
    }

    public function getLineVatAttribute(): float
    {
        return $this->line_total * ($this->vat_percent / 100);
    }

    public function getLineTotalWithVatAttribute(): float
    {
        return $this->line_total + $this->line_vat;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
