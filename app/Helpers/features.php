<?php

use App\Models\Company;
use App\Services\ModuleService;

if (! function_exists('company_has_module')) {
    /**
     * Check if the current company (or a specific company) has access to a module.
     */
    function company_has_module(string $slug, ?Company $company = null): bool
    {
        $company = $company ?? app('current.company');

        if (! $company) {
            // Fallback to config for backward compatibility when no company context
            return config("features.{$slug}", false);
        }

        return app(ModuleService::class)->isEnabled($company, $slug);
    }
}

if (! function_exists('current_company')) {
    /**
     * Get the current company from the application container.
     */
    function current_company(): ?Company
    {
        return app('current.company');
    }
}
