<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'contact_id',
        'activity_type_id',
        'subject',
        'description',
        'is_completed',
        'due_date',
        'completed_at',
        'created_by',
        'assigned_to',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_completed', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function markAsIncomplete(): void
    {
        $this->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }

    public function isOverdue(): bool
    {
        return ! $this->is_completed
            && $this->due_date
            && $this->due_date->isPast();
    }

    public function getStatusLabel(): string
    {
        if ($this->is_completed) {
            return 'Utført';
        }

        if ($this->isOverdue()) {
            return 'Forfalt';
        }

        return 'Ikke utført';
    }

    public function getStatusColor(): string
    {
        if ($this->is_completed) {
            return 'success';
        }

        if ($this->isOverdue()) {
            return 'danger';
        }

        return 'warning';
    }
}
