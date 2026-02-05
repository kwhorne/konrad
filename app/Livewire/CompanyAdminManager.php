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

    public function render()
    {
        $query = Company::withoutGlobalScopes()
            ->withCount('users')
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

        return view('livewire.company-admin-manager', [
            'companies' => $query->paginate(15),
            'totalCompanies' => Company::withoutGlobalScopes()->count(),
            'activeCompanies' => Company::withoutGlobalScopes()->where('is_active', true)->count(),
            'premiumModules' => Module::active()->premium()->ordered()->get(),
        ]);
    }
}
