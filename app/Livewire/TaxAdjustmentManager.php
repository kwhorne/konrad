<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\TaxAdjustment;
use App\Rules\ExistsInCompany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class TaxAdjustmentManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public $showModal = false;

    public $editingId = null;

    public $filterYear;

    public $filterType = '';

    // Form fields
    public $fiscal_year;

    public $adjustment_type = 'permanent';

    public $category = '';

    public $description = '';

    public $account_id = '';

    public $accounting_amount = '';

    public $tax_amount = '';

    public $notes = '';

    protected function rules(): array
    {
        return [
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'adjustment_type' => 'required|in:permanent,temporary_deductible,temporary_taxable',
            'category' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'account_id' => ['nullable', new ExistsInCompany('accounts')],
            'accounting_amount' => 'required|numeric',
            'tax_amount' => 'required|numeric',
            'notes' => 'nullable|string',
        ];
    }

    protected $messages = [
        'fiscal_year.required' => 'Regnskapsår er påkrevd.',
        'adjustment_type.required' => 'Type forskjell er påkrevd.',
        'category.required' => 'Kategori er påkrevd.',
        'description.required' => 'Beskrivelse er påkrevd.',
        'accounting_amount.required' => 'Regnskapsmessig beløp er påkrevd.',
        'tax_amount.required' => 'Skattemessig beløp er påkrevd.',
    ];

    public function mount(): void
    {
        $this->filterYear = now()->year;
        $this->fiscal_year = now()->year;
    }

    public function updatedFilterYear(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function openModal($id = null): void
    {
        $this->resetForm();

        if ($id) {
            $this->editingId = $id;
            $adjustment = TaxAdjustment::findOrFail($id);

            $this->fiscal_year = $adjustment->fiscal_year;
            $this->adjustment_type = $adjustment->adjustment_type;
            $this->category = $adjustment->category;
            $this->description = $adjustment->description;
            $this->account_id = $adjustment->account_id ?? '';
            $this->accounting_amount = $adjustment->accounting_amount;
            $this->tax_amount = $adjustment->tax_amount;
            $this->notes = $adjustment->notes;
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->authorize('create', TaxAdjustment::class);

        $this->validate();

        $data = [
            'fiscal_year' => $this->fiscal_year,
            'adjustment_type' => $this->adjustment_type,
            'category' => $this->category,
            'description' => $this->description,
            'account_id' => $this->account_id ?: null,
            'accounting_amount' => $this->accounting_amount,
            'tax_amount' => $this->tax_amount,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            $adjustment = TaxAdjustment::findOrFail($this->editingId);
            $adjustment->update($data);
            session()->flash('success', 'Skatteforskjellen ble oppdatert.');
        } else {
            $data['created_by'] = auth()->id();
            TaxAdjustment::create($data);
            session()->flash('success', 'Skatteforskjellen ble opprettet.');
        }

        $this->closeModal();
    }

    public function delete($id): void
    {
        $adjustment = TaxAdjustment::findOrFail($id);
        $this->authorize('delete', $adjustment);

        $adjustment->delete();
        session()->flash('success', 'Skatteforskjellen ble slettet.');
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->fiscal_year = $this->filterYear;
        $this->adjustment_type = 'permanent';
        $this->category = '';
        $this->description = '';
        $this->account_id = '';
        $this->accounting_amount = '';
        $this->tax_amount = '';
        $this->notes = '';
        $this->resetValidation();
    }

    public function render()
    {
        $query = TaxAdjustment::with(['account', 'creator'])
            ->where('fiscal_year', $this->filterYear)
            ->orderBy('adjustment_type')
            ->orderBy('category');

        if ($this->filterType) {
            $query->where('adjustment_type', $this->filterType);
        }

        $adjustments = $query->paginate(15);

        // Summary
        $permanentTotal = TaxAdjustment::where('fiscal_year', $this->filterYear)
            ->where('adjustment_type', 'permanent')
            ->sum('difference');

        $temporaryTotal = TaxAdjustment::where('fiscal_year', $this->filterYear)
            ->whereIn('adjustment_type', ['temporary_deductible', 'temporary_taxable'])
            ->sum('difference');

        $years = TaxAdjustment::selectRaw('DISTINCT fiscal_year')
            ->orderBy('fiscal_year', 'desc')
            ->pluck('fiscal_year');

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('livewire.tax-adjustment-manager', [
            'adjustments' => $adjustments,
            'permanentTotal' => $permanentTotal,
            'temporaryTotal' => $temporaryTotal,
            'years' => $years,
            'accounts' => Account::active()->ordered()->get(),
            'categories' => [
                'entertainment' => 'Representasjon',
                'fines' => 'Bøter og gebyrer',
                'unrealized_gains' => 'Urealiserte gevinster',
                'unrealized_losses' => 'Urealiserte tap',
                'depreciation_difference' => 'Avskrivningsforskjell',
                'provisions' => 'Avsetninger',
                'warranty' => 'Garantiforpliktelser',
                'bad_debts' => 'Tap på fordringer',
                'other' => 'Annet',
            ],
        ]);
    }
}
