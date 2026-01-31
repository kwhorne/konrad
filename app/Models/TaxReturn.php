<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxReturn extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    public const TAX_RATE = 22.00; // Norsk selskapsskatt 22%

    protected $fillable = [
        'fiscal_year',
        'period_start',
        'period_end',
        'accounting_profit',
        'permanent_differences',
        'temporary_differences_change',
        'taxable_income',
        'tax_rate',
        'tax_payable',
        'deferred_tax_change',
        'total_tax_expense',
        'losses_brought_forward',
        'losses_used',
        'losses_carried_forward',
        'status',
        'calculation_details',
        'notes',
        'created_by',
        'altinn_submission_id',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
            'accounting_profit' => 'decimal:2',
            'permanent_differences' => 'decimal:2',
            'temporary_differences_change' => 'decimal:2',
            'taxable_income' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_payable' => 'decimal:2',
            'deferred_tax_change' => 'decimal:2',
            'total_tax_expense' => 'decimal:2',
            'losses_brought_forward' => 'decimal:2',
            'losses_used' => 'decimal:2',
            'losses_carried_forward' => 'decimal:2',
            'calculation_details' => 'array',
        ];
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function altinnSubmission(): BelongsTo
    {
        return $this->belongsTo(AltinnSubmission::class);
    }

    public function submission(): MorphOne
    {
        return $this->morphOne(AltinnSubmission::class, 'submittable');
    }

    // Scopes
    public function scopeForYear($query, int $year)
    {
        return $query->where('fiscal_year', $year);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('fiscal_year', 'desc');
    }

    // Accessors
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Utkast',
            'ready' => 'Klar for innsending',
            'submitted' => 'Sendt inn',
            default => $this->status,
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'draft' => 'warning',
            'ready' => 'info',
            'submitted' => 'success',
            default => 'outline',
        };
    }

    public function getFormattedAccountingProfit(): string
    {
        return number_format($this->accounting_profit, 2, ',', ' ').' NOK';
    }

    public function getFormattedTaxableIncome(): string
    {
        return number_format($this->taxable_income, 2, ',', ' ').' NOK';
    }

    public function getFormattedTaxPayable(): string
    {
        return number_format($this->tax_payable, 2, ',', ' ').' NOK';
    }

    public function getFormattedTotalTaxExpense(): string
    {
        return number_format($this->total_tax_expense, 2, ',', ' ').' NOK';
    }

    public function getReportPeriod(): string
    {
        return $this->period_start->format('d.m.Y').' - '.$this->period_end->format('d.m.Y');
    }

    public function getDeadline(): \Carbon\Carbon
    {
        // Skattemelding frist: 31. mai året etter regnskapsåret
        return \Carbon\Carbon::create($this->fiscal_year + 1, 5, 31);
    }

    public function getDaysUntilDeadline(): int
    {
        return now()->diffInDays($this->getDeadline(), false);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'submitted' && now()->isAfter($this->getDeadline());
    }

    // Status methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'ready']);
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === 'ready';
    }

    public function markAsReady(): void
    {
        $this->update(['status' => 'ready']);
    }

    public function markAsSubmitted(): void
    {
        $this->update(['status' => 'submitted']);
    }

    public function markAsDraft(): void
    {
        $this->update(['status' => 'draft']);
    }

    // Calculation methods
    public function calculateTax(): void
    {
        // Beregn skattepliktig inntekt
        $taxableBeforeLosses = $this->accounting_profit + $this->permanent_differences + $this->temporary_differences_change;

        // Bruk av fremførbart underskudd
        if ($taxableBeforeLosses > 0 && $this->losses_brought_forward > 0) {
            $this->losses_used = min($taxableBeforeLosses, $this->losses_brought_forward);
            $this->taxable_income = $taxableBeforeLosses - $this->losses_used;
            $this->losses_carried_forward = $this->losses_brought_forward - $this->losses_used;
        } else {
            $this->losses_used = 0;
            $this->taxable_income = max(0, $taxableBeforeLosses);
            $this->losses_carried_forward = $this->losses_brought_forward + ($taxableBeforeLosses < 0 ? abs($taxableBeforeLosses) : 0);
        }

        // Beregn skatt
        $this->tax_payable = $this->taxable_income * ($this->tax_rate / 100);
        $this->total_tax_expense = $this->tax_payable + $this->deferred_tax_change;
    }

    public function getEffectiveTaxRate(): float
    {
        if ($this->accounting_profit == 0) {
            return 0;
        }

        return round(($this->total_tax_expense / $this->accounting_profit) * 100, 2);
    }
}
