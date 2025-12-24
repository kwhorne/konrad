<?php

namespace App\Livewire;

use App\Models\ContactPerson;
use Livewire\Component;

class ContactPersonManager extends Component
{
    public $contactId;

    public $persons = [];

    public $showModal = false;

    // Form fields
    public $editingIndex = null;

    public $name = '';

    public $title = '';

    public $department = '';

    public $email = '';

    public $phone = '';

    public $linkedin = '';

    public $notes = '';

    public $birthday = '';

    public $is_primary = false;

    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'title' => 'nullable|string|max:255',
        'department' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:255',
        'linkedin' => 'nullable|url|max:255',
        'notes' => 'nullable|string',
        'birthday' => 'nullable|date',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function mount($contactId = null, $existingPersons = [])
    {
        $this->contactId = $contactId;

        if ($contactId && is_numeric($contactId)) {
            $this->persons = ContactPerson::where('contact_id', $contactId)->get()->toArray();
        } elseif (! empty($existingPersons)) {
            $this->persons = $existingPersons;
        }
    }

    public function openModal($index = null)
    {
        if ($index !== null) {
            $this->editingIndex = $index;
            $person = $this->persons[$index];

            $this->name = $person['name'] ?? '';
            $this->title = $person['title'] ?? '';
            $this->department = $person['department'] ?? '';
            $this->email = $person['email'] ?? '';
            $this->phone = $person['phone'] ?? '';
            $this->linkedin = $person['linkedin'] ?? '';
            $this->notes = $person['notes'] ?? '';
            $this->birthday = $person['birthday'] ?? '';
            $this->is_primary = $person['is_primary'] ?? false;
            $this->is_active = $person['is_active'] ?? true;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function savePerson()
    {
        $this->validate();

        $personData = [
            'name' => $this->name,
            'title' => $this->title,
            'department' => $this->department,
            'email' => $this->email,
            'phone' => $this->phone,
            'linkedin' => $this->linkedin,
            'notes' => $this->notes,
            'birthday' => $this->birthday,
            'is_primary' => $this->is_primary,
            'is_active' => $this->is_active,
        ];

        // If is_primary is true, set all others to false
        if ($this->is_primary) {
            foreach ($this->persons as &$person) {
                $person['is_primary'] = false;
            }
        }

        if ($this->editingIndex !== null) {
            $this->persons[$this->editingIndex] = $personData;
        } else {
            $this->persons[] = $personData;
        }

        $this->dispatch('persons-updated', persons: $this->persons);
        $this->closeModal();
    }

    public function deletePerson($index)
    {
        unset($this->persons[$index]);
        $this->persons = array_values($this->persons);
        $this->dispatch('persons-updated', persons: $this->persons);
    }

    public function setPrimary($index)
    {
        foreach ($this->persons as $key => &$person) {
            $person['is_primary'] = ($key === $index);
        }
        $this->dispatch('persons-updated', persons: $this->persons);
    }

    private function resetForm()
    {
        $this->editingIndex = null;
        $this->name = '';
        $this->title = '';
        $this->department = '';
        $this->email = '';
        $this->phone = '';
        $this->linkedin = '';
        $this->notes = '';
        $this->birthday = '';
        $this->is_primary = false;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.contact-person-manager');
    }
}
