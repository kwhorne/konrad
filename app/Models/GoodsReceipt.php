<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceipt extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'contact_id',
        'stock_location_id',
        'receipt_date',
        'supplier_delivery_note',
        'notes',
        'status',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'posted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (GoodsReceipt $receipt) {
            if (empty($receipt->receipt_number)) {
                $receipt->receipt_number = static::generateReceiptNumber();
            }
            if (empty($receipt->receipt_date)) {
                $receipt->receipt_date = now();
            }
        });
    }

    public static function generateReceiptNumber(): string
    {
        $prefix = 'VM';
        $year = date('Y');

        $lastReceipt = static::withTrashed()
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $pattern = '/'.$prefix.'-'.$year.'-(\d+)/';
        if ($lastReceipt && preg_match($pattern, $lastReceipt->receipt_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $nextNumber);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
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

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(GoodsReceiptLine::class)->orderBy('sort_order');
    }

    public function getTotalQuantityAttribute(): float
    {
        return $this->lines->sum('quantity_received');
    }

    public function getTotalValueAttribute(): float
    {
        return $this->lines->sum(fn ($line) => $line->quantity_received * $line->unit_cost);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Utkast',
            'posted' => 'Bokfort',
            'cancelled' => 'Kansellert',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'zinc',
            'posted' => 'green',
            'cancelled' => 'red',
            default => 'zinc',
        };
    }

    public function getCanEditAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getCanPostAttribute(): bool
    {
        return $this->status === 'draft' && $this->lines->isNotEmpty();
    }

    public function getCanCancelAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('receipt_date')->orderByDesc('id');
    }
}
