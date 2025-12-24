<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'line_type',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'discount_percent',
        'performed_at',
        'performed_by',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'performed_at' => 'date',
        'sort_order' => 'integer',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function performedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function getLineTotalAttribute(): float
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discount = $subtotal * ($this->discount_percent / 100);

        return $subtotal - $discount;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeTimeEntries($query)
    {
        return $query->where('line_type', 'time');
    }

    public function scopeProductEntries($query)
    {
        return $query->where('line_type', 'product');
    }
}
