<?php

namespace App\Livewire;

use App\Models\AnnualAccount;
use App\Models\CashFlowStatement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class CashFlowStatementManager extends Component
{
    use AuthorizesRequests;

    public $annualAccountId;

    public $showModal = false;

    // Operating activities
    public $profit_before_tax = 0;

    public $tax_paid = 0;

    public $depreciation = 0;

    public $change_in_inventory = 0;

    public $change_in_receivables = 0;

    public $change_in_payables = 0;

    public $other_operating_items = 0;

    // Investing activities
    public $purchase_of_fixed_assets = 0;

    public $sale_of_fixed_assets = 0;

    public $purchase_of_investments = 0;

    public $sale_of_investments = 0;

    public $other_investing_items = 0;

    // Financing activities
    public $proceeds_from_borrowings = 0;

    public $repayment_of_borrowings = 0;

    public $share_capital_increase = 0;

    public $dividends_paid = 0;

    public $other_financing_items = 0;

    // Cash balances
    public $opening_cash_balance = 0;

    public $notes = '';

    protected function rules(): array
    {
        return [
            'profit_before_tax' => 'required|numeric',
            'tax_paid' => 'required|numeric|min:0',
            'depreciation' => 'required|numeric|min:0',
            'change_in_inventory' => 'required|numeric',
            'change_in_receivables' => 'required|numeric',
            'change_in_payables' => 'required|numeric',
            'other_operating_items' => 'nullable|numeric',
            'purchase_of_fixed_assets' => 'required|numeric|min:0',
            'sale_of_fixed_assets' => 'required|numeric|min:0',
            'purchase_of_investments' => 'required|numeric|min:0',
            'sale_of_investments' => 'required|numeric|min:0',
            'other_investing_items' => 'nullable|numeric',
            'proceeds_from_borrowings' => 'required|numeric|min:0',
            'repayment_of_borrowings' => 'required|numeric|min:0',
            'share_capital_increase' => 'required|numeric|min:0',
            'dividends_paid' => 'required|numeric|min:0',
            'other_financing_items' => 'nullable|numeric',
            'opening_cash_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }

    public function mount($annualAccountId): void
    {
        $this->annualAccountId = $annualAccountId;
        $this->loadData();
    }

    private function loadData()
    {
        $annualAccount = AnnualAccount::with('cashFlowStatement')->findOrFail($this->annualAccountId);

        if ($cashFlow = $annualAccount->cashFlowStatement) {
            $this->profit_before_tax = $cashFlow->profit_before_tax;
            $this->tax_paid = $cashFlow->tax_paid;
            $this->depreciation = $cashFlow->depreciation;
            $this->change_in_inventory = $cashFlow->change_in_inventory;
            $this->change_in_receivables = $cashFlow->change_in_receivables;
            $this->change_in_payables = $cashFlow->change_in_payables;
            $this->other_operating_items = $cashFlow->other_operating_items;
            $this->purchase_of_fixed_assets = $cashFlow->purchase_of_fixed_assets;
            $this->sale_of_fixed_assets = $cashFlow->sale_of_fixed_assets;
            $this->purchase_of_investments = $cashFlow->purchase_of_investments;
            $this->sale_of_investments = $cashFlow->sale_of_investments;
            $this->other_investing_items = $cashFlow->other_investing_items;
            $this->proceeds_from_borrowings = $cashFlow->proceeds_from_borrowings;
            $this->repayment_of_borrowings = $cashFlow->repayment_of_borrowings;
            $this->share_capital_increase = $cashFlow->share_capital_increase;
            $this->dividends_paid = $cashFlow->dividends_paid;
            $this->other_financing_items = $cashFlow->other_financing_items;
            $this->opening_cash_balance = $cashFlow->opening_cash_balance;
            $this->notes = $cashFlow->notes ?? '';
        } else {
            // Initialize with profit from annual account
            $this->profit_before_tax = $annualAccount->profit_before_tax;
        }
    }

    public function openModal(): void
    {
        $this->loadData();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorize('update', AnnualAccount::findOrFail($this->annualAccountId));

        $this->validate();

        $annualAccount = AnnualAccount::findOrFail($this->annualAccountId);

        if (! $annualAccount->canBeEdited()) {
            session()->flash('error', 'Kan ikke redigere innsendt årsregnskap.');
            $this->closeModal();

            return;
        }

        $data = [
            'annual_account_id' => $this->annualAccountId,
            'profit_before_tax' => $this->profit_before_tax,
            'tax_paid' => $this->tax_paid,
            'depreciation' => $this->depreciation,
            'change_in_inventory' => $this->change_in_inventory,
            'change_in_receivables' => $this->change_in_receivables,
            'change_in_payables' => $this->change_in_payables,
            'other_operating_items' => $this->other_operating_items ?? 0,
            'purchase_of_fixed_assets' => $this->purchase_of_fixed_assets,
            'sale_of_fixed_assets' => $this->sale_of_fixed_assets,
            'purchase_of_investments' => $this->purchase_of_investments,
            'sale_of_investments' => $this->sale_of_investments,
            'other_investing_items' => $this->other_investing_items ?? 0,
            'proceeds_from_borrowings' => $this->proceeds_from_borrowings,
            'repayment_of_borrowings' => $this->repayment_of_borrowings,
            'share_capital_increase' => $this->share_capital_increase,
            'dividends_paid' => $this->dividends_paid,
            'other_financing_items' => $this->other_financing_items ?? 0,
            'opening_cash_balance' => $this->opening_cash_balance,
            'notes' => $this->notes ?: null,
            'created_by' => auth()->id(),
        ];

        CashFlowStatement::updateOrCreate(
            ['annual_account_id' => $this->annualAccountId],
            $data
        );

        session()->flash('success', 'Kontantstrømoppstillingen ble lagret.');
        $this->closeModal();
    }

    public function getCalculatedTotals(): array
    {
        // Operating cash flow
        $netOperating = $this->profit_before_tax
            - $this->tax_paid
            + $this->depreciation
            - $this->change_in_inventory
            - $this->change_in_receivables
            + $this->change_in_payables
            + ($this->other_operating_items ?? 0);

        // Investing cash flow
        $netInvesting = -$this->purchase_of_fixed_assets
            + $this->sale_of_fixed_assets
            - $this->purchase_of_investments
            + $this->sale_of_investments
            + ($this->other_investing_items ?? 0);

        // Financing cash flow
        $netFinancing = $this->proceeds_from_borrowings
            - $this->repayment_of_borrowings
            + $this->share_capital_increase
            - $this->dividends_paid
            + ($this->other_financing_items ?? 0);

        // Net change
        $netChange = $netOperating + $netInvesting + $netFinancing;
        $closingBalance = $this->opening_cash_balance + $netChange;

        return [
            'net_operating' => $netOperating,
            'net_investing' => $netInvesting,
            'net_financing' => $netFinancing,
            'net_change' => $netChange,
            'closing_balance' => $closingBalance,
        ];
    }

    public function render()
    {
        $annualAccount = AnnualAccount::with('cashFlowStatement')->findOrFail($this->annualAccountId);
        $cashFlow = $annualAccount->cashFlowStatement;
        $totals = $this->getCalculatedTotals();

        return view('livewire.cash-flow-statement-manager', [
            'annualAccount' => $annualAccount,
            'cashFlow' => $cashFlow,
            'totals' => $totals,
        ]);
    }
}
