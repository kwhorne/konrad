<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptLine extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_line_id',
        'product_id',
        'description',
        'quantity_ordered',
        'quantity_received',
        'unit_cost',
        'sort_order',
    ];

    protected $casts = [
        'quantity_ordered' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'unit_cost' => 'decimal:4',
        'sort_order' => 'integer',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderLine::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getLineTotalAttribute(): float
    {
        return (float) $this->quantity_received * (float) $this->unit_cost;
    }

    public function getVarianceAttribute(): float
    {
        return (float) $this->quantity_received - (float) $this->quantity_ordered;
    }

    public function getIsOverReceivedAttribute(): bool
    {
        return $this->quantity_received > $this->quantity_ordered;
    }

    public function getIsUnderReceivedAttribute(): bool
    {
        return $this->quantity_received < $this->quantity_ordered;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
