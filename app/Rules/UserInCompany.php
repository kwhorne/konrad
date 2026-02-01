<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UserInCompany implements ValidationRule
{
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

        // Check if user exists
        $userExists = DB::table('users')
            ->where('id', $value)
            ->exists();

        if (! $userExists) {
            $fail('Den valgte brukeren finnes ikke.');

            return;
        }

        // Check if user belongs to the current company
        $belongsToCompany = DB::table('company_user')
            ->where('user_id', $value)
            ->where('company_id', $company->id)
            ->exists();

        if (! $belongsToCompany) {
            $fail('Den valgte brukeren tilhører ikke dette selskapet.');
        }
    }
}
