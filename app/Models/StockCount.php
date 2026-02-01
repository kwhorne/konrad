<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockCount extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'count_number',
        'stock_location_id',
        'count_date',
        'description',
        'status',
        'notes',
        'total_expected_value',
        'total_counted_value',
        'total_variance_value',
        'created_by',
        'completed_by',
        'completed_at',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'count_date' => 'date',
        'completed_at' => 'datetime',
        'posted_at' => 'datetime',
        'total_expected_value' => 'decimal:2',
        'total_counted_value' => 'decimal:2',
        'total_variance_value' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (StockCount $count) {
            if (empty($count->count_number)) {
                $count->count_number = static::generateCountNumber();
            }
            if (empty($count->count_date)) {
                $count->count_date = now();
            }
        });
    }

    public static function generateCountNumber(): string
    {
        $prefix = 'VT';
        $year = date('Y');

        $lastCount = static::withTrashed()
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $pattern = '/'.$prefix.'-'.$year.'-(\d+)/';
        if ($lastCount && preg_match($pattern, $lastCount->count_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $nextNumber);
    }

    public function stockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(StockCountLine::class)->orderBy('sort_order');
    }

    public function getProgressAttribute(): array
    {
        $total = $this->lines->count();
        $counted = $this->lines->where('is_counted', true)->count();

        return [
            'total' => $total,
            'counted' => $counted,
            'remaining' => $total - $counted,
            'percentage' => $total > 0 ? round(($counted / $total) * 100) : 0,
        ];
    }

    public function getTotalVarianceQuantityAttribute(): float
    {
        return $this->lines->sum('variance_quantity') ?? 0;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Utkast',
            'in_progress' => 'Pagar',
            'completed' => 'Fullfort',
            'posted' => 'Bokfort',
            'cancelled' => 'Kansellert',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'zinc',
            'in_progress' => 'blue',
            'completed' => 'amber',
            'posted' => 'green',
            'cancelled' => 'red',
            default => 'zinc',
        };
    }

    public function getCanEditAttribute(): bool
    {
        return in_array($this->status, ['draft', 'in_progress']);
    }

    public function getCanStartAttribute(): bool
    {
        return $this->status === 'draft' && $this->lines->isNotEmpty();
    }

    public function getCanCompleteAttribute(): bool
    {
        return $this->status === 'in_progress' && $this->lines->every(fn ($line) => $line->is_counted);
    }

    public function getCanPostAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getCanCancelAttribute(): bool
    {
        return in_array($this->status, ['draft', 'in_progress']);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('count_date')->orderByDesc('id');
    }

    public function recalculateTotals(): void
    {
        $this->total_expected_value = $this->lines->sum('expected_value');
        $this->total_counted_value = $this->lines->sum('counted_value') ?? 0;
        $this->total_variance_value = $this->lines->sum('variance_value') ?? 0;
        $this->save();
    }
}
