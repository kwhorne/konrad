<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankStatement extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_MATCHING = 'matching';

    public const STATUS_MATCHED = 'matched';

    public const STATUS_RECONCILED = 'reconciled';

    public const STATUS_FINALIZED = 'finalized';

    protected $fillable = [
        'reference_number',
        'file_path',
        'original_filename',
        'bank_name',
        'account_number',
        'bank_account_id',
        'from_date',
        'to_date',
        'opening_balance',
        'closing_balance',
        'status',
        'transaction_count',
        'matched_count',
        'unmatched_count',
        'created_by',
        'finalized_by',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'finalized_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (BankStatement $statement) {
            if (empty($statement->reference_number)) {
                $statement->reference_number = static::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        $prefix = 'BU';
        $year = date('Y');

        $lastStatement = static::withTrashed()
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $pattern = '/'.$prefix.'-'.$year.'-(\d+)/';
        if ($lastStatement && preg_match($pattern, $lastStatement->reference_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $nextNumber);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'bank_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class)->orderBy('sort_order');
    }

    public function canFinalize(): bool
    {
        if ($this->status === self::STATUS_FINALIZED) {
            return false;
        }

        return $this->unmatched_count === 0;
    }

    public function finalize(User $user): bool
    {
        if (! $this->canFinalize()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_FINALIZED,
            'finalized_by' => $user->id,
            'finalized_at' => now(),
        ]);

        return true;
    }

    public function recalculateCounts(): void
    {
        $matchedCount = $this->transactions()
            ->whereIn('match_status', ['auto_matched', 'manual_matched'])
            ->count();

        $unmatchedCount = $this->transactions()
            ->where('match_status', 'unmatched')
            ->count();

        $totalCount = $this->transactions()->count();

        $this->update([
            'transaction_count' => $totalCount,
            'matched_count' => $matchedCount,
            'unmatched_count' => $unmatchedCount,
        ]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Venter',
            self::STATUS_MATCHING => 'Matcher',
            self::STATUS_MATCHED => 'Matchet',
            self::STATUS_RECONCILED => 'Avstemt',
            self::STATUS_FINALIZED => 'Fullfort',
            default => 'Ukjent',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'zinc',
            self::STATUS_MATCHING => 'blue',
            self::STATUS_MATCHED => 'amber',
            self::STATUS_RECONCILED => 'cyan',
            self::STATUS_FINALIZED => 'green',
            default => 'zinc',
        };
    }

    public function getMatchedPercentAttribute(): int
    {
        if ($this->transaction_count === 0) {
            return 0;
        }

        return (int) round(($this->matched_count / $this->transaction_count) * 100);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', self::STATUS_FINALIZED);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }
}
