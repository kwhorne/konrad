<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyModule;
use App\Models\Module;
use Carbon\Carbon;

class ModuleService
{
    /**
     * Check if a module is enabled for a company.
     */
    public function isEnabled(Company $company, string $slug): bool
    {
        $module = Module::where('slug', $slug)->first();

        if (! $module) {
            return false;
        }

        // Standard modules are always enabled
        if (! $module->is_premium) {
            return true;
        }

        // Check company-specific enablement
        $companyModule = CompanyModule::where('company_id', $company->id)
            ->where('module_id', $module->id)
            ->where('is_enabled', true)
            ->first();

        if (! $companyModule) {
            return false;
        }

        // Check expiration
        if ($companyModule->expires_at && $companyModule->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Enable a module for a company.
     */
    public function enableForCompany(
        Company $company,
        Module $module,
        string $enabledBy = 'admin',
        ?Carbon $expiresAt = null,
        ?string $stripeSubscriptionId = null,
        ?string $stripeSubscriptionStatus = null
    ): CompanyModule {
        return CompanyModule::updateOrCreate(
            [
                'company_id' => $company->id,
                'module_id' => $module->id,
            ],
            [
                'is_enabled' => true,
                'enabled_at' => now(),
                'expires_at' => $expiresAt,
                'enabled_by' => $enabledBy,
                'stripe_subscription_id' => $stripeSubscriptionId,
                'stripe_subscription_status' => $stripeSubscriptionStatus,
            ]
        );
    }

    /**
     * Disable a module for a company.
     */
    public function disableForCompany(Company $company, Module $module): bool
    {
        $companyModule = CompanyModule::where('company_id', $company->id)
            ->where('module_id', $module->id)
            ->first();

        if (! $companyModule) {
            return false;
        }

        $companyModule->update([
            'is_enabled' => false,
            'stripe_subscription_id' => null,
            'stripe_subscription_status' => null,
        ]);

        return true;
    }

    /**
     * Get all enabled modules for a company.
     *
     * @return \Illuminate\Support\Collection<int, Module>
     */
    public function getEnabledModules(Company $company)
    {
        // Get all standard modules
        $standardModules = Module::active()->standard()->get();

        // Get enabled premium modules for this company
        $enabledPremiumModules = $company->enabledModules()
            ->where('is_premium', true)
            ->get();

        return $standardModules->merge($enabledPremiumModules);
    }

    /**
     * Get all premium modules with their status for a company.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPremiumModulesWithStatus(Company $company)
    {
        return Module::active()
            ->premium()
            ->ordered()
            ->get()
            ->map(function ($module) use ($company) {
                $companyModule = CompanyModule::where('company_id', $company->id)
                    ->where('module_id', $module->id)
                    ->first();

                $module->is_enabled_for_company = $companyModule?->isActive() ?? false;
                $module->company_module = $companyModule;

                return $module;
            });
    }

    /**
     * Enable module by slug for a company.
     */
    public function enableBySlug(Company $company, string $slug, string $enabledBy = 'admin'): ?CompanyModule
    {
        $module = Module::where('slug', $slug)->first();

        if (! $module) {
            return null;
        }

        return $this->enableForCompany($company, $module, $enabledBy);
    }

    /**
     * Disable module by slug for a company.
     */
    public function disableBySlug(Company $company, string $slug): bool
    {
        $module = Module::where('slug', $slug)->first();

        if (! $module) {
            return false;
        }

        return $this->disableForCompany($company, $module);
    }
}
