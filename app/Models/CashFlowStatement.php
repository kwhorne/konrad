<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashFlowStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'annual_account_id',
        // Operasjonelle aktiviteter
        'profit_before_tax',
        'tax_paid',
        'depreciation',
        'change_in_inventory',
        'change_in_receivables',
        'change_in_payables',
        'other_operating_items',
        'net_operating_cash_flow',
        // Investeringsaktiviteter
        'purchase_of_fixed_assets',
        'sale_of_fixed_assets',
        'purchase_of_investments',
        'sale_of_investments',
        'other_investing_items',
        'net_investing_cash_flow',
        // Finansieringsaktiviteter
        'proceeds_from_borrowings',
        'repayment_of_borrowings',
        'share_capital_increase',
        'dividends_paid',
        'other_financing_items',
        'net_financing_cash_flow',
        // Netto endring
        'net_change_in_cash',
        'opening_cash_balance',
        'closing_cash_balance',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'profit_before_tax' => 'decimal:2',
            'tax_paid' => 'decimal:2',
            'depreciation' => 'decimal:2',
            'change_in_inventory' => 'decimal:2',
            'change_in_receivables' => 'decimal:2',
            'change_in_payables' => 'decimal:2',
            'other_operating_items' => 'decimal:2',
            'net_operating_cash_flow' => 'decimal:2',
            'purchase_of_fixed_assets' => 'decimal:2',
            'sale_of_fixed_assets' => 'decimal:2',
            'purchase_of_investments' => 'decimal:2',
            'sale_of_investments' => 'decimal:2',
            'other_investing_items' => 'decimal:2',
            'net_investing_cash_flow' => 'decimal:2',
            'proceeds_from_borrowings' => 'decimal:2',
            'repayment_of_borrowings' => 'decimal:2',
            'share_capital_increase' => 'decimal:2',
            'dividends_paid' => 'decimal:2',
            'other_financing_items' => 'decimal:2',
            'net_financing_cash_flow' => 'decimal:2',
            'net_change_in_cash' => 'decimal:2',
            'opening_cash_balance' => 'decimal:2',
            'closing_cash_balance' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->calculateTotals();
        });
    }

    // Relationships
    public function annualAccount(): BelongsTo
    {
        return $this->belongsTo(AnnualAccount::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Calculations
    public function calculateTotals(): void
    {
        // Operasjonelle aktiviteter (indirekte metode)
        $this->net_operating_cash_flow = $this->profit_before_tax
            - $this->tax_paid
            + $this->depreciation
            - $this->change_in_inventory
            - $this->change_in_receivables
            + $this->change_in_payables
            + $this->other_operating_items;

        // Investeringsaktiviteter
        $this->net_investing_cash_flow = -$this->purchase_of_fixed_assets
            + $this->sale_of_fixed_assets
            - $this->purchase_of_investments
            + $this->sale_of_investments
            + $this->other_investing_items;

        // Finansieringsaktiviteter
        $this->net_financing_cash_flow = $this->proceeds_from_borrowings
            - $this->repayment_of_borrowings
            + $this->share_capital_increase
            - $this->dividends_paid
            + $this->other_financing_items;

        // Netto endring
        $this->net_change_in_cash = $this->net_operating_cash_flow
            + $this->net_investing_cash_flow
            + $this->net_financing_cash_flow;

        // Utgående saldo
        $this->closing_cash_balance = $this->opening_cash_balance + $this->net_change_in_cash;
    }

    // Accessors
    public function getFormattedNetOperating(): string
    {
        return number_format($this->net_operating_cash_flow, 0, ',', ' ').' kr';
    }

    public function getFormattedNetInvesting(): string
    {
        return number_format($this->net_investing_cash_flow, 0, ',', ' ').' kr';
    }

    public function getFormattedNetFinancing(): string
    {
        return number_format($this->net_financing_cash_flow, 0, ',', ' ').' kr';
    }

    public function getFormattedNetChange(): string
    {
        return number_format($this->net_change_in_cash, 0, ',', ' ').' kr';
    }

    // Validation
    public function isBalanced(): bool
    {
        $calculatedClosing = $this->opening_cash_balance + $this->net_change_in_cash;

        return abs($calculatedClosing - $this->closing_cash_balance) < 0.01;
    }

    public function getOperatingItems(): array
    {
        return [
            ['label' => 'Resultat før skatt', 'amount' => $this->profit_before_tax, 'type' => 'add'],
            ['label' => 'Betalt skatt', 'amount' => -$this->tax_paid, 'type' => 'subtract'],
            ['label' => 'Avskrivninger', 'amount' => $this->depreciation, 'type' => 'add'],
            ['label' => 'Endring i varelager', 'amount' => -$this->change_in_inventory, 'type' => 'adjust'],
            ['label' => 'Endring i kundefordringer', 'amount' => -$this->change_in_receivables, 'type' => 'adjust'],
            ['label' => 'Endring i leverandørgjeld', 'amount' => $this->change_in_payables, 'type' => 'adjust'],
            ['label' => 'Andre poster', 'amount' => $this->other_operating_items, 'type' => 'adjust'],
        ];
    }

    public function getInvestingItems(): array
    {
        return [
            ['label' => 'Kjøp av driftsmidler', 'amount' => -$this->purchase_of_fixed_assets, 'type' => 'outflow'],
            ['label' => 'Salg av driftsmidler', 'amount' => $this->sale_of_fixed_assets, 'type' => 'inflow'],
            ['label' => 'Kjøp av investeringer', 'amount' => -$this->purchase_of_investments, 'type' => 'outflow'],
            ['label' => 'Salg av investeringer', 'amount' => $this->sale_of_investments, 'type' => 'inflow'],
            ['label' => 'Andre poster', 'amount' => $this->other_investing_items, 'type' => 'adjust'],
        ];
    }

    public function getFinancingItems(): array
    {
        return [
            ['label' => 'Opptak av lån', 'amount' => $this->proceeds_from_borrowings, 'type' => 'inflow'],
            ['label' => 'Nedbetaling av lån', 'amount' => -$this->repayment_of_borrowings, 'type' => 'outflow'],
            ['label' => 'Kapitalforhøyelse', 'amount' => $this->share_capital_increase, 'type' => 'inflow'],
            ['label' => 'Utbetalt utbytte', 'amount' => -$this->dividends_paid, 'type' => 'outflow'],
            ['label' => 'Andre poster', 'amount' => $this->other_financing_items, 'type' => 'adjust'],
        ];
    }
}
