<?php

namespace App\Livewire;

use App\Models\TwoFactorIpWhitelist;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;

class TwoFactorIpWhitelistManager extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingEntryId = null;

    public ?int $deletingEntryId = null;

    // Form fields
    public string $ip_address = '';

    public string $cidr_range = '';

    public string $description = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'ip_address' => ['required', 'string', 'max:45', 'ip'],
            'cidr_range' => ['nullable', 'string', 'max:50', 'regex:/^(\d{1,3}\.){3}\d{1,3}\/\d{1,2}$/'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'ip_address.required' => 'IP-adresse er påkrevd.',
            'ip_address.ip' => 'Ugyldig IP-adresse.',
            'cidr_range.regex' => 'Ugyldig CIDR-format. Bruk format som 192.168.1.0/24.',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $entryId): void
    {
        $entry = TwoFactorIpWhitelist::findOrFail($entryId);

        $this->editingEntryId = $entry->id;
        $this->ip_address = $entry->ip_address;
        $this->cidr_range = $entry->cidr_range ?? '';
        $this->description = $entry->description ?? '';
        $this->is_active = $entry->is_active;

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'ip_address' => $this->ip_address,
            'cidr_range' => $this->cidr_range ?: null,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingEntryId) {
            $entry = TwoFactorIpWhitelist::findOrFail($this->editingEntryId);
            $entry->update($data);
            $message = 'IP-whitelist oppdatert';
        } else {
            $data['created_by'] = auth()->id();
            TwoFactorIpWhitelist::create($data);
            $message = 'IP-whitelist opprettet';
        }

        Flux::toast(text: $message, variant: 'success');
        $this->closeModal();
    }

    public function confirmDelete(int $entryId): void
    {
        $this->deletingEntryId = $entryId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->deletingEntryId = null;
        $this->showDeleteModal = false;
    }

    public function delete(): void
    {
        if (! $this->deletingEntryId) {
            return;
        }

        $entry = TwoFactorIpWhitelist::findOrFail($this->deletingEntryId);
        $entry->delete();

        Flux::toast(text: 'IP-whitelist slettet', variant: 'success');
        $this->cancelDelete();
    }

    public function toggleActive(int $entryId): void
    {
        $entry = TwoFactorIpWhitelist::findOrFail($entryId);
        $entry->update(['is_active' => ! $entry->is_active]);

        $status = $entry->is_active ? 'aktivert' : 'deaktivert';
        Flux::toast(text: "IP-whitelist {$status}", variant: 'success');
    }

    protected function resetForm(): void
    {
        $this->editingEntryId = null;
        $this->ip_address = '';
        $this->cidr_range = '';
        $this->description = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $query = TwoFactorIpWhitelist::query()
            ->with('creator')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('ip_address', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('created_at', 'desc');

        return view('livewire.two-factor-ip-whitelist-manager', [
            'entries' => $query->paginate(15),
            'totalEntries' => TwoFactorIpWhitelist::count(),
            'activeEntries' => TwoFactorIpWhitelist::where('is_active', true)->count(),
        ]);
    }
}
