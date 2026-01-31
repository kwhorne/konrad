<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherLine extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'voucher_id',
        'account_id',
        'description',
        'debit',
        'credit',
        'vat_amount',
        'contact_id',
        'sort_order',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (VoucherLine $line) {
            $line->voucher->recalculateTotals();
        });

        static::deleted(function (VoucherLine $line) {
            $line->voucher->recalculateTotals();
        });
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function getAmountAttribute(): float
    {
        return $this->debit > 0 ? $this->debit : -$this->credit;
    }

    public function scopeByAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeByContact($query, int $contactId)
    {
        return $query->where('contact_id', $contactId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
