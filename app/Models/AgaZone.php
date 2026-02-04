<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgaZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'rate',
        'fribeloep',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'fribeloep' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the rate as a percentage factor.
     */
    public function getRateFactorAttribute(): float
    {
        return $this->rate / 100;
    }

    /**
     * Scope to get only active zones.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get zone by code.
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
