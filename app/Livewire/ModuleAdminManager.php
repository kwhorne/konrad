<?php

namespace App\Livewire;

use App\Models\Module;
use Livewire\Component;
use Livewire\WithPagination;

class ModuleAdminManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingModuleId = null;

    public ?int $deletingModuleId = null;

    // Form fields
    public string $slug = '';

    public string $name = '';

    public string $description = '';

    public bool $is_premium = true;

    public int $price_monthly = 0;

    public ?string $stripe_price_id = null;

    public bool $is_active = true;

    public int $sort_order = 0;

    protected function rules(): array
    {
        $uniqueRule = $this->editingModuleId
            ? 'unique:modules,slug,'.$this->editingModuleId
            : 'unique:modules,slug';

        return [
            'slug' => ['required', 'string', 'max:50', 'alpha_dash', $uniqueRule],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_premium' => ['boolean'],
            'price_monthly' => ['required', 'integer', 'min:0'],
            'stripe_price_id' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    protected function messages(): array
    {
        return [
            'slug.required' => 'Slug er påkrevd.',
            'slug.unique' => 'Denne slug-en er allerede i bruk.',
            'slug.alpha_dash' => 'Slug kan kun inneholde bokstaver, tall, bindestrek og understrek.',
            'name.required' => 'Navn er påkrevd.',
            'price_monthly.min' => 'Pris kan ikke være negativ.',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $moduleId): void
    {
        $module = Module::findOrFail($moduleId);

        $this->editingModuleId = $module->id;
        $this->slug = $module->slug;
        $this->name = $module->name;
        $this->description = $module->description ?? '';
        $this->is_premium = $module->is_premium;
        $this->price_monthly = $module->price_monthly;
        $this->stripe_price_id = $module->stripe_price_id;
        $this->is_active = $module->is_active;
        $this->sort_order = $module->sort_order;

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
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'is_premium' => $this->is_premium,
            'price_monthly' => $this->price_monthly,
            'stripe_price_id' => $this->stripe_price_id ?: null,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];

        if ($this->editingModuleId) {
            $module = Module::findOrFail($this->editingModuleId);
            $module->update($data);
            $message = 'Modul oppdatert';
        } else {
            Module::create($data);
            $message = 'Modul opprettet';
        }

        $this->dispatch('toast', message: $message, variant: 'success');
        $this->closeModal();
    }

    public function confirmDelete(int $moduleId): void
    {
        $this->deletingModuleId = $moduleId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->deletingModuleId = null;
        $this->showDeleteModal = false;
    }

    public function delete(): void
    {
        if (! $this->deletingModuleId) {
            return;
        }

        $module = Module::findOrFail($this->deletingModuleId);

        // Check if module is in use by any companies
        if ($module->companyModules()->exists()) {
            $this->dispatch('toast', message: 'Kan ikke slette modul som er i bruk av selskaper', variant: 'danger');
            $this->cancelDelete();

            return;
        }

        $module->delete();

        $this->dispatch('toast', message: 'Modul slettet', variant: 'success');
        $this->cancelDelete();
    }

    public function toggleActive(int $moduleId): void
    {
        $module = Module::findOrFail($moduleId);
        $module->update(['is_active' => ! $module->is_active]);

        $status = $module->is_active ? 'aktivert' : 'deaktivert';
        $this->dispatch('toast', message: "Modul {$status}", variant: 'success');
    }

    protected function resetForm(): void
    {
        $this->editingModuleId = null;
        $this->slug = '';
        $this->name = '';
        $this->description = '';
        $this->is_premium = true;
        $this->price_monthly = 0;
        $this->stripe_price_id = null;
        $this->is_active = true;
        $this->sort_order = 0;
        $this->resetValidation();
    }

    public function render()
    {
        $query = Module::query()
            ->withCount('companyModules')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('slug', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterType === 'premium', fn ($q) => $q->where('is_premium', true))
            ->when($this->filterType === 'standard', fn ($q) => $q->where('is_premium', false))
            ->orderBy('sort_order')
            ->orderBy('name');

        return view('livewire.module-admin-manager', [
            'modules' => $query->paginate(15),
            'totalModules' => Module::count(),
            'premiumModules' => Module::where('is_premium', true)->count(),
            'standardModules' => Module::where('is_premium', false)->count(),
        ]);
    }
}
