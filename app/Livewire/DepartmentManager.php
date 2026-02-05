<?php

namespace App\Livewire;

use App\Models\AccountingSettings;
use App\Models\Department;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public $showModal = false;

    public $editingId = null;

    public $search = '';

    public $filterActive = '';

    // Form fields
    public $code = '';

    public $name = '';

    public $description = '';

    public $sort_order = 0;

    public $is_active = true;

    protected function rules(): array
    {
        return [
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'code.required' => 'Avdelingskode er pakrevd.',
        'name.required' => 'Avdelingsnavn er pakrevd.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'code', 'name', 'description', 'sort_order', 'is_active']);
        $this->is_active = true;
        $this->sort_order = 0;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $department = Department::findOrFail($id);
        $this->editingId = $department->id;
        $this->code = $department->code;
        $this->name = $department->name;
        $this->description = $department->description ?? '';
        $this->sort_order = $department->sort_order;
        $this->is_active = $department->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorize('create', Department::class);

        $this->validate();

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'sort_order' => $this->sort_order ?? 0,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $department = Department::findOrFail($this->editingId);
            $department->update($data);
            Flux::toast(text: 'Avdeling oppdatert', variant: 'success');
        } else {
            Department::create($data);
            Flux::toast(text: 'Avdeling opprettet', variant: 'success');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'code', 'name', 'description', 'sort_order']);
    }

    public function delete(int $id): void
    {
        $department = Department::findOrFail($id);
        $this->authorize('delete', $department);

        // Check if department is in use
        $inUse = $department->voucherLines()->exists()
            || $department->invoices()->exists()
            || $department->quotes()->exists()
            || $department->orders()->exists()
            || $department->supplierInvoices()->exists();

        if ($inUse) {
            Flux::toast(text: 'Kan ikke slette avdeling som er i bruk', variant: 'danger');

            return;
        }

        $department->delete();
        Flux::toast(text: 'Avdeling slettet', variant: 'success');
    }

    public function render()
    {
        $company = app('current.company');
        $settings = $company ? AccountingSettings::forCompany($company->id) : null;
        $departmentsEnabled = $settings?->isDepartmentsEnabled() ?? false;

        $query = Department::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('code', 'like', "%{$this->search}%")
                        ->orWhere('name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterActive !== '', fn ($q) => $q->where('is_active', $this->filterActive === '1'))
            ->ordered();

        return view('livewire.department-manager', [
            'departments' => $query->paginate(20),
            'departmentsEnabled' => $departmentsEnabled,
        ]);
    }
}
