<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockTransaction extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'transaction_number',
        'product_id',
        'stock_location_id',
        'to_stock_location_id',
        'transaction_type',
        'quantity',
        'unit_cost',
        'total_cost',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
        'is_posted',
        'posted_at',
        'transaction_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after' => 'decimal:2',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
        'transaction_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (StockTransaction $transaction) {
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = static::generateTransactionNumber();
            }
            if (empty($transaction->transaction_date)) {
                $transaction->transaction_date = now();
            }
        });
    }

    public static function generateTransactionNumber(): string
    {
        $prefix = 'ST';
        $year = date('Y');

        $lastTransaction = static::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $pattern = '/'.$prefix.'-'.$year.'-(\d+)/';
        if ($lastTransaction && preg_match($pattern, $lastTransaction->transaction_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%05d', $prefix, $year, $nextNumber);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class);
    }

    public function toStockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'to_stock_location_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTransactionTypeLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            'receipt' => 'Varemottak',
            'issue' => 'Utlevering',
            'transfer_out' => 'Overføring ut',
            'transfer_in' => 'Overføring inn',
            'adjustment_in' => 'Korrigering +',
            'adjustment_out' => 'Korrigering -',
            'count_adjustment' => 'Tellekorrigering',
            default => $this->transaction_type,
        };
    }

    public function getTransactionTypeColorAttribute(): string
    {
        return match ($this->transaction_type) {
            'receipt', 'transfer_in', 'adjustment_in' => 'green',
            'issue', 'transfer_out', 'adjustment_out' => 'red',
            'count_adjustment' => 'yellow',
            default => 'zinc',
        };
    }

    public function getIsIncreaseAttribute(): bool
    {
        return in_array($this->transaction_type, ['receipt', 'transfer_in', 'adjustment_in']);
    }

    public function getIsDecreaseAttribute(): bool
    {
        return in_array($this->transaction_type, ['issue', 'transfer_out', 'adjustment_out']);
    }

    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }

    public function scopeUnposted($query)
    {
        return $query->where('is_posted', false);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('transaction_type', $type);
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
        return $query->orderByDesc('transaction_date')->orderByDesc('id');
    }
}
