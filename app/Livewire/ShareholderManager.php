<?php

namespace App\Livewire;

use App\Models\Contact;
use App\Models\Shareholder;
use App\Rules\ExistsInCompany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class ShareholderManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public $showModal = false;

    public $editingId = null;

    public $search = '';

    public $filterType = '';

    public $filterActive = '';

    // Form fields
    public $shareholder_type = 'person';

    public $name = '';

    public $national_id = '';

    public $organization_number = '';

    public $country_code = 'NO';

    public $address = '';

    public $postal_code = '';

    public $city = '';

    public $email = '';

    public $phone = '';

    public $is_active = true;

    public $notes = '';

    public $contact_id = '';

    protected function rules(): array
    {
        return [
            'shareholder_type' => 'required|in:person,company',
            'name' => 'required|string|max:255',
            'national_id' => 'nullable|string|max:11',
            'organization_number' => 'nullable|string|max:9',
            'country_code' => 'required|string|size:2',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'contact_id' => ['nullable', new ExistsInCompany('contacts')],
        ];
    }

    protected $messages = [
        'name.required' => 'Navn er påkrevd.',
        'shareholder_type.required' => 'Type aksjonær er påkrevd.',
        'country_code.required' => 'Landskode er påkrevd.',
        'country_code.size' => 'Landskode må være 2 tegn (f.eks. NO).',
        'email.email' => 'Ugyldig e-postadresse.',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterType()
    {
        $this->resetPage();
    }

    public function updatedFilterActive()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetForm();

        if ($id) {
            $this->editingId = $id;
            $shareholder = Shareholder::findOrFail($id);

            $this->shareholder_type = $shareholder->shareholder_type;
            $this->name = $shareholder->name;
            $this->national_id = $shareholder->national_id;
            $this->organization_number = $shareholder->organization_number;
            $this->country_code = $shareholder->country_code;
            $this->address = $shareholder->address;
            $this->postal_code = $shareholder->postal_code;
            $this->city = $shareholder->city;
            $this->email = $shareholder->email;
            $this->phone = $shareholder->phone;
            $this->is_active = $shareholder->is_active;
            $this->notes = $shareholder->notes;
            $this->contact_id = $shareholder->contact_id ?? '';
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
        $this->authorize('create', Shareholder::class);

        $this->validate();

        $data = [
            'shareholder_type' => $this->shareholder_type,
            'name' => $this->name,
            'national_id' => $this->national_id ?: null,
            'organization_number' => $this->organization_number ?: null,
            'country_code' => $this->country_code,
            'address' => $this->address ?: null,
            'postal_code' => $this->postal_code ?: null,
            'city' => $this->city ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'is_active' => $this->is_active,
            'notes' => $this->notes ?: null,
            'contact_id' => $this->contact_id ?: null,
        ];

        if ($this->editingId) {
            $shareholder = Shareholder::findOrFail($this->editingId);
            $shareholder->update($data);
            session()->flash('success', 'Aksjonæren ble oppdatert.');
        } else {
            $data['created_by'] = auth()->id();
            Shareholder::create($data);
            session()->flash('success', 'Aksjonæren ble opprettet.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $shareholder = Shareholder::findOrFail($id);
        $this->authorize('delete', $shareholder);

        // Check if shareholder has active holdings
        if ($shareholder->activeShareholdings()->exists()) {
            session()->flash('error', 'Kan ikke slette aksjonær med aktive aksjeinnehav.');

            return;
        }

        $shareholder->delete();
        session()->flash('success', 'Aksjonæren ble slettet.');
    }

    public function toggleActive($id)
    {
        $shareholder = Shareholder::findOrFail($id);
        $this->authorize('update', $shareholder);

        $shareholder->update(['is_active' => ! $shareholder->is_active]);
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->shareholder_type = 'person';
        $this->name = '';
        $this->national_id = '';
        $this->organization_number = '';
        $this->country_code = 'NO';
        $this->address = '';
        $this->postal_code = '';
        $this->city = '';
        $this->email = '';
        $this->phone = '';
        $this->is_active = true;
        $this->notes = '';
        $this->contact_id = '';
        $this->resetValidation();
    }

    public function render()
    {
        $query = Shareholder::with(['contact', 'activeShareholdings.shareClass'])
            ->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('organization_number', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterType) {
            $query->where('shareholder_type', $this->filterType);
        }

        if ($this->filterActive !== '') {
            $query->where('is_active', $this->filterActive === '1');
        }

        return view('livewire.shareholder-manager', [
            'shareholders' => $query->paginate(15),
            'contacts' => Contact::active()->ordered()->get(),
        ]);
    }
}
