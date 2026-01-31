<?php

namespace App\Models\Traits;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    /**
     * Boot the trait.
     */
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model) {
            if (empty($model->company_id)) {
                $company = app('current.company');
                if ($company) {
                    $model->company_id = $company->id;
                }
            }
        });
    }

    /**
     * Initialize the trait.
     */
    public function initializeBelongsToCompany(): void
    {
        if (! in_array('company_id', $this->fillable)) {
            $this->fillable[] = 'company_id';
        }
    }

    /**
     * Get the company that owns this model.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to a specific company.
     */
    public function scopeForCompany($query, Company|int $company)
    {
        $companyId = $company instanceof Company ? $company->id : $company;

        return $query->withoutGlobalScope(CompanyScope::class)->where('company_id', $companyId);
    }

    /**
     * Scope a query to include all companies (bypass tenant scope).
     */
    public function scopeWithoutCompanyScope($query)
    {
        return $query->withoutGlobalScope(CompanyScope::class);
    }
}
