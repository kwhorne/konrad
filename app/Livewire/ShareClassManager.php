<?php

namespace App\Livewire;

use App\Models\ShareClass;
use Livewire\Component;

class ShareClassManager extends Component
{
    public $showModal = false;

    public $editingId = null;

    // Form fields
    public $name = '';

    public $code = '';

    public $isin = '';

    public $par_value = '';

    public $total_shares = 0;

    public $has_voting_rights = true;

    public $has_dividend_rights = true;

    public $voting_weight = '1.00';

    public $restrictions = '';

    public $is_active = true;

    public $sort_order = 0;

    protected function rules(): array
    {
        $codeRule = 'required|string|max:10|unique:share_classes,code';
        if ($this->editingId) {
            $codeRule .= ','.$this->editingId;
        }

        return [
            'name' => 'required|string|max:255',
            'code' => $codeRule,
            'isin' => 'nullable|string|max:12',
            'par_value' => 'required|numeric|min:0.01',
            'total_shares' => 'required|integer|min:0',
            'has_voting_rights' => 'boolean',
            'has_dividend_rights' => 'boolean',
            'voting_weight' => 'required|numeric|min:0',
            'restrictions' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    protected $messages = [
        'name.required' => 'Navn er påkrevd.',
        'code.required' => 'Kode er påkrevd.',
        'code.unique' => 'Denne koden er allerede i bruk.',
        'par_value.required' => 'Pålydende er påkrevd.',
        'par_value.min' => 'Pålydende må være minst 0,01 kr.',
    ];

    public function openModal($id = null)
    {
        $this->resetForm();

        if ($id) {
            $this->editingId = $id;
            $shareClass = ShareClass::findOrFail($id);

            $this->name = $shareClass->name;
            $this->code = $shareClass->code;
            $this->isin = $shareClass->isin;
            $this->par_value = $shareClass->par_value;
            $this->total_shares = $shareClass->total_shares;
            $this->has_voting_rights = $shareClass->has_voting_rights;
            $this->has_dividend_rights = $shareClass->has_dividend_rights;
            $this->voting_weight = $shareClass->voting_weight;
            $this->restrictions = $shareClass->restrictions ? json_encode($shareClass->restrictions) : '';
            $this->is_active = $shareClass->is_active;
            $this->sort_order = $shareClass->sort_order;
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

        $restrictions = null;
        if ($this->restrictions) {
            $restrictions = json_decode($this->restrictions, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $restrictions = ['notes' => $this->restrictions];
            }
        }

        $data = [
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'isin' => $this->isin ?: null,
            'par_value' => $this->par_value,
            'total_shares' => $this->total_shares,
            'has_voting_rights' => $this->has_voting_rights,
            'has_dividend_rights' => $this->has_dividend_rights,
            'voting_weight' => $this->voting_weight,
            'restrictions' => $restrictions,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order ?? 0,
        ];

        if ($this->editingId) {
            $shareClass = ShareClass::findOrFail($this->editingId);
            $shareClass->update($data);
            session()->flash('success', 'Aksjeklassen ble oppdatert.');
        } else {
            ShareClass::create($data);
            session()->flash('success', 'Aksjeklassen ble opprettet.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $shareClass = ShareClass::findOrFail($id);

        // Check if share class has holdings
        if ($shareClass->activeShareholdings()->exists()) {
            session()->flash('error', 'Kan ikke slette aksjeklasse med aktive aksjeinnehav.');

            return;
        }

        $shareClass->delete();
        session()->flash('success', 'Aksjeklassen ble slettet.');
    }

    public function toggleActive($id)
    {
        $shareClass = ShareClass::findOrFail($id);
        $shareClass->update(['is_active' => ! $shareClass->is_active]);
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->code = '';
        $this->isin = '';
        $this->par_value = '';
        $this->total_shares = 0;
        $this->has_voting_rights = true;
        $this->has_dividend_rights = true;
        $this->voting_weight = '1.00';
        $this->restrictions = '';
        $this->is_active = true;
        $this->sort_order = 0;
        $this->resetValidation();
    }

    public function render()
    {
        $shareClasses = ShareClass::ordered()->get();

        $totalCapital = $shareClasses->where('is_active', true)->sum(fn ($c) => $c->getTotalCapital());
        $totalShares = $shareClasses->where('is_active', true)->sum('total_shares');

        return view('livewire.share-class-manager', [
            'shareClasses' => $shareClasses,
            'totalCapital' => $totalCapital,
            'totalShares' => $totalShares,
        ]);
    }
}
