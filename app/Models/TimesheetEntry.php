<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetEntry extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'timesheet_id',
        'entry_date',
        'hours',
        'project_id',
        'work_order_id',
        'description',
        'is_billable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'hours' => 'decimal:2',
            'is_billable' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (TimesheetEntry $entry) {
            $entry->timesheet->recalculateTotalHours();
        });

        static::deleted(function (TimesheetEntry $entry) {
            // Load fresh to avoid relationship caching issues
            $timesheet = Timesheet::withoutGlobalScopes()->find($entry->timesheet_id);
            $timesheet?->recalculateTotalHours();
        });
    }

    // Relasjoner

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    // Accessors

    public function getTargetLabelAttribute(): string
    {
        if ($this->workOrder) {
            return $this->workOrder->work_order_number.': '.$this->workOrder->title;
        }

        if ($this->project) {
            return $this->project->project_number.': '.$this->project->name;
        }

        return $this->description ?: 'Uspesifisert';
    }

    public function getTargetTypeAttribute(): ?string
    {
        if ($this->work_order_id) {
            return 'work_order';
        }

        if ($this->project_id) {
            return 'project';
        }

        return null;
    }

    public function getDayOfWeekAttribute(): string
    {
        return $this->entry_date->isoFormat('ddd');
    }

    // Scopes

    public function scopeForDate($query, $date)
    {
        return $query->where('entry_date', $date);
    }

    public function scopeBillable($query)
    {
        return $query->where('is_billable', true);
    }

    public function scopeNonBillable($query)
    {
        return $query->where('is_billable', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('entry_date');
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForWorkOrder($query, $workOrderId)
    {
        return $query->where('work_order_id', $workOrderId);
    }
}
