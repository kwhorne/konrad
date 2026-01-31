<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierInvoiceLine extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'supplier_invoice_id',
        'account_id',
        'description',
        'quantity',
        'unit_price',
        'vat_rate_id',
        'vat_percent',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'vat_percent' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (SupplierInvoiceLine $line) {
            $line->supplierInvoice->recalculateTotals();
        });

        static::deleted(function (SupplierInvoiceLine $line) {
            $line->supplierInvoice->recalculateTotals();
        });
    }

    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function getVatAmountAttribute(): float
    {
        return $this->subtotal * ($this->vat_percent / 100);
    }

    public function getTotalAttribute(): float
    {
        return $this->subtotal + $this->vat_amount;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
