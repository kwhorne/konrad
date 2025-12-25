<?php

namespace App\Livewire;

use App\Models\AnnualAccount;
use App\Models\AnnualAccountNote;
use Livewire\Component;

class AnnualAccountNotesManager extends Component
{
    public $annualAccountId;

    public $showModal = false;

    public $editingId = null;

    // Form fields
    public $note_number;

    public $note_type = '';

    public $title = '';

    public $content = '';

    public $is_visible = true;

    protected function rules(): array
    {
        return [
            'note_type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_visible' => 'boolean',
        ];
    }

    protected $messages = [
        'note_type.required' => 'Notetype er påkrevd.',
        'title.required' => 'Tittel er påkrevd.',
        'content.required' => 'Innhold er påkrevd.',
    ];

    public function mount($annualAccountId)
    {
        $this->annualAccountId = $annualAccountId;
    }

    public function openModal($id = null)
    {
        $this->resetForm();

        if ($id) {
            $this->editingId = $id;
            $note = AnnualAccountNote::findOrFail($id);

            $this->note_number = $note->note_number;
            $this->note_type = $note->note_type;
            $this->title = $note->title;
            $this->content = $note->content;
            $this->is_visible = $note->is_visible;
        } else {
            $maxNumber = AnnualAccountNote::where('annual_account_id', $this->annualAccountId)
                ->max('note_number') ?? 0;
            $this->note_number = $maxNumber + 1;
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

        $annualAccount = AnnualAccount::findOrFail($this->annualAccountId);

        if (! $annualAccount->canBeEdited()) {
            session()->flash('error', 'Kan ikke redigere innsendt årsregnskap.');
            $this->closeModal();

            return;
        }

        if ($this->editingId) {
            $note = AnnualAccountNote::findOrFail($this->editingId);
            $note->update([
                'note_type' => $this->note_type,
                'title' => $this->title,
                'content' => $this->content,
                'is_visible' => $this->is_visible,
            ]);
            session()->flash('success', 'Noten ble oppdatert.');
        } else {
            AnnualAccountNote::create([
                'annual_account_id' => $this->annualAccountId,
                'note_number' => $this->note_number,
                'note_type' => $this->note_type,
                'title' => $this->title,
                'content' => $this->content,
                'sort_order' => $this->note_number,
                'is_required' => AnnualAccountNote::isTypeRequired($this->note_type),
                'is_visible' => $this->is_visible,
                'created_by' => auth()->id(),
            ]);
            session()->flash('success', 'Noten ble opprettet.');
        }

        $this->closeModal();
    }

    public function toggleVisibility($id)
    {
        $note = AnnualAccountNote::findOrFail($id);

        $annualAccount = AnnualAccount::findOrFail($this->annualAccountId);
        if (! $annualAccount->canBeEdited()) {
            session()->flash('error', 'Kan ikke redigere innsendt årsregnskap.');

            return;
        }

        if ($note->is_required && $note->is_visible) {
            session()->flash('error', 'Påkrevde noter kan ikke skjules.');

            return;
        }

        $note->update(['is_visible' => ! $note->is_visible]);
    }

    public function delete($id)
    {
        $note = AnnualAccountNote::findOrFail($id);

        $annualAccount = AnnualAccount::findOrFail($this->annualAccountId);
        if (! $annualAccount->canBeEdited()) {
            session()->flash('error', 'Kan ikke redigere innsendt årsregnskap.');

            return;
        }

        if ($note->is_required) {
            session()->flash('error', 'Påkrevde noter kan ikke slettes.');

            return;
        }

        $note->delete();
        session()->flash('success', 'Noten ble slettet.');
    }

    public function useTemplate($type)
    {
        $this->note_type = $type;
        $this->title = AnnualAccountNote::getDefaultTitle($type);

        $this->content = match ($type) {
            'accounting_principles' => AnnualAccountNote::getAccountingPrinciplesTemplate(),
            'employees' => AnnualAccountNote::getEmployeesTemplate(),
            'equity' => AnnualAccountNote::getEquityTemplate(),
            default => '',
        };
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->note_number = null;
        $this->note_type = '';
        $this->title = '';
        $this->content = '';
        $this->is_visible = true;
        $this->resetValidation();
    }

    public function render()
    {
        $notes = AnnualAccountNote::where('annual_account_id', $this->annualAccountId)
            ->ordered()
            ->get();

        $annualAccount = AnnualAccount::findOrFail($this->annualAccountId);

        return view('livewire.annual-account-notes-manager', [
            'notes' => $notes,
            'annualAccount' => $annualAccount,
            'noteTypes' => AnnualAccountNote::NOTE_TYPES,
        ]);
    }
}
