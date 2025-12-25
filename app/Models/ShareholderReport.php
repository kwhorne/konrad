<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShareholderReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'year',
        'report_date',
        'share_capital',
        'total_shares',
        'number_of_shareholders',
        'status',
        'snapshot_data',
        'changes_during_year',
        'dividend_summary',
        'notes',
        'created_by',
        'altinn_submission_id',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'report_date' => 'date',
            'share_capital' => 'decimal:2',
            'total_shares' => 'integer',
            'number_of_shareholders' => 'integer',
            'snapshot_data' => 'array',
            'changes_during_year' => 'array',
            'dividend_summary' => 'array',
        ];
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function altinnSubmission(): BelongsTo
    {
        return $this->belongsTo(AltinnSubmission::class);
    }

    public function submission(): MorphOne
    {
        return $this->morphOne(AltinnSubmission::class, 'submittable');
    }

    // Scopes
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('year', 'desc');
    }

    // Accessors
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Utkast',
            'ready' => 'Klar for innsending',
            'submitted' => 'Sendt inn',
            default => $this->status,
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'draft' => 'warning',
            'ready' => 'info',
            'submitted' => 'success',
            default => 'outline',
        };
    }

    public function getFormattedShareCapital(): string
    {
        return number_format($this->share_capital, 2, ',', ' ').' NOK';
    }

    public function getReportPeriod(): string
    {
        return "01.01.{$this->year} - 31.12.{$this->year}";
    }

    public function getDeadline(): \Carbon\Carbon
    {
        // RF-1086 deadline is January 31 of the following year
        return \Carbon\Carbon::create($this->year + 1, 1, 31);
    }

    public function getDaysUntilDeadline(): int
    {
        return now()->diffInDays($this->getDeadline(), false);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'submitted' && now()->isAfter($this->getDeadline());
    }

    // Business methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'ready']);
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === 'ready';
    }

    public function markAsReady(): void
    {
        $this->update(['status' => 'ready']);
    }

    public function markAsSubmitted(): void
    {
        $this->update(['status' => 'submitted']);
    }

    public function markAsDraft(): void
    {
        $this->update(['status' => 'draft']);
    }

    /**
     * Generate snapshot of current shareholder data.
     */
    public function generateSnapshot(): void
    {
        $reportDate = $this->report_date ?? \Carbon\Carbon::create($this->year, 12, 31);

        // Get all shareholders with active holdings at report date
        $shareholders = Shareholder::with(['activeShareholdings.shareClass'])
            ->whereHas('shareholdings', function ($query) use ($reportDate) {
                $query->activeAtDate($reportDate);
            })
            ->get();

        $snapshot = [
            'generated_at' => now()->toIso8601String(),
            'report_date' => $reportDate->toDateString(),
            'share_classes' => [],
            'shareholders' => [],
        ];

        // Share classes
        $shareClasses = ShareClass::active()->ordered()->get();
        foreach ($shareClasses as $class) {
            $snapshot['share_classes'][] = [
                'id' => $class->id,
                'code' => $class->code,
                'name' => $class->name,
                'par_value' => $class->par_value,
                'total_shares' => $class->total_shares,
                'has_voting_rights' => $class->has_voting_rights,
                'has_dividend_rights' => $class->has_dividend_rights,
            ];
        }

        // Shareholders
        foreach ($shareholders as $shareholder) {
            $shareholderData = [
                'id' => $shareholder->id,
                'type' => $shareholder->shareholder_type,
                'name' => $shareholder->name,
                'identifier' => $shareholder->shareholder_type === 'company'
                    ? $shareholder->organization_number
                    : $shareholder->national_id,
                'country_code' => $shareholder->country_code,
                'holdings' => [],
            ];

            foreach ($shareholder->activeShareholdings as $holding) {
                $shareholderData['holdings'][] = [
                    'share_class_id' => $holding->share_class_id,
                    'share_class_code' => $holding->shareClass->code,
                    'number_of_shares' => $holding->number_of_shares,
                    'acquisition_cost' => $holding->acquisition_cost,
                    'acquired_date' => $holding->acquired_date->toDateString(),
                    'acquisition_type' => $holding->acquisition_type,
                ];
            }

            $snapshot['shareholders'][] = $shareholderData;
        }

        // Calculate summary fields
        $this->update([
            'snapshot_data' => $snapshot,
            'share_capital' => $shareClasses->sum(fn ($c) => $c->total_shares * $c->par_value),
            'total_shares' => $shareClasses->sum('total_shares'),
            'number_of_shareholders' => $shareholders->count(),
        ]);
    }

    /**
     * Generate summary of changes during the year.
     */
    public function generateChangeSummary(): void
    {
        $yearStart = \Carbon\Carbon::create($this->year, 1, 1);
        $yearEnd = \Carbon\Carbon::create($this->year, 12, 31);

        $transactions = ShareTransaction::with(['shareClass', 'fromShareholder', 'toShareholder'])
            ->whereBetween('transaction_date', [$yearStart, $yearEnd])
            ->ordered()
            ->get();

        $summary = [
            'generated_at' => now()->toIso8601String(),
            'period' => [
                'from' => $yearStart->toDateString(),
                'to' => $yearEnd->toDateString(),
            ],
            'statistics' => [
                'total_transactions' => $transactions->count(),
                'issues' => $transactions->where('transaction_type', 'issue')->count(),
                'transfers' => $transactions->where('transaction_type', 'transfer')->count(),
                'redemptions' => $transactions->where('transaction_type', 'redemption')->count(),
                'other' => $transactions->whereNotIn('transaction_type', ['issue', 'transfer', 'redemption'])->count(),
            ],
            'transactions' => [],
        ];

        foreach ($transactions as $transaction) {
            $summary['transactions'][] = [
                'transaction_number' => $transaction->transaction_number,
                'date' => $transaction->transaction_date->toDateString(),
                'type' => $transaction->transaction_type,
                'share_class_code' => $transaction->shareClass->code,
                'from_shareholder' => $transaction->fromShareholder?->name,
                'to_shareholder' => $transaction->toShareholder?->name,
                'number_of_shares' => $transaction->number_of_shares,
                'price_per_share' => $transaction->price_per_share,
                'total_amount' => $transaction->total_amount,
            ];
        }

        $this->update(['changes_during_year' => $summary]);
    }

    /**
     * Generate dividend summary for the year.
     */
    public function generateDividendSummary(): void
    {
        $dividends = Dividend::with('shareClass')
            ->where('fiscal_year', $this->year)
            ->paid()
            ->get();

        $summary = [
            'generated_at' => now()->toIso8601String(),
            'fiscal_year' => $this->year,
            'total_dividends' => $dividends->sum('total_amount'),
            'dividend_count' => $dividends->count(),
            'dividends' => [],
        ];

        foreach ($dividends as $dividend) {
            $summary['dividends'][] = [
                'share_class_code' => $dividend->shareClass->code,
                'type' => $dividend->dividend_type,
                'declaration_date' => $dividend->declaration_date->toDateString(),
                'record_date' => $dividend->record_date->toDateString(),
                'payment_date' => $dividend->payment_date->toDateString(),
                'amount_per_share' => $dividend->amount_per_share,
                'total_amount' => $dividend->total_amount,
            ];
        }

        $this->update(['dividend_summary' => $summary]);
    }

    /**
     * Generate all summaries for the report.
     */
    public function generateAllSummaries(): void
    {
        $this->generateSnapshot();
        $this->generateChangeSummary();
        $this->generateDividendSummary();
    }
}
