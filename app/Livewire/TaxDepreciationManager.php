<?php

namespace App\Livewire;

use App\Models\TaxDepreciationSchedule;
use Livewire\Component;

class TaxDepreciationManager extends Component
{
    public $showModal = false;

    public $editingId = null;

    public $filterYear;

    // Form fields
    public $depreciation_group = '';

    public $opening_balance = 0;

    public $additions = 0;

    public $disposals = 0;

    public $notes = '';

    protected function rules(): array
    {
        return [
            'opening_balance' => 'required|numeric|min:0',
            'additions' => 'required|numeric|min:0',
            'disposals' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }

    public function mount()
    {
        $this->filterYear = now()->year;
    }

    public function updatedFilterYear()
    {
        // Check if schedules exist for this year, if not, offer to initialize
    }

    public function initializeYear()
    {
        TaxDepreciationSchedule::initializeForYear($this->filterYear, auth()->id());
        session()->flash('success', "Saldogrupper for {$this->filterYear} ble opprettet.");
    }

    public function openModal($id)
    {
        $this->resetForm();
        $this->editingId = $id;

        $schedule = TaxDepreciationSchedule::findOrFail($id);

        $this->depreciation_group = $schedule->depreciation_group;
        $this->opening_balance = $schedule->opening_balance;
        $this->additions = $schedule->additions;
        $this->disposals = $schedule->disposals;
        $this->notes = $schedule->notes;

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

        $schedule = TaxDepreciationSchedule::findOrFail($this->editingId);
        $schedule->update([
            'opening_balance' => $this->opening_balance,
            'additions' => $this->additions,
            'disposals' => $this->disposals,
            'notes' => $this->notes ?: null,
        ]);

        session()->flash('success', 'Saldogruppen ble oppdatert.');
        $this->closeModal();
    }

    public function recalculateAll()
    {
        $schedules = TaxDepreciationSchedule::forYear($this->filterYear)->get();

        foreach ($schedules as $schedule) {
            $schedule->calculateDepreciation();
            $schedule->save();
        }

        session()->flash('success', 'Alle avskrivninger ble beregnet på nytt.');
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->depreciation_group = '';
        $this->opening_balance = 0;
        $this->additions = 0;
        $this->disposals = 0;
        $this->notes = '';
        $this->resetValidation();
    }

    public function render()
    {
        $schedules = TaxDepreciationSchedule::forYear($this->filterYear)
            ->ordered()
            ->get();

        $totalDepreciation = $schedules->sum('depreciation_amount');
        $totalClosingBalance = $schedules->sum('closing_balance');

        $years = TaxDepreciationSchedule::selectRaw('DISTINCT fiscal_year')
            ->orderBy('fiscal_year', 'desc')
            ->pluck('fiscal_year');

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('livewire.tax-depreciation-manager', [
            'schedules' => $schedules,
            'totalDepreciation' => $totalDepreciation,
            'totalClosingBalance' => $totalClosingBalance,
            'years' => $years,
            'hasSchedules' => $schedules->count() > 0,
        ]);
    }
}
