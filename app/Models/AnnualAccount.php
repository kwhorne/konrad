<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnualAccount extends Model
{
    use HasFactory, SoftDeletes;

    // Størrelseskategorier (regnskapsloven)
    public const SIZE_SMALL = 'small';       // Små foretak

    public const SIZE_MEDIUM = 'medium';     // Mellomstore foretak

    public const SIZE_LARGE = 'large';       // Store foretak

    // Størrelseskriterier (regnskapsloven § 1-6)
    public const SIZE_THRESHOLDS = [
        'small' => [
            'revenue' => 70_000_000,
            'assets' => 35_000_000,
            'employees' => 50,
        ],
        'large' => [
            'revenue' => 350_000_000,
            'assets' => 175_000_000,
            'employees' => 250,
        ],
    ];

    protected $fillable = [
        'fiscal_year',
        'period_start',
        'period_end',
        'company_size',
        'revenue',
        'operating_profit',
        'profit_before_tax',
        'net_profit',
        'total_assets',
        'total_equity',
        'total_liabilities',
        'average_employees',
        'auditor_name',
        'auditor_org_number',
        'audit_opinion',
        'audit_date',
        'board_approval_date',
        'general_meeting_date',
        'status',
        'altinn_submission_id',
        'altinn_reference',
        'submitted_at',
        'notes',
        'created_by',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
            'revenue' => 'decimal:2',
            'operating_profit' => 'decimal:2',
            'profit_before_tax' => 'decimal:2',
            'net_profit' => 'decimal:2',
            'total_assets' => 'decimal:2',
            'total_equity' => 'decimal:2',
            'total_liabilities' => 'decimal:2',
            'average_employees' => 'integer',
            'audit_date' => 'date',
            'board_approval_date' => 'date',
            'general_meeting_date' => 'date',
            'submitted_at' => 'datetime',
        ];
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function accountNotes(): HasMany
    {
        return $this->hasMany(AnnualAccountNote::class)->orderBy('sort_order');
    }

    public function cashFlowStatement(): HasOne
    {
        return $this->hasOne(CashFlowStatement::class);
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
        return $query->where('fiscal_year', $year);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('fiscal_year', 'desc');
    }

    // Accessors
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Utkast',
            'approved' => 'Godkjent av styret',
            'submitted' => 'Sendt inn',
            'accepted' => 'Akseptert',
            'rejected' => 'Avvist',
            default => $this->status,
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'draft' => 'warning',
            'approved' => 'info',
            'submitted' => 'primary',
            'accepted' => 'success',
            'rejected' => 'danger',
            default => 'outline',
        };
    }

    public function getSizeLabel(): string
    {
        return match ($this->company_size) {
            'small' => 'Lite foretak',
            'medium' => 'Mellomstort foretak',
            'large' => 'Stort foretak',
            default => $this->company_size,
        };
    }

    public function getAuditOpinionLabel(): string
    {
        return match ($this->audit_opinion) {
            'unqualified' => 'Uten forbehold',
            'qualified' => 'Med forbehold',
            'adverse' => 'Negativ',
            'disclaimer' => 'Konklusjon ikke avgitt',
            'not_required' => 'Ikke revisjonspliktig',
            default => $this->audit_opinion ?? 'Ikke angitt',
        };
    }

    public function getReportPeriod(): string
    {
        return $this->period_start->format('d.m.Y').' - '.$this->period_end->format('d.m.Y');
    }

    public function getDeadline(): \Carbon\Carbon
    {
        // Årsregnskap frist: 31. juli året etter regnskapsåret
        return \Carbon\Carbon::create($this->fiscal_year + 1, 7, 31);
    }

    public function getDaysUntilDeadline(): int
    {
        return now()->diffInDays($this->getDeadline(), false);
    }

    public function isOverdue(): bool
    {
        return ! in_array($this->status, ['submitted', 'accepted']) && now()->isAfter($this->getDeadline());
    }

    // Status methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'accepted']);
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'approved', 'rejected']);
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === 'approved';
    }

    public function approve(int $userId): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'board_approval_date' => now(),
        ]);
    }

    public function markAsSubmitted(): void
    {
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function markAsDraft(): void
    {
        $this->update(['status' => 'draft']);
    }

    // Size determination
    public function determineSize(): string
    {
        $meetsLarge = 0;
        $meetsSmall = 0;

        // Sjekk store foretak-kriterier
        if ($this->revenue >= self::SIZE_THRESHOLDS['large']['revenue']) {
            $meetsLarge++;
        }
        if ($this->total_assets >= self::SIZE_THRESHOLDS['large']['assets']) {
            $meetsLarge++;
        }
        if ($this->average_employees >= self::SIZE_THRESHOLDS['large']['employees']) {
            $meetsLarge++;
        }

        // Må oppfylle minst 2 av 3 kriterier
        if ($meetsLarge >= 2) {
            return self::SIZE_LARGE;
        }

        // Sjekk små foretak-kriterier
        if ($this->revenue <= self::SIZE_THRESHOLDS['small']['revenue']) {
            $meetsSmall++;
        }
        if ($this->total_assets <= self::SIZE_THRESHOLDS['small']['assets']) {
            $meetsSmall++;
        }
        if ($this->average_employees <= self::SIZE_THRESHOLDS['small']['employees']) {
            $meetsSmall++;
        }

        if ($meetsSmall >= 2) {
            return self::SIZE_SMALL;
        }

        return self::SIZE_MEDIUM;
    }

    // Financial ratios
    public function getEquityRatio(): float
    {
        if ($this->total_assets == 0) {
            return 0;
        }

        return round(($this->total_equity / $this->total_assets) * 100, 2);
    }

    public function getProfitMargin(): float
    {
        if ($this->revenue == 0) {
            return 0;
        }

        return round(($this->net_profit / $this->revenue) * 100, 2);
    }

    public function getOperatingMargin(): float
    {
        if ($this->revenue == 0) {
            return 0;
        }

        return round(($this->operating_profit / $this->revenue) * 100, 2);
    }

    // Notes helpers
    public function requiresCashFlowStatement(): bool
    {
        return $this->company_size !== self::SIZE_SMALL;
    }

    public function getRequiredNotes(): array
    {
        $required = [
            'accounting_principles',
            'equity',
        ];

        if ($this->average_employees > 0) {
            $required[] = 'employees';
        }

        if ($this->company_size !== self::SIZE_SMALL) {
            $required[] = 'fixed_assets';
            $required[] = 'debt';
            $required[] = 'related_parties';
        }

        return $required;
    }
}
