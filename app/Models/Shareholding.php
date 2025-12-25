<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shareholding extends Model
{
    use HasFactory;

    protected $fillable = [
        'shareholder_id',
        'share_class_id',
        'number_of_shares',
        'acquisition_cost',
        'cost_per_share',
        'acquired_date',
        'acquisition_type',
        'acquisition_reference',
        'disposed_date',
        'disposal_type',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'number_of_shares' => 'integer',
            'acquisition_cost' => 'decimal:2',
            'cost_per_share' => 'decimal:4',
            'acquired_date' => 'date',
            'disposed_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function shareholder(): BelongsTo
    {
        return $this->belongsTo(Shareholder::class);
    }

    public function shareClass(): BelongsTo
    {
        return $this->belongsTo(ShareClass::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDisposed($query)
    {
        return $query->where('is_active', false)->whereNotNull('disposed_date');
    }

    public function scopeByShareClass($query, int $shareClassId)
    {
        return $query->where('share_class_id', $shareClassId);
    }

    public function scopeAcquiredBetween($query, $from, $to)
    {
        return $query->whereBetween('acquired_date', [$from, $to]);
    }

    public function scopeActiveAtDate($query, $date)
    {
        return $query->where('acquired_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('disposed_date')
                    ->orWhere('disposed_date', '>', $date);
            });
    }

    // Accessors
    public function getAcquisitionTypeLabel(): string
    {
        return match ($this->acquisition_type) {
            'foundation' => 'Stiftelse',
            'purchase' => 'Kjøp',
            'inheritance' => 'Arv',
            'gift' => 'Gave',
            'bonus' => 'Bonusemisjon',
            'split' => 'Aksjesplitt',
            default => $this->acquisition_type,
        };
    }

    public function getDisposalTypeLabel(): ?string
    {
        if (! $this->disposal_type) {
            return null;
        }

        return match ($this->disposal_type) {
            'sale' => 'Salg',
            'redemption' => 'Innløsning',
            'merger' => 'Fusjon',
            default => $this->disposal_type,
        };
    }

    public function getFormattedAcquisitionCost(): string
    {
        if (! $this->acquisition_cost) {
            return '-';
        }

        return number_format($this->acquisition_cost, 2, ',', ' ').' NOK';
    }

    public function getFormattedCostPerShare(): string
    {
        if (! $this->cost_per_share) {
            return '-';
        }

        return number_format($this->cost_per_share, 4, ',', ' ').' NOK';
    }

    // Business methods
    public function getCurrentValue(): float
    {
        return $this->number_of_shares * $this->shareClass->par_value;
    }

    public function getFormattedCurrentValue(): string
    {
        return number_format($this->getCurrentValue(), 2, ',', ' ').' NOK';
    }

    public function getOwnershipPercentageInClass(): float
    {
        if ($this->shareClass->total_shares === 0) {
            return 0;
        }

        return round(($this->number_of_shares / $this->shareClass->total_shares) * 100, 2);
    }

    public function dispose(string $disposalType, $disposedDate = null): void
    {
        $this->update([
            'disposed_date' => $disposedDate ?? now(),
            'disposal_type' => $disposalType,
            'is_active' => false,
        ]);
    }

    public function calculateCostPerShare(): float
    {
        if ($this->number_of_shares === 0) {
            return 0;
        }

        return $this->acquisition_cost / $this->number_of_shares;
    }
}
