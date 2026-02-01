<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ExistsInCompany implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @param  string  $table  The table name to check
     * @param  string  $column  The column to check (default: 'id')
     */
    public function __construct(
        protected string $table,
        protected string $column = 'id'
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        $company = app('current.company');

        if (! $company) {
            $fail('Ingen selskap er valgt.');

            return;
        }

        $exists = DB::table($this->table)
            ->where($this->column, $value)
            ->where('company_id', $company->id)
            ->exists();

        if (! $exists) {
            $fail('Den valgte verdien er ugyldig.');
        }
    }
}
