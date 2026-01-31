<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dividend extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'fiscal_year',
        'declaration_date',
        'record_date',
        'payment_date',
        'share_class_id',
        'amount_per_share',
        'total_amount',
        'dividend_type',
        'status',
        'description',
        'resolution_reference',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'declaration_date' => 'date',
            'record_date' => 'date',
            'payment_date' => 'date',
            'amount_per_share' => 'decimal:4',
            'total_amount' => 'decimal:2',
        ];
    }

    // Relationships
    public function shareClass(): BelongsTo
    {
        return $this->belongsTo(ShareClass::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeForYear($query, int $year)
    {
        return $query->where('fiscal_year', $year);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['declared', 'approved']);
    }

    public function scopeOrdinary($query)
    {
        return $query->where('dividend_type', 'ordinary');
    }

    public function scopeExtraordinary($query)
    {
        return $query->where('dividend_type', 'extraordinary');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('payment_date', 'desc');
    }

    // Accessors
    public function getDividendTypeLabel(): string
    {
        return match ($this->dividend_type) {
            'ordinary' => 'Ordinært utbytte',
            'extraordinary' => 'Ekstraordinært utbytte',
            default => $this->dividend_type,
        };
    }

    public function getDividendTypeBadgeColor(): string
    {
        return match ($this->dividend_type) {
            'ordinary' => 'primary',
            'extraordinary' => 'warning',
            default => 'outline',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'declared' => 'Vedtatt',
            'approved' => 'Godkjent',
            'paid' => 'Utbetalt',
            'cancelled' => 'Kansellert',
            default => $this->status,
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'declared' => 'warning',
            'approved' => 'info',
            'paid' => 'success',
            'cancelled' => 'danger',
            default => 'outline',
        };
    }

    public function getFormattedAmountPerShare(): string
    {
        return number_format($this->amount_per_share, 4, ',', ' ').' NOK';
    }

    public function getFormattedTotalAmount(): string
    {
        return number_format($this->total_amount, 2, ',', ' ').' NOK';
    }

    // Business methods
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBePaid(): bool
    {
        return in_array($this->status, ['declared', 'approved']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['declared', 'approved']);
    }

    public function markAsApproved(): void
    {
        $this->update(['status' => 'approved']);
    }

    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Calculate dividend amount for a specific shareholder.
     */
    public function calculateShareholderDividend(Shareholder $shareholder): float
    {
        $shares = $shareholder->getTotalSharesByClass($this->share_class_id);

        return $shares * $this->amount_per_share;
    }

    /**
     * Get all shareholders eligible for this dividend with their amounts.
     *
     * @return array<int, array{shareholder: Shareholder, shares: int, amount: float}>
     */
    public function getEligibleShareholders(): array
    {
        $shareholdings = Shareholding::with('shareholder')
            ->where('share_class_id', $this->share_class_id)
            ->activeAtDate($this->record_date)
            ->get();

        $result = [];

        foreach ($shareholdings as $holding) {
            $shareholderId = $holding->shareholder_id;

            if (! isset($result[$shareholderId])) {
                $result[$shareholderId] = [
                    'shareholder' => $holding->shareholder,
                    'shares' => 0,
                    'amount' => 0,
                ];
            }

            $result[$shareholderId]['shares'] += $holding->number_of_shares;
            $result[$shareholderId]['amount'] += $holding->number_of_shares * $this->amount_per_share;
        }

        return array_values($result);
    }

    /**
     * Recalculate total amount based on shares at record date.
     */
    public function recalculateTotalAmount(): void
    {
        $totalShares = Shareholding::where('share_class_id', $this->share_class_id)
            ->activeAtDate($this->record_date)
            ->sum('number_of_shares');

        $this->update([
            'total_amount' => $totalShares * $this->amount_per_share,
        ]);
    }
}
