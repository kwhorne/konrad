<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Module;
use App\Services\ModuleService;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;

class CompanyAdminManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    public bool $showModuleModal = false;

    public ?int $editingCompanyId = null;

    public string $editingCompanyName = '';

    /** @var array<int, bool> */
    public array $moduleStates = [];

    // Create company form
    public bool $showCreateModal = false;

    public string $createName = '';

    public string $createOrganizationNumber = '';

    public string $createEmail = '';

    public string $createPhone = '';

    public string $createAddress = '';

    public string $createCity = '';

    public string $createPostalCode = '';

    public string $createCountry = 'Norge';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $companyId): void
    {
        $company = Company::withoutGlobalScopes()->findOrFail($companyId);
        $company->update(['is_active' => ! $company->is_active]);

        $status = $company->is_active ? 'aktivert' : 'deaktivert';
        Flux::toast(text: "Selskap {$status}", variant: 'success');
    }

    public function openModuleModal(int $companyId): void
    {
        $company = Company::withoutGlobalScopes()->findOrFail($companyId);
        $this->editingCompanyId = $companyId;
        $this->editingCompanyName = $company->name;

        $modules = Module::active()->premium()->get();
        $enabledModuleIds = $company->enabledModules()->pluck('modules.id')->toArray();

        $this->moduleStates = [];
        foreach ($modules as $module) {
            $this->moduleStates[$module->id] = in_array($module->id, $enabledModuleIds);
        }

        $this->showModuleModal = true;
    }

    public function closeModuleModal(): void
    {
        $this->showModuleModal = false;
        $this->editingCompanyId = null;
        $this->editingCompanyName = '';
        $this->moduleStates = [];
    }

    public function saveModules(): void
    {
        if (! $this->editingCompanyId) {
            return;
        }

        $company = Company::withoutGlobalScopes()->findOrFail($this->editingCompanyId);
        $moduleService = app(ModuleService::class);

        foreach ($this->moduleStates as $moduleId => $enabled) {
            $module = Module::find($moduleId);
            if (! $module) {
                continue;
            }

            if ($enabled) {
                $moduleService->enableForCompany($company, $module, 'admin');
            } else {
                $moduleService->disableForCompany($company, $module);
            }
        }

        Flux::toast(text: 'Moduler oppdatert', variant: 'success');
        $this->closeModuleModal();
    }

    public function openCreateModal(): void
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function resetCreateForm(): void
    {
        $this->createName = '';
        $this->createOrganizationNumber = '';
        $this->createEmail = '';
        $this->createPhone = '';
        $this->createAddress = '';
        $this->createCity = '';
        $this->createPostalCode = '';
        $this->createCountry = 'Norge';
    }

    public function createCompany(): void
    {
        $this->validate([
            'createName' => ['required', 'string', 'max:255'],
            'createOrganizationNumber' => ['nullable', 'string', 'max:20'],
            'createEmail' => ['nullable', 'email', 'max:255'],
            'createPhone' => ['nullable', 'string', 'max:50'],
            'createAddress' => ['nullable', 'string', 'max:255'],
            'createCity' => ['nullable', 'string', 'max:100'],
            'createPostalCode' => ['nullable', 'string', 'max:20'],
            'createCountry' => ['nullable', 'string', 'max:100'],
        ]);

        Company::create([
            'name' => $this->createName,
            'organization_number' => $this->createOrganizationNumber ?: fake()->unique()->numerify('#########'),
            'email' => $this->createEmail,
            'phone' => $this->createPhone,
            'address' => $this->createAddress,
            'city' => $this->createCity,
            'postal_code' => $this->createPostalCode,
            'country' => $this->createCountry ?: 'Norge',
            'is_active' => true,
        ]);

        Flux::toast(text: 'Selskap opprettet', variant: 'success');
        $this->closeCreateModal();
    }

    public function render()
    {
        $query = Company::withoutGlobalScopes()
            ->withCount('users')
            ->with(['enabledModules' => fn ($q) => $q->where('is_premium', true)])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('organization_number', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterStatus === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name');

        $stats = Company::withoutGlobalScopes()
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->first();

        return view('livewire.company-admin-manager', [
            'companies' => $query->paginate(15),
            'totalCompanies' => $stats->total,
            'activeCompanies' => $stats->active,
            'premiumModules' => Module::active()->premium()->ordered()->get(),
        ]);
    }
}
