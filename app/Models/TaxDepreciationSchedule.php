<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxDepreciationSchedule extends Model
{
    use HasFactory;

    // Standard norske saldogrupper med avskrivningssatser
    public const DEPRECIATION_GROUPS = [
        'a' => ['name' => 'Kontormaskiner o.l.', 'rate' => 30.00],
        'b' => ['name' => 'Ervervet goodwill', 'rate' => 20.00],
        'c' => ['name' => 'Vogntog, lastebiler, busser m.v.', 'rate' => 20.00],
        'd' => ['name' => 'Personbiler, maskiner og inventar', 'rate' => 20.00],
        'e' => ['name' => 'Skip, rigger m.v.', 'rate' => 14.00],
        'f' => ['name' => 'Fly og helikopter', 'rate' => 12.00],
        'g' => ['name' => 'Anleggsmaskiner', 'rate' => 10.00],
        'h' => ['name' => 'Bygg og anlegg, hoteller m.v.', 'rate' => 4.00],
        'i' => ['name' => 'Forretningsbygg', 'rate' => 2.00],
        'j' => ['name' => 'Tekniske installasjoner', 'rate' => 10.00],
    ];

    protected $fillable = [
        'fiscal_year',
        'depreciation_group',
        'group_name',
        'depreciation_rate',
        'opening_balance',
        'additions',
        'disposals',
        'basis_for_depreciation',
        'depreciation_amount',
        'closing_balance',
        'gain_loss_account',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'depreciation_rate' => 'decimal:2',
            'opening_balance' => 'decimal:2',
            'additions' => 'decimal:2',
            'disposals' => 'decimal:2',
            'basis_for_depreciation' => 'decimal:2',
            'depreciation_amount' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'gain_loss_account' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->calculateDepreciation();
        });
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeForYear($query, int $year)
    {
        return $query->where('fiscal_year', $year);
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('depreciation_group', $group);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('depreciation_group');
    }

    // Calculations
    public function calculateDepreciation(): void
    {
        // Avskrivningsgrunnlag = IB + Tilgang - Avgang
        $this->basis_for_depreciation = $this->opening_balance + $this->additions - $this->disposals;

        // Maksimalt tillatt avskrivning
        $maxDepreciation = $this->basis_for_depreciation * ($this->depreciation_rate / 100);

        // Avskrivningsbeløp kan ikke være høyere enn grunnlaget
        $this->depreciation_amount = min($maxDepreciation, max(0, $this->basis_for_depreciation));

        // UB = Grunnlag - Avskrivning
        $this->closing_balance = $this->basis_for_depreciation - $this->depreciation_amount;
    }

    // Accessors
    public function getFormattedOpeningBalance(): string
    {
        return number_format($this->opening_balance, 2, ',', ' ').' NOK';
    }

    public function getFormattedClosingBalance(): string
    {
        return number_format($this->closing_balance, 2, ',', ' ').' NOK';
    }

    public function getFormattedDepreciation(): string
    {
        return number_format($this->depreciation_amount, 2, ',', ' ').' NOK';
    }

    public function getDepreciationRateFormatted(): string
    {
        return number_format($this->depreciation_rate, 0).'%';
    }

    // Static helpers
    public static function getGroupInfo(string $group): ?array
    {
        return self::DEPRECIATION_GROUPS[$group] ?? null;
    }

    public static function getDefaultRate(string $group): float
    {
        return self::DEPRECIATION_GROUPS[$group]['rate'] ?? 0;
    }

    public static function getGroupName(string $group): string
    {
        return self::DEPRECIATION_GROUPS[$group]['name'] ?? $group;
    }

    public function getGroupNameAttribute(): string
    {
        return self::DEPRECIATION_GROUPS[$this->depreciation_group]['name'] ?? $this->depreciation_group;
    }

    public static function initializeForYear(int $year, int $createdBy): void
    {
        // Get previous year's closing balances
        $previousYear = $year - 1;
        $previousSchedules = self::forYear($previousYear)->get()->keyBy('depreciation_group');

        foreach (self::DEPRECIATION_GROUPS as $group => $info) {
            $openingBalance = 0;
            if ($previousSchedules->has($group)) {
                $openingBalance = $previousSchedules[$group]->closing_balance;
            }

            self::firstOrCreate(
                ['fiscal_year' => $year, 'depreciation_group' => $group],
                [
                    'group_name' => $info['name'],
                    'depreciation_rate' => $info['rate'],
                    'opening_balance' => $openingBalance,
                    'additions' => 0,
                    'disposals' => 0,
                    'basis_for_depreciation' => $openingBalance,
                    'depreciation_amount' => 0,
                    'closing_balance' => $openingBalance,
                    'gain_loss_account' => 0,
                    'created_by' => $createdBy,
                ]
            );
        }
    }
}
