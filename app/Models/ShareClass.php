<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShareClass extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'name',
        'code',
        'isin',
        'par_value',
        'total_shares',
        'has_voting_rights',
        'has_dividend_rights',
        'voting_weight',
        'restrictions',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'par_value' => 'decimal:2',
            'total_shares' => 'integer',
            'has_voting_rights' => 'boolean',
            'has_dividend_rights' => 'boolean',
            'voting_weight' => 'decimal:2',
            'restrictions' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Relationships
    public function shareholdings(): HasMany
    {
        return $this->hasMany(Shareholding::class);
    }

    public function activeShareholdings(): HasMany
    {
        return $this->hasMany(Shareholding::class)->where('is_active', true);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ShareTransaction::class);
    }

    public function dividends(): HasMany
    {
        return $this->hasMany(Dividend::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithVotingRights($query)
    {
        return $query->where('has_voting_rights', true);
    }

    public function scopeWithDividendRights($query)
    {
        return $query->where('has_dividend_rights', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }

    // Accessors
    public function getDisplayName(): string
    {
        return "{$this->code} - {$this->name}";
    }

    public function getFormattedParValue(): string
    {
        return number_format($this->par_value, 2, ',', ' ').' NOK';
    }

    public function getTotalCapital(): float
    {
        return $this->total_shares * $this->par_value;
    }

    public function getFormattedTotalCapital(): string
    {
        return number_format($this->getTotalCapital(), 2, ',', ' ').' NOK';
    }

    public function getRightsDescription(): string
    {
        $rights = [];

        if ($this->has_voting_rights) {
            $weight = $this->voting_weight != 1
                ? " ({$this->voting_weight}x)"
                : '';
            $rights[] = "Stemmerett{$weight}";
        }

        if ($this->has_dividend_rights) {
            $rights[] = 'Utbytterett';
        }

        return implode(', ', $rights) ?: 'Ingen særskilte rettigheter';
    }

    // Business methods
    public function getShareholderCount(): int
    {
        return $this->activeShareholdings()
            ->distinct('shareholder_id')
            ->count('shareholder_id');
    }

    public function getAllocatedShares(): int
    {
        return $this->activeShareholdings()->sum('number_of_shares');
    }

    public function getUnallocatedShares(): int
    {
        return max(0, $this->total_shares - $this->getAllocatedShares());
    }

    public function getTotalVotingPower(): float
    {
        if (! $this->has_voting_rights) {
            return 0;
        }

        return $this->total_shares * $this->voting_weight;
    }

    public function recalculateTotalShares(): void
    {
        $this->total_shares = $this->activeShareholdings()->sum('number_of_shares');
        $this->save();
    }
}
