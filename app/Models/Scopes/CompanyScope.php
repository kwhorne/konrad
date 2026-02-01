<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! app()->bound('current.company')) {
            return;
        }

        $company = app('current.company');

        if ($company) {
            $builder->where(function ($query) use ($model, $company) {
                $query->where($model->getTable().'.company_id', $company->id)
                    ->orWhereNull($model->getTable().'.company_id');
            });
        }
    }
}
