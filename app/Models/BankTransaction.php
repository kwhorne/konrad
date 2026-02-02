<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankTransaction extends Model
{
    use BelongsToCompany, HasFactory;

    public const MATCH_STATUS_UNMATCHED = 'unmatched';

    public const MATCH_STATUS_AUTO_MATCHED = 'auto_matched';

    public const MATCH_STATUS_MANUAL_MATCHED = 'manual_matched';

    public const MATCH_STATUS_IGNORED = 'ignored';

    public const TYPE_CREDIT = 'credit';

    public const TYPE_DEBIT = 'debit';

    protected $fillable = [
        'bank_statement_id',
        'transaction_date',
        'value_date',
        'description',
        'reference',
        'amount',
        'running_balance',
        'transaction_type',
        'counterparty_name',
        'counterparty_account',
        'raw_data',
        'match_status',
        'match_confidence',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'value_date' => 'date',
            'amount' => 'decimal:2',
            'running_balance' => 'decimal:2',
            'match_confidence' => 'decimal:2',
            'raw_data' => 'array',
        ];
    }

    public function bankStatement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(BankTransactionMatch::class);
    }

    public function confirmedMatch(): HasOne
    {
        return $this->hasOne(BankTransactionMatch::class)->where('is_confirmed', true);
    }

    public function draftVoucher(): HasOne
    {
        return $this->hasOne(BankReconciliationDraft::class);
    }

    public function getIsCreditAttribute(): bool
    {
        return $this->transaction_type === self::TYPE_CREDIT;
    }

    public function getIsDebitAttribute(): bool
    {
        return $this->transaction_type === self::TYPE_DEBIT;
    }

    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->isCredit ? '+' : '-';

        return $prefix.number_format(abs($this->amount), 2, ',', ' ');
    }

    public function getAmountColorAttribute(): string
    {
        return $this->isCredit ? 'green' : 'red';
    }

    public function getMatchStatusLabelAttribute(): string
    {
        return match ($this->match_status) {
            self::MATCH_STATUS_UNMATCHED => 'Ikke matchet',
            self::MATCH_STATUS_AUTO_MATCHED => 'Auto-matchet',
            self::MATCH_STATUS_MANUAL_MATCHED => 'Manuelt matchet',
            self::MATCH_STATUS_IGNORED => 'Ignorert',
            default => 'Ukjent',
        };
    }

    public function getMatchStatusColorAttribute(): string
    {
        return match ($this->match_status) {
            self::MATCH_STATUS_UNMATCHED => 'amber',
            self::MATCH_STATUS_AUTO_MATCHED => 'blue',
            self::MATCH_STATUS_MANUAL_MATCHED => 'green',
            self::MATCH_STATUS_IGNORED => 'zinc',
            default => 'zinc',
        };
    }

    public function scopeCredits($query)
    {
        return $query->where('transaction_type', self::TYPE_CREDIT);
    }

    public function scopeDebits($query)
    {
        return $query->where('transaction_type', self::TYPE_DEBIT);
    }

    public function scopeUnmatched($query)
    {
        return $query->where('match_status', self::MATCH_STATUS_UNMATCHED);
    }

    public function scopeMatched($query)
    {
        return $query->whereIn('match_status', [
            self::MATCH_STATUS_AUTO_MATCHED,
            self::MATCH_STATUS_MANUAL_MATCHED,
        ]);
    }

    public function scopeIgnored($query)
    {
        return $query->where('match_status', self::MATCH_STATUS_IGNORED);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeByDate($query)
    {
        return $query->orderBy('transaction_date')->orderBy('sort_order');
    }
}
