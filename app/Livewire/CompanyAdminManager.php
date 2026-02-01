<?php

namespace App\Livewire;

use App\Models\Company;
use Livewire\Component;
use Livewire\WithPagination;

class CompanyAdminManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

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
        $this->dispatch('toast', message: "Selskap {$status}", variant: 'success');
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
        ]);
    }
}
