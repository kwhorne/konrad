<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayType extends Model
{
    use BelongsToCompany, HasFactory;

    public const CATEGORY_FASTLONN = 'fastlonn';

    public const CATEGORY_TIMELONN = 'timelonn';

    public const CATEGORY_OVERTID = 'overtid';

    public const CATEGORY_BONUS = 'bonus';

    public const CATEGORY_TILLEGG = 'tillegg';

    public const CATEGORY_TREKK = 'trekk';

    public const CATEGORY_NATURALYTELSE = 'naturalytelse';

    public const CATEGORY_UTGIFTSGODTGJORELSE = 'utgiftsgodtgjorelse';

    protected $fillable = [
        'code',
        'name',
        'category',
        'is_taxable',
        'is_aga_basis',
        'is_vacation_basis',
        'is_otp_basis',
        'default_rate',
        'overtid_faktor',
        'a_melding_code',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_taxable' => 'boolean',
            'is_aga_basis' => 'boolean',
            'is_vacation_basis' => 'boolean',
            'is_otp_basis' => 'boolean',
            'default_rate' => 'decimal:2',
            'overtid_faktor' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get all payroll entry lines using this pay type.
     */
    public function entryLines(): HasMany
    {
        return $this->hasMany(PayrollEntryLine::class);
    }

    /**
     * Check if this pay type adds to salary (not a deduction).
     */
    public function getIsAdditionAttribute(): bool
    {
        return $this->category !== self::CATEGORY_TREKK;
    }

    /**
     * Get category label in Norwegian.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            self::CATEGORY_FASTLONN => 'Fastlonn',
            self::CATEGORY_TIMELONN => 'Timelonn',
            self::CATEGORY_OVERTID => 'Overtid',
            self::CATEGORY_BONUS => 'Bonus/provisjon',
            self::CATEGORY_TILLEGG => 'Tillegg',
            self::CATEGORY_TREKK => 'Trekk',
            self::CATEGORY_NATURALYTELSE => 'Naturalytelse',
            self::CATEGORY_UTGIFTSGODTGJORELSE => 'Utgiftsgodtgjorelse',
            default => 'Ukjent',
        };
    }

    /**
     * Scope to get only active pay types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }

    /**
     * Scope to filter by category.
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
