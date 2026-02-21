<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyModule;
use App\Models\Module;
use App\Models\User;

class AdminAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function getMetrics(): array
    {
        $totalCompanies = Company::withoutGlobalScopes()->count();
        $activeCompanies = Company::withoutGlobalScopes()->where('is_active', true)->count();
        $newCompaniesThisMonth = Company::withoutGlobalScopes()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
        $churnThisMonth = Company::withoutGlobalScopes()
            ->where('is_active', false)
            ->where('updated_at', '>=', now()->startOfMonth())
            ->count();

        $totalUsers = User::count();
        $newUsersThisWeek = User::where('created_at', '>=', now()->startOfWeek())->count();
        $newUsersThisMonth = User::where('created_at', '>=', now()->startOfMonth())->count();

        $mrr = $this->calculateMrr();
        $arr = $mrr * 12;

        $activePremiumSubscriptions = CompanyModule::where('is_enabled', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereHas('module', fn ($q) => $q->where('is_premium', true))
            ->count();

        $modulePopularity = $this->getModulePopularity();
        $revenuePerModule = $this->getRevenuePerModule();

        $recentSignups = Company::withoutGlobalScopes()
            ->with(['users' => function ($q) {
                $q->wherePivot('role', 'owner');
            }])
            ->latest()
            ->limit(10)
            ->get();

        return compact(
            'totalCompanies',
            'activeCompanies',
            'newCompaniesThisMonth',
            'churnThisMonth',
            'totalUsers',
            'newUsersThisWeek',
            'newUsersThisMonth',
            'mrr',
            'arr',
            'activePremiumSubscriptions',
            'modulePopularity',
            'revenuePerModule',
            'recentSignups'
        );
    }

    /**
     * Calculate Monthly Recurring Revenue in øre.
     */
    public function calculateMrr(): int
    {
        return (int) CompanyModule::where('is_enabled', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->join('modules', 'company_modules.module_id', '=', 'modules.id')
            ->where('modules.is_premium', true)
            ->sum('modules.price_monthly');
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function getModulePopularity(): \Illuminate\Support\Collection
    {
        return Module::active()
            ->premium()
            ->ordered()
            ->withCount(['companies as enabled_count' => function ($query) {
                $query->where('company_modules.is_enabled', true)
                    ->where(function ($q) {
                        $q->whereNull('company_modules.expires_at')
                            ->orWhere('company_modules.expires_at', '>', now());
                    });
            }])
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function getRevenuePerModule(): \Illuminate\Support\Collection
    {
        return Module::active()
            ->premium()
            ->ordered()
            ->withCount(['companies as enabled_count' => function ($query) {
                $query->where('company_modules.is_enabled', true)
                    ->where(function ($q) {
                        $q->whereNull('company_modules.expires_at')
                            ->orWhere('company_modules.expires_at', '>', now());
                    });
            }])
            ->get()
            ->map(function ($module) {
                $module->monthly_revenue = $module->enabled_count * $module->price_monthly;

                return $module;
            });
    }
}
