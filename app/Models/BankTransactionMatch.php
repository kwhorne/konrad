<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BankTransactionMatch extends Model
{
    use BelongsToCompany;

    public const MATCH_TYPE_EXACT = 'exact';

    public const MATCH_TYPE_FUZZY = 'fuzzy';

    public const MATCH_TYPE_MANUAL = 'manual';

    protected $fillable = [
        'bank_transaction_id',
        'matchable_type',
        'matchable_id',
        'match_type',
        'match_confidence',
        'matched_by',
        'matched_at',
        'is_confirmed',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'match_confidence' => 'decimal:2',
            'matched_at' => 'datetime',
            'is_confirmed' => 'boolean',
        ];
    }

    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }

    public function matchable(): MorphTo
    {
        return $this->morphTo();
    }

    public function matchedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_by');
    }

    public function confirm(?User $user = null): bool
    {
        $this->update([
            'is_confirmed' => true,
            'matched_by' => $user?->id ?? $this->matched_by,
            'matched_at' => $this->matched_at ?? now(),
        ]);

        $this->bankTransaction->update([
            'match_status' => $user
                ? BankTransaction::MATCH_STATUS_MANUAL_MATCHED
                : BankTransaction::MATCH_STATUS_AUTO_MATCHED,
            'match_confidence' => $this->match_confidence,
        ]);

        $this->bankTransaction->bankStatement->recalculateCounts();

        return true;
    }

    public function unconfirm(): bool
    {
        $this->update([
            'is_confirmed' => false,
        ]);

        $this->bankTransaction->update([
            'match_status' => BankTransaction::MATCH_STATUS_UNMATCHED,
            'match_confidence' => null,
        ]);

        $this->bankTransaction->bankStatement->recalculateCounts();

        return true;
    }

    public function getMatchTypeLabelAttribute(): string
    {
        return match ($this->match_type) {
            self::MATCH_TYPE_EXACT => 'Eksakt',
            self::MATCH_TYPE_FUZZY => 'Delvis',
            self::MATCH_TYPE_MANUAL => 'Manuell',
            default => 'Ukjent',
        };
    }

    public function getConfidencePercentAttribute(): int
    {
        return (int) round(($this->match_confidence ?? 0) * 100);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('is_confirmed', true);
    }

    public function scopeUnconfirmed($query)
    {
        return $query->where('is_confirmed', false);
    }
}
