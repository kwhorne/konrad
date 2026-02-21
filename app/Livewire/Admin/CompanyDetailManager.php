<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use App\Models\CompanyModule;
use App\Models\Module;
use App\Services\ModuleService;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class CompanyDetailManager extends Component
{
    use AuthorizesRequests;

    public int $companyId;

    // Info form
    public string $name = '';

    public string $organizationNumber = '';

    public string $vatNumber = '';

    public string $address = '';

    public string $postalCode = '';

    public string $city = '';

    public string $country = '';

    public string $phone = '';

    public string $email = '';

    public string $website = '';

    public bool $isActive = true;

    // Module modal
    public bool $showModuleModal = false;

    /** @var array<int, bool> */
    public array $moduleStates = [];

    /** @var array<int, string> */
    public array $moduleExpiries = [];

    public function mount(int $companyId): void
    {
        $company = Company::withoutGlobalScopes()->findOrFail($companyId);
        $this->companyId = $companyId;
        $this->fillForm($company);
    }

    private function fillForm(Company $company): void
    {
        $this->name = $company->name ?? '';
        $this->organizationNumber = $company->organization_number ?? '';
        $this->vatNumber = $company->vat_number ?? '';
        $this->address = $company->address ?? '';
        $this->postalCode = $company->postal_code ?? '';
        $this->city = $company->city ?? '';
        $this->country = $company->country ?? '';
        $this->phone = $company->phone ?? '';
        $this->email = $company->email ?? '';
        $this->website = $company->website ?? '';
        $this->isActive = (bool) $company->is_active;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'organizationNumber' => ['nullable', 'string', 'max:20'],
            'vatNumber' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'postalCode' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        $company = Company::withoutGlobalScopes()->findOrFail($this->companyId);
        $company->update([
            'name' => $this->name,
            'organization_number' => $this->organizationNumber,
            'vat_number' => $this->vatNumber,
            'address' => $this->address,
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'is_active' => $this->isActive,
        ]);

        Flux::toast(text: 'Selskap oppdatert', variant: 'success');
    }

    public function openModuleModal(): void
    {
        $company = Company::withoutGlobalScopes()->findOrFail($this->companyId);
        $modules = Module::active()->premium()->ordered()->get();
        $companyModules = CompanyModule::where('company_id', $this->companyId)->get()->keyBy('module_id');

        $this->moduleStates = [];
        $this->moduleExpiries = [];

        foreach ($modules as $module) {
            $companyModule = $companyModules->get($module->id);
            $this->moduleStates[$module->id] = $companyModule?->isActive() ?? false;
            $this->moduleExpiries[$module->id] = $companyModule?->expires_at?->format('Y-m-d') ?? '';
        }

        $this->showModuleModal = true;
    }

    public function closeModuleModal(): void
    {
        $this->showModuleModal = false;
        $this->moduleStates = [];
        $this->moduleExpiries = [];
    }

    public function saveModules(): void
    {
        $company = Company::withoutGlobalScopes()->findOrFail($this->companyId);
        $moduleService = app(ModuleService::class);

        foreach ($this->moduleStates as $moduleId => $enabled) {
            $module = Module::find($moduleId);
            if (! $module) {
                continue;
            }

            $expiresAt = ! empty($this->moduleExpiries[$moduleId])
                ? Carbon::parse($this->moduleExpiries[$moduleId])->endOfDay()
                : null;

            if ($enabled) {
                $moduleService->enableForCompany($company, $module, 'admin', $expiresAt);
            } else {
                $moduleService->disableForCompany($company, $module);
            }
        }

        Flux::toast(text: 'Moduler oppdatert', variant: 'success');
        $this->closeModuleModal();
    }

    public function render(): \Illuminate\View\View
    {
        $company = Company::withoutGlobalScopes()
            ->with(['users'])
            ->findOrFail($this->companyId);

        $companyModules = CompanyModule::where('company_id', $this->companyId)
            ->with('module')
            ->get();

        $totalMonthlyOre = $companyModules
            ->filter(fn ($cm) => $cm->isActive())
            ->sum(fn ($cm) => $cm->module?->price_monthly ?? 0);

        $subscriptions = $company->subscriptions ?? collect();

        $premiumModules = Module::active()->premium()->ordered()->get();

        return view('livewire.admin.company-detail-manager', compact(
            'company',
            'companyModules',
            'totalMonthlyOre',
            'subscriptions',
            'premiumModules',
        ));
    }
}
