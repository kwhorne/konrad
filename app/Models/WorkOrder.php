<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_number',
        'title',
        'description',
        'contact_id',
        'project_id',
        'work_order_type_id',
        'work_order_status_id',
        'work_order_priority_id',
        'assigned_to',
        'created_by',
        'scheduled_date',
        'due_date',
        'completed_at',
        'estimated_hours',
        'budget',
        'internal_notes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'budget' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (WorkOrder $workOrder) {
            if (empty($workOrder->work_order_number)) {
                $workOrder->work_order_number = static::generateWorkOrderNumber();
            }
        });
    }

    public static function generateWorkOrderNumber(): string
    {
        $year = date('Y');
        $lastOrder = static::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        if ($lastOrder && preg_match('/WO-'.$year.'-(\d+)/', $lastOrder->work_order_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('WO-%s-%04d', $year, $nextNumber);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function workOrderType(): BelongsTo
    {
        return $this->belongsTo(WorkOrderType::class);
    }

    public function workOrderStatus(): BelongsTo
    {
        return $this->belongsTo(WorkOrderStatus::class);
    }

    public function workOrderPriority(): BelongsTo
    {
        return $this->belongsTo(WorkOrderPriority::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(WorkOrderLine::class)->orderBy('sort_order');
    }

    public function getTotalHoursAttribute(): float
    {
        return $this->lines->where('line_type', 'time')->sum('quantity');
    }

    public function getTotalAmountAttribute(): float
    {
        return $this->lines->sum(fn ($line) => $line->line_total);
    }

    public function getBudgetVarianceAttribute(): ?float
    {
        if ($this->budget === null) {
            return null;
        }

        return $this->budget - $this->total_amount;
    }

    public function getIsOverdueAttribute(): bool
    {
        if (! $this->due_date || $this->completed_at) {
            return false;
        }

        return $this->due_date->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    public function scopeOverdue($query)
    {
        return $query->whereNull('completed_at')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    public function scopePending($query)
    {
        return $query->whereNull('completed_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }
}
