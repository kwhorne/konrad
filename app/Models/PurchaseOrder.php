<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number',
        'contact_id',
        'stock_location_id',
        'status',
        'order_date',
        'expected_date',
        'supplier_reference',
        'shipping_address',
        'notes',
        'internal_notes',
        'subtotal',
        'vat_total',
        'total',
        'created_by',
        'approved_by',
        'approved_at',
        'sent_at',
        'sort_order',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'subtotal' => 'decimal:2',
        'vat_total' => 'decimal:2',
        'total' => 'decimal:2',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $po) {
            if (empty($po->po_number)) {
                $po->po_number = static::generatePoNumber();
            }
            if (empty($po->order_date)) {
                $po->order_date = now();
            }
        });
    }

    public static function generatePoNumber(): string
    {
        $prefix = 'PO';
        $year = date('Y');

        $lastPo = static::withTrashed()
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $pattern = '/'.$prefix.'-'.$year.'-(\d+)/';
        if ($lastPo && preg_match($pattern, $lastPo->po_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $nextNumber);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function stockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseOrderLine::class)->orderBy('sort_order');
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = 0;
        $vatTotal = 0;

        foreach ($this->lines as $line) {
            $lineSubtotal = $line->quantity * $line->unit_price * (1 - $line->discount_percent / 100);
            $lineVat = $lineSubtotal * ($line->vat_percent / 100);
            $subtotal += $lineSubtotal;
            $vatTotal += $lineVat;
        }

        $this->update([
            'subtotal' => $subtotal,
            'vat_total' => $vatTotal,
            'total' => $subtotal + $vatTotal,
        ]);
    }

    public function updateReceiptStatus(): void
    {
        $this->load('lines');

        $allReceived = $this->lines->every(fn ($line) => $line->quantity_received >= $line->quantity);
        $anyReceived = $this->lines->some(fn ($line) => $line->quantity_received > 0);

        if ($allReceived && $this->lines->isNotEmpty()) {
            $this->update(['status' => 'received']);
        } elseif ($anyReceived) {
            $this->update(['status' => 'partially_received']);
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Utkast',
            'pending_approval' => 'Til godkjenning',
            'approved' => 'Godkjent',
            'sent' => 'Sendt',
            'partially_received' => 'Delvis mottatt',
            'received' => 'Mottatt',
            'cancelled' => 'Kansellert',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'zinc',
            'pending_approval' => 'yellow',
            'approved' => 'blue',
            'sent' => 'indigo',
            'partially_received' => 'orange',
            'received' => 'green',
            'cancelled' => 'red',
            default => 'zinc',
        };
    }

    public function getCanEditAttribute(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    public function getCanApproveAttribute(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function getCanSendAttribute(): bool
    {
        return in_array($this->status, ['approved', 'pending_approval']);
    }

    public function getCanReceiveAttribute(): bool
    {
        return in_array($this->status, ['approved', 'sent', 'partially_received']);
    }

    public function getCanCancelAttribute(): bool
    {
        return ! in_array($this->status, ['received', 'cancelled']);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['approved', 'sent', 'partially_received']);
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('order_date')->orderByDesc('id');
    }
}
