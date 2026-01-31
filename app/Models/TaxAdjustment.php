<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxAdjustment extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'fiscal_year',
        'adjustment_type',
        'category',
        'description',
        'account_id',
        'accounting_amount',
        'tax_amount',
        'difference',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'accounting_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'difference' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->difference = $model->accounting_amount - $model->tax_amount;
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

    public function scopePermanent($query)
    {
        return $query->where('adjustment_type', 'permanent');
    }

    public function scopeTemporary($query)
    {
        return $query->whereIn('adjustment_type', ['temporary_deductible', 'temporary_taxable']);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
    public function getAdjustmentTypeLabel(): string
    {
        return match ($this->adjustment_type) {
            'permanent' => 'Permanent forskjell',
            'temporary_deductible' => 'Midlertidig fradragsberettiget',
            'temporary_taxable' => 'Midlertidig skattepliktig',
            default => $this->adjustment_type,
        };
    }

    public function getTypeLabel(): string
    {
        return match ($this->adjustment_type) {
            'permanent' => 'Permanent',
            'temporary_deductible' => 'Midl. (fradrag)',
            'temporary_taxable' => 'Midl. (skattbar)',
            default => $this->adjustment_type,
        };
    }

    public function getTypeBadgeColor(): string
    {
        return match ($this->adjustment_type) {
            'permanent' => 'warning',
            'temporary_deductible' => 'success',
            'temporary_taxable' => 'danger',
            default => 'outline',
        };
    }

    public function getCategoryLabel(): string
    {
        return match ($this->category) {
            'entertainment' => 'Representasjon',
            'fines' => 'Bøter og gebyrer',
            'unrealized_gains' => 'Urealiserte gevinster',
            'unrealized_losses' => 'Urealiserte tap',
            'depreciation_difference' => 'Avskrivningsforskjell',
            'provisions' => 'Avsetninger',
            'warranty' => 'Garantiforpliktelser',
            'bad_debts' => 'Tap på fordringer',
            'other' => 'Annet',
            default => $this->category,
        };
    }

    public function getFormattedDifference(): string
    {
        $prefix = $this->difference >= 0 ? '+' : '';

        return $prefix.number_format($this->difference, 2, ',', ' ').' NOK';
    }

    public function isPermanent(): bool
    {
        return $this->adjustment_type === 'permanent';
    }

    public function isTemporary(): bool
    {
        return in_array($this->adjustment_type, ['temporary_deductible', 'temporary_taxable']);
    }

    public function isDeductible(): bool
    {
        return $this->adjustment_type === 'temporary_deductible';
    }

    public function isTaxable(): bool
    {
        return $this->adjustment_type === 'temporary_taxable';
    }
}
