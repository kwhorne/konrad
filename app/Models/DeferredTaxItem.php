<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeferredTaxItem extends Model
{
    use HasFactory;

    public const TAX_RATE = 0.22; // 22% norsk selskapsskatt

    protected $fillable = [
        'fiscal_year',
        'item_type',
        'category',
        'description',
        'account_id',
        'accounting_value',
        'tax_value',
        'temporary_difference',
        'deferred_tax',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'accounting_value' => 'decimal:2',
            'tax_value' => 'decimal:2',
            'temporary_difference' => 'decimal:2',
            'deferred_tax' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->temporary_difference = $model->accounting_value - $model->tax_value;
            $model->deferred_tax = abs($model->temporary_difference) * self::TAX_RATE;
        });
    }

    // Relationships
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeForYear($query, int $year)
    {
        return $query->where('fiscal_year', $year);
    }

    public function scopeAssets($query)
    {
        return $query->where('item_type', 'asset');
    }

    public function scopeLiabilities($query)
    {
        return $query->where('item_type', 'liability');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
    public function getItemTypeLabel(): string
    {
        return match ($this->item_type) {
            'asset' => 'Eiendel',
            'liability' => 'Gjeld',
            default => $this->item_type,
        };
    }

    public function getCategoryLabel(): string
    {
        return match ($this->category) {
            'fixed_assets' => 'Varige driftsmidler',
            'receivables' => 'Fordringer',
            'provisions' => 'Avsetninger',
            'losses_carried_forward' => 'Fremførbart underskudd',
            'inventory' => 'Varelager',
            'financial_instruments' => 'Finansielle instrumenter',
            'other' => 'Annet',
            default => $this->category,
        };
    }

    public function getFormattedTemporaryDifference(): string
    {
        return number_format($this->temporary_difference, 2, ',', ' ').' NOK';
    }

    public function getFormattedDeferredTax(): string
    {
        return number_format($this->deferred_tax, 2, ',', ' ').' NOK';
    }

    public function isDeferredTaxAsset(): bool
    {
        // Utsatt skattefordel: Regnskapsmessig verdi < Skattemessig verdi (for eiendeler)
        // eller Regnskapsmessig verdi > Skattemessig verdi (for gjeld)
        if ($this->item_type === 'asset') {
            return $this->accounting_value < $this->tax_value;
        }

        return $this->accounting_value > $this->tax_value;
    }

    public function isDeferredTaxLiability(): bool
    {
        return ! $this->isDeferredTaxAsset();
    }

    public function getSignedDeferredTax(): float
    {
        return $this->isDeferredTaxAsset() ? -$this->deferred_tax : $this->deferred_tax;
    }
}
