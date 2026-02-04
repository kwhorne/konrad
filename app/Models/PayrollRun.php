<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PayrollRun extends Model
{
    use BelongsToCompany, HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_CALCULATED = 'calculated';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PAID = 'paid';

    public const STATUS_REPORTED = 'reported';

    protected $fillable = [
        'year',
        'month',
        'period_start',
        'period_end',
        'utbetalingsdato',
        'status',
        'total_bruttolonn',
        'total_forskuddstrekk',
        'total_nettolonn',
        'total_feriepenger_grunnlag',
        'total_arbeidsgiveravgift',
        'total_otp',
        'aga_sone',
        'aga_sats',
        'created_by',
        'approved_by',
        'approved_at',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
            'utbetalingsdato' => 'date',
            'total_bruttolonn' => 'decimal:2',
            'total_forskuddstrekk' => 'decimal:2',
            'total_nettolonn' => 'decimal:2',
            'total_feriepenger_grunnlag' => 'decimal:2',
            'total_arbeidsgiveravgift' => 'decimal:2',
            'total_otp' => 'decimal:2',
            'aga_sats' => 'decimal:2',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get all entries (per employee) for this run.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }

    /**
     * Get the user who created this run.
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this run.
     */
    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the A-melding report for this run.
     */
    public function aMeldingReport(): HasOne
    {
        return $this->hasOne(AMeldingReport::class);
    }

    /**
     * Approve the payroll run.
     */
    public function approve(User $user): bool
    {
        if ($this->status !== self::STATUS_CALCULATED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Mark the payroll run as paid.
     */
    public function markAsPaid(): bool
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);

        return true;
    }

    /**
     * Mark the payroll run as reported.
     */
    public function markAsReported(): bool
    {
        if ($this->status !== self::STATUS_PAID) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_REPORTED,
        ]);

        return true;
    }

    /**
     * Recalculate totals from entries.
     */
    public function recalculateTotals(): void
    {
        $this->total_bruttolonn = $this->entries()->sum('bruttolonn');
        $this->total_forskuddstrekk = $this->entries()->sum('forskuddstrekk');
        $this->total_nettolonn = $this->entries()->sum('nettolonn');
        $this->total_feriepenger_grunnlag = $this->entries()->sum('feriepenger_grunnlag');
        $this->total_arbeidsgiveravgift = $this->entries()->sum('arbeidsgiveravgift');
        $this->total_otp = $this->entries()->sum('otp_belop');
        $this->save();
    }

    /**
     * Get status label in Norwegian.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Utkast',
            self::STATUS_CALCULATED => 'Beregnet',
            self::STATUS_APPROVED => 'Godkjent',
            self::STATUS_PAID => 'Utbetalt',
            self::STATUS_REPORTED => 'Rapportert',
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
            self::STATUS_CALCULATED => 'amber',
            self::STATUS_APPROVED => 'blue',
            self::STATUS_PAID => 'green',
            self::STATUS_REPORTED => 'emerald',
            default => 'zinc',
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
     * Check if the run can be edited.
     */
    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_CALCULATED]);
    }

    /**
     * Get the total employer cost.
     */
    public function getTotalEmployerCostAttribute(): float
    {
        return $this->total_bruttolonn + $this->total_arbeidsgiveravgift + $this->total_otp;
    }

    /**
     * Scope to get runs for a specific year.
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
