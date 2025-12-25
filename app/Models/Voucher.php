<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voucher_number',
        'voucher_date',
        'description',
        'voucher_type',
        'reference_type',
        'reference_id',
        'total_debit',
        'total_credit',
        'is_balanced',
        'is_posted',
        'created_by',
        'posted_at',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'is_balanced' => 'boolean',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Voucher $voucher) {
            if (empty($voucher->voucher_number)) {
                $voucher->voucher_number = static::generateVoucherNumber();
            }
            if (empty($voucher->voucher_date)) {
                $voucher->voucher_date = now();
            }
        });
    }

    public static function generateVoucherNumber(): string
    {
        $prefix = 'BIL';
        $year = date('Y');

        $lastVoucher = static::withTrashed()
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $pattern = '/'.$prefix.'-'.$year.'-(\d+)/';
        if ($lastVoucher && preg_match($pattern, $lastVoucher->voucher_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $nextNumber);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(VoucherLine::class)->orderBy('sort_order');
    }

    public function recalculateTotals(): void
    {
        $totals = $this->lines()
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $this->update([
            'total_debit' => $totals->total_debit ?? 0,
            'total_credit' => $totals->total_credit ?? 0,
            'is_balanced' => abs(($totals->total_debit ?? 0) - ($totals->total_credit ?? 0)) < 0.01,
        ]);
    }

    public function post(): bool
    {
        if ($this->is_posted) {
            return false;
        }

        if (! $this->is_balanced) {
            return false;
        }

        $this->update([
            'is_posted' => true,
            'posted_at' => now(),
        ]);

        return true;
    }

    public function getVoucherTypeLabelAttribute(): string
    {
        return match ($this->voucher_type) {
            'manual' => 'Manuell',
            'invoice' => 'Utgående faktura',
            'payment' => 'Innbetaling',
            'supplier_invoice' => 'Leverandørfaktura',
            'supplier_payment' => 'Utbetaling',
            default => 'Ukjent',
        };
    }

    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }

    public function scopeUnposted($query)
    {
        return $query->where('is_posted', false);
    }

    public function scopeBalanced($query)
    {
        return $query->where('is_balanced', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('voucher_date')->orderByDesc('id');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('voucher_type', $type);
    }
}
