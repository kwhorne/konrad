<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VatCode extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'direction',
        'rate',
        'affects_base',
        'affects_tax',
        'sign',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'affects_base' => 'boolean',
        'affects_tax' => 'boolean',
        'sign' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function reportLines(): HasMany
    {
        return $this->hasMany(VatReportLine::class);
    }

    public function getCategoryNameAttribute(): string
    {
        return match ($this->category) {
            'salg_norge' => 'Salg av varer og tjenester i Norge',
            'kjop_norge' => 'Kjøp av varer og tjenester i Norge',
            'import' => 'Kjøp av tjenester fra utlandet (import)',
            'export' => 'Utførsel av varer og tjenester',
            'other' => 'Andre forhold',
            default => 'Ukjent',
        };
    }

    public function getDirectionNameAttribute(): string
    {
        return match ($this->direction) {
            'output' => 'Utgående',
            'input' => 'Inngående',
            default => 'Ukjent',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }
}
