<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AltinnSubmission extends Model
{
    use BelongsToCompany, SoftDeletes;

    // Submission types
    public const TYPE_AKSJONAERREGISTER = 'aksjonaerregister';

    public const TYPE_SKATTEMELDING = 'skattemelding';

    public const TYPE_ARSREGNSKAP = 'arsregnskap';

    // Statuses
    public const STATUS_DRAFT = 'draft';

    public const STATUS_VALIDATING = 'validating';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_ERROR = 'error';

    protected $fillable = [
        'submission_type',
        'year',
        'submittable_type',
        'submittable_id',
        'status',
        'altinn_instance_id',
        'altinn_reference',
        'submission_data',
        'validation_errors',
        'altinn_response',
        'submitted_at',
        'accepted_at',
        'rejected_at',
        'rejection_reason',
        'created_by',
        'submitted_by',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'submission_data' => 'array',
            'validation_errors' => 'array',
            'altinn_response' => 'array',
            'submitted_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    // Relationships

    public function submittable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    // Scopes

    public function scopeByType($query, string $type)
    {
        return $query->where('submission_type', $type);
    }

    public function scopeByYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    // Status helpers

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isValidating(): bool
    {
        return $this->status === self::STATUS_VALIDATING;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function hasError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_ERROR, self::STATUS_REJECTED]);
    }

    public function canBeSubmitted(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_ERROR, self::STATUS_REJECTED]);
    }

    // Workflow methods

    public function markAsValidating(): void
    {
        $this->update(['status' => self::STATUS_VALIDATING]);
    }

    public function markAsSubmitted(string $instanceId, ?string $reference = null): void
    {
        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'altinn_instance_id' => $instanceId,
            'altinn_reference' => $reference,
            'submitted_at' => now(),
            'submitted_by' => auth()->id(),
        ]);
    }

    public function markAsAccepted(?string $reference = null): void
    {
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'altinn_reference' => $reference ?? $this->altinn_reference,
            'accepted_at' => now(),
        ]);
    }

    public function markAsRejected(string $reason, ?array $errors = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'validation_errors' => $errors,
            'rejected_at' => now(),
        ]);
    }

    public function markAsError(string $error, ?array $response = null): void
    {
        $this->update([
            'status' => self::STATUS_ERROR,
            'rejection_reason' => $error,
            'altinn_response' => $response,
        ]);
    }

    // Attribute accessors

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Kladd',
            self::STATUS_VALIDATING => 'Validerer',
            self::STATUS_SUBMITTED => 'Innsendt',
            self::STATUS_ACCEPTED => 'Godkjent',
            self::STATUS_REJECTED => 'Avvist',
            self::STATUS_ERROR => 'Feil',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'zinc',
            self::STATUS_VALIDATING => 'amber',
            self::STATUS_SUBMITTED => 'blue',
            self::STATUS_ACCEPTED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_ERROR => 'red',
            default => 'zinc',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->submission_type) {
            self::TYPE_AKSJONAERREGISTER => 'Aksjonærregisteroppgaven',
            self::TYPE_SKATTEMELDING => 'Skattemelding',
            self::TYPE_ARSREGNSKAP => 'Årsregnskap',
            default => $this->submission_type,
        };
    }

    public function getDeadlineAttribute(): ?\Carbon\Carbon
    {
        $config = config("altinn.services.{$this->submission_type}");

        if (! $config) {
            return null;
        }

        // Deadline is for the following year (e.g., 2024 submission due in 2025)
        return \Carbon\Carbon::create(
            $this->year + 1,
            $config['deadline_month'],
            $config['deadline_day']
        );
    }

    public function getDaysUntilDeadlineAttribute(): ?int
    {
        $deadline = $this->deadline;

        if (! $deadline) {
            return null;
        }

        return now()->diffInDays($deadline, false);
    }

    public function getIsOverdueAttribute(): bool
    {
        $daysUntil = $this->days_until_deadline;

        return $daysUntil !== null && $daysUntil < 0;
    }

    // Static helpers

    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_AKSJONAERREGISTER => 'Aksjonærregisteroppgaven (RF-1086)',
            self::TYPE_SKATTEMELDING => 'Skattemelding (RF-1028)',
            self::TYPE_ARSREGNSKAP => 'Årsregnskap',
        ];
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Kladd',
            self::STATUS_VALIDATING => 'Validerer',
            self::STATUS_SUBMITTED => 'Innsendt',
            self::STATUS_ACCEPTED => 'Godkjent',
            self::STATUS_REJECTED => 'Avvist',
            self::STATUS_ERROR => 'Feil',
        ];
    }
}
