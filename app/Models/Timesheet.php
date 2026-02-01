<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timesheet extends Model
{
    use BelongsToCompany, HasFactory;

    // Statuser
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'week_start',
        'week_end',
        'status',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'total_hours',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'total_hours' => 'decimal:2',
        ];
    }

    // Relasjoner

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimesheetEntry::class)->ordered();
    }

    public function submittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Workflow-metoder

    public function submit(User $user): bool
    {
        if ($this->status !== self::STATUS_DRAFT && $this->status !== self::STATUS_REJECTED) {
            return false;
        }

        if ($this->total_hours <= 0) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'submitted_by' => $user->id,
            'submitted_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        return true;
    }

    public function approve(User $user): bool
    {
        if ($this->status !== self::STATUS_SUBMITTED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return true;
    }

    public function reject(User $user, string $reason): bool
    {
        if ($this->status !== self::STATUS_SUBMITTED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    public function reopen(): bool
    {
        if ($this->status !== self::STATUS_REJECTED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_DRAFT,
        ]);

        return true;
    }

    public function recalculateTotalHours(): void
    {
        $this->total_hours = $this->entries()->sum('hours');
        $this->save();
    }

    // Accessors

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Utkast',
            self::STATUS_SUBMITTED => 'Innsendt',
            self::STATUS_APPROVED => 'Godkjent',
            self::STATUS_REJECTED => 'Avvist',
            default => 'Ukjent',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'zinc',
            self::STATUS_SUBMITTED => 'amber',
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
            default => 'zinc',
        };
    }

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    public function getIsSubmittableAttribute(): bool
    {
        return $this->is_editable && $this->total_hours > 0;
    }

    public function getWeekNumberAttribute(): int
    {
        return $this->week_start->isoWeek();
    }

    public function getWeekLabelAttribute(): string
    {
        return 'Uke '.$this->week_number.', '.$this->week_start->year;
    }

    public function getDateRangeLabelAttribute(): string
    {
        return $this->week_start->format('d.m').' - '.$this->week_end->format('d.m.Y');
    }

    // Scopes

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeAwaitingApproval($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeForWeek($query, Carbon $date)
    {
        $weekStart = $date->copy()->startOfWeek();

        return $query->where('week_start', $weekStart);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('week_start');
    }

    // Helpers

    public static function getWeekDates(Carbon $date): array
    {
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();

        return [
            'start' => $weekStart,
            'end' => $weekEnd,
        ];
    }

    public function getDaysOfWeek(): array
    {
        $days = [];
        $current = $this->week_start->copy();

        while ($current <= $this->week_end) {
            $days[] = [
                'date' => $current->copy(),
                'day_name' => $current->isoFormat('ddd'),
                'day_short' => $current->isoFormat('dd'),
                'day_number' => $current->day,
                'is_weekend' => $current->isWeekend(),
            ];
            $current->addDay();
        }

        return $days;
    }
}
