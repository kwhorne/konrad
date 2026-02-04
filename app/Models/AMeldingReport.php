<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AMeldingReport extends Model
{
    use BelongsToCompany, HasFactory;

    public const TYPE_ORDINAER = 'ordinaer';

    public const TYPE_TILLEGG = 'tillegg';

    public const TYPE_ERSTATNING = 'erstatning';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_GENERATED = 'generated';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'payroll_run_id',
        'year',
        'month',
        'melding_type',
        'status',
        'melding_data',
        'xml_content',
        'altinn_reference',
        'submitted_at',
        'confirmed_at',
        'rejection_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'melding_data' => 'array',
            'submitted_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get the payroll run this report is for.
     */
    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    /**
     * Get the user who created this report.
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Mark the report as submitted.
     */
    public function markAsSubmitted(string $reference): void
    {
        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'altinn_reference' => $reference,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Mark the report as confirmed.
     */
    public function markAsConfirmed(): void
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Mark the report as rejected.
     */
    public function markAsRejected(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Get status label in Norwegian.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Utkast',
            self::STATUS_GENERATED => 'Generert',
            self::STATUS_SUBMITTED => 'Innsendt',
            self::STATUS_CONFIRMED => 'Bekreftet',
            self::STATUS_REJECTED => 'Avvist',
            default => 'Ukjent',
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'zinc',
            self::STATUS_GENERATED => 'amber',
            self::STATUS_SUBMITTED => 'blue',
            self::STATUS_CONFIRMED => 'green',
            self::STATUS_REJECTED => 'red',
            default => 'zinc',
        };
    }

    /**
     * Get melding type label in Norwegian.
     */
    public function getMeldingTypeLabelAttribute(): string
    {
        return match ($this->melding_type) {
            self::TYPE_ORDINAER => 'Ordinaer',
            self::TYPE_TILLEGG => 'Tilleggsmelding',
            self::TYPE_ERSTATNING => 'Erstatningsmelding',
            default => 'Ukjent',
        };
    }

    /**
     * Get the period label.
     */
    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1 => 'Januar',
            2 => 'Februar',
            3 => 'Mars',
            4 => 'April',
            5 => 'Mai',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'August',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[$this->month].' '.$this->year;
    }

    /**
     * Check if the report can be edited.
     */
    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_GENERATED]);
    }

    /**
     * Scope to get reports for a specific year.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope to order by period.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('year')->orderByDesc('month');
    }
}
