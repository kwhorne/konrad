<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\DeferredTaxItem;
use App\Rules\ExistsInCompany;
use Livewire\Component;

class DeferredTaxManager extends Component
{
    public $showModal = false;

    public $editingId = null;

    public $filterYear;

    // Form fields
    public $fiscal_year;

    public $item_type = 'asset';

    public $category = '';

    public $description = '';

    public $account_id = '';

    public $accounting_value = '';

    public $tax_value = '';

    public $notes = '';

    protected function rules(): array
    {
        return [
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'item_type' => 'required|in:asset,liability',
            'category' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'account_id' => ['nullable', new ExistsInCompany('accounts')],
            'accounting_value' => 'required|numeric',
            'tax_value' => 'required|numeric',
            'notes' => 'nullable|string',
        ];
    }

    protected $messages = [
        'fiscal_year.required' => 'Regnskapsår er påkrevd.',
        'item_type.required' => 'Type er påkrevd.',
        'category.required' => 'Kategori er påkrevd.',
        'description.required' => 'Beskrivelse er påkrevd.',
        'accounting_value.required' => 'Regnskapsmessig verdi er påkrevd.',
        'tax_value.required' => 'Skattemessig verdi er påkrevd.',
    ];

    public function mount()
    {
        $this->filterYear = now()->year;
        $this->fiscal_year = now()->year;
    }

    public function updatedFilterYear()
    {
        $this->fiscal_year = $this->filterYear;
    }

    public function openModal($id = null)
    {
        $this->resetForm();

        if ($id) {
            $this->editingId = $id;
            $item = DeferredTaxItem::findOrFail($id);

            $this->fiscal_year = $item->fiscal_year;
            $this->item_type = $item->item_type;
            $this->category = $item->category;
            $this->description = $item->description;
            $this->account_id = $item->account_id ?? '';
            $this->accounting_value = $item->accounting_value;
            $this->tax_value = $item->tax_value;
            $this->notes = $item->notes;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'fiscal_year' => $this->fiscal_year,
            'item_type' => $this->item_type,
            'category' => $this->category,
            'description' => $this->description,
            'account_id' => $this->account_id ?: null,
            'accounting_value' => $this->accounting_value,
            'tax_value' => $this->tax_value,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            $item = DeferredTaxItem::findOrFail($this->editingId);
            $item->update($data);
            session()->flash('success', 'Utsatt skatt-posten ble oppdatert.');
        } else {
            $data['created_by'] = auth()->id();
            DeferredTaxItem::create($data);
            session()->flash('success', 'Utsatt skatt-posten ble opprettet.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        DeferredTaxItem::findOrFail($id)->delete();
        session()->flash('success', 'Utsatt skatt-posten ble slettet.');
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->fiscal_year = $this->filterYear;
        $this->item_type = 'asset';
        $this->category = '';
        $this->description = '';
        $this->account_id = '';
        $this->accounting_value = '';
        $this->tax_value = '';
        $this->notes = '';
        $this->resetValidation();
    }

    public function render()
    {
        $items = DeferredTaxItem::with(['account', 'creator'])
            ->where('fiscal_year', $this->filterYear)
            ->orderBy('item_type')
            ->orderBy('category')
            ->get();

        // Summary calculations
        $deferredTaxAssets = $items->filter(fn ($i) => $i->isDeferredTaxAsset())->sum('deferred_tax');
        $deferredTaxLiabilities = $items->filter(fn ($i) => $i->isDeferredTaxLiability())->sum('deferred_tax');
        $netDeferredTax = $items->sum(fn ($i) => $i->getSignedDeferredTax());

        $years = DeferredTaxItem::selectRaw('DISTINCT fiscal_year')
            ->orderBy('fiscal_year', 'desc')
            ->pluck('fiscal_year');

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('livewire.deferred-tax-manager', [
            'items' => $items,
            'deferredTaxAssets' => $deferredTaxAssets,
            'deferredTaxLiabilities' => $deferredTaxLiabilities,
            'netDeferredTax' => $netDeferredTax,
            'years' => $years,
            'accounts' => Account::active()->ordered()->get(),
            'categories' => [
                'fixed_assets' => 'Varige driftsmidler',
                'receivables' => 'Fordringer',
                'provisions' => 'Avsetninger',
                'losses_carried_forward' => 'Fremførbart underskudd',
                'inventory' => 'Varelager',
                'financial_instruments' => 'Finansielle instrumenter',
                'other' => 'Annet',
            ],
        ]);
    }
}
