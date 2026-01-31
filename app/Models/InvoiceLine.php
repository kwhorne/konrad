<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'invoice_id',
        'order_line_id',
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
        static::saved(function (InvoiceLine $line) {
            $line->invoice->recalculateTotals();
        });

        static::deleted(function (InvoiceLine $line) {
            $line->invoice->recalculateTotals();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function orderLine(): BelongsTo
    {
        return $this->belongsTo(OrderLine::class);
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
