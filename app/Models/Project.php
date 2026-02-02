<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'project_number',
        'name',
        'description',
        'contact_id',
        'manager_id',
        'project_type_id',
        'project_status_id',
        'start_date',
        'end_date',
        'budget',
        'estimated_hours',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->project_number)) {
                $project->project_number = static::generateProjectNumber();
            }
        });
    }

    public static function generateProjectNumber(): string
    {
        $year = date('Y');
        $lastProject = static::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        if ($lastProject && preg_match('/P-'.$year.'-(\d+)/', $lastProject->project_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('P-%s-%04d', $year, $nextNumber);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    public function projectStatus(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ProjectLine::class)->orderBy('sort_order');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ProjectAttachment::class)->orderByDesc('created_at');
    }

    public function getTotalAttribute(): float
    {
        return $this->lines->sum(fn ($line) => $line->line_total);
    }

    public function getBudgetVarianceAttribute(): ?float
    {
        if ($this->budget === null) {
            return null;
        }

        return $this->budget - $this->total;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }
}
