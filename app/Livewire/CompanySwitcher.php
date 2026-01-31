<?php

namespace App\Livewire;

use App\Models\Company;
use App\Services\CompanyService;
use Livewire\Component;

class CompanySwitcher extends Component
{
    public function switchCompany(int $companyId): void
    {
        $company = Company::find($companyId);

        if (! $company) {
            return;
        }

        $companyService = app(CompanyService::class);

        if ($companyService->switchCompany(auth()->user(), $company)) {
            $this->redirect(request()->header('Referer', route('dashboard')), navigate: true);
        }
    }

    public function render()
    {
        $user = auth()->user();
        $companies = $user->companies()->orderBy('name')->get();
        $currentCompany = app('current.company');

        return view('livewire.company-switcher', [
            'companies' => $companies,
            'currentCompany' => $currentCompany,
        ]);
    }
}
