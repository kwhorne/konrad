<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CompanyService
{
    /**
     * Create a new company and assign the user as owner.
     *
     * @param  array<string, mixed>  $data
     */
    public function createCompany(array $data, User $owner): Company
    {
        return DB::transaction(function () use ($data, $owner) {
            $company = Company::create($data);

            // Attach user as owner
            $company->users()->attach($owner->id, [
                'role' => 'owner',
                'is_default' => ! $owner->hasCompany(),
                'joined_at' => now(),
            ]);

            // Set as current company if user doesn't have one
            if (! $owner->current_company_id) {
                $owner->update([
                    'current_company_id' => $company->id,
                    'onboarding_completed' => true,
                ]);
            }

            return $company;
        });
    }

    /**
     * Invite a user to a company.
     * If the user doesn't exist, creates a new user with a random password.
     *
     * @return array{user: User, is_new: bool}
     */
    public function inviteUser(Company $company, string $email, string $role = 'member', ?string $name = null): array
    {
        return DB::transaction(function () use ($company, $email, $role, $name) {
            $user = User::where('email', $email)->first();
            $isNew = false;

            if (! $user) {
                // Create new user with random password
                $user = User::create([
                    'name' => $name ?? explode('@', $email)[0],
                    'email' => $email,
                    'password' => bcrypt(str()->random(32)),
                    'onboarding_completed' => true,
                ]);
                $isNew = true;
            }

            // Check if user already belongs to company
            if ($company->hasUser($user)) {
                // Update role if different
                $company->users()->updateExistingPivot($user->id, [
                    'role' => $role,
                ]);
            } else {
                // Attach to company
                $company->users()->attach($user->id, [
                    'role' => $role,
                    'is_default' => ! $user->hasCompany(),
                    'joined_at' => now(),
                ]);

                // Set as current company if user doesn't have one
                if (! $user->current_company_id) {
                    $user->update(['current_company_id' => $company->id]);
                }
            }

            return ['user' => $user->fresh(), 'is_new' => $isNew];
        });
    }

    /**
     * Remove a user from a company.
     */
    public function removeUser(Company $company, User $user): bool
    {
        // Don't allow removing the owner if they're the only owner
        if ($company->isOwner($user) && $company->users()->wherePivot('role', 'owner')->count() <= 1) {
            return false;
        }

        return DB::transaction(function () use ($company, $user) {
            $company->users()->detach($user->id);

            // If this was their current company, switch to another
            if ($user->current_company_id === $company->id) {
                $newCompany = $user->companies()->first();
                $user->update([
                    'current_company_id' => $newCompany?->id,
                ]);
            }

            return true;
        });
    }

    /**
     * Switch the user's current company.
     */
    public function switchCompany(User $user, Company $company): bool
    {
        // Verify user belongs to company
        if (! $company->hasUser($user)) {
            return false;
        }

        $user->update(['current_company_id' => $company->id]);
        app()->instance('current.company', $company);

        return true;
    }

    /**
     * Transfer company ownership to another user.
     */
    public function transferOwnership(Company $company, User $currentOwner, User $newOwner): bool
    {
        if (! $company->isOwner($currentOwner)) {
            return false;
        }

        if (! $company->hasUser($newOwner)) {
            return false;
        }

        return DB::transaction(function () use ($company, $currentOwner, $newOwner) {
            // Demote current owner to manager
            $company->users()->updateExistingPivot($currentOwner->id, [
                'role' => 'manager',
            ]);

            // Promote new owner
            $company->users()->updateExistingPivot($newOwner->id, [
                'role' => 'owner',
            ]);

            return true;
        });
    }

    /**
     * Lookup company info from Brønnøysundregistrene.
     *
     * @return array<string, mixed>|null
     */
    public function lookupBrreg(string $organizationNumber): ?array
    {
        // Clean organization number (remove spaces and dashes)
        $orgNumber = preg_replace('/[^0-9]/', '', $organizationNumber);

        if (strlen($orgNumber) !== 9) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->get("https://data.brreg.no/enhetsregisteret/api/enheter/{$orgNumber}");

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            return [
                'organization_number' => $data['organisasjonsnummer'] ?? $orgNumber,
                'name' => $data['navn'] ?? null,
                'business_address' => $this->extractAddress($data['forretningsadresse'] ?? null),
                'postal_address' => $this->extractAddress($data['postadresse'] ?? null),
                'organization_form' => $data['organisasjonsform']['beskrivelse'] ?? null,
                'industry' => $data['naeringskode1']['beskrivelse'] ?? null,
                'registered_in_vat' => $data['registrertIMvaregisteret'] ?? false,
                'founded_date' => $data['stiftelsesdato'] ?? null,
                'employee_count' => $data['antallAnsatte'] ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract address components from Brønnøysund API response.
     *
     * @param  array<string, mixed>|null  $addressData
     * @return array<string, string|null>|null
     */
    private function extractAddress(?array $addressData): ?array
    {
        if (! $addressData) {
            return null;
        }

        $streetParts = $addressData['adresse'] ?? [];
        $street = is_array($streetParts) ? implode(', ', $streetParts) : $streetParts;

        return [
            'address' => $street ?: null,
            'postal_code' => $addressData['postnummer'] ?? null,
            'city' => $addressData['poststed'] ?? null,
            'municipality' => $addressData['kommune'] ?? null,
            'country' => $addressData['land'] ?? 'Norge',
        ];
    }

    /**
     * Update user's role in a company.
     */
    public function updateUserRole(Company $company, User $user, string $role): bool
    {
        if (! $company->hasUser($user)) {
            return false;
        }

        // Don't allow demoting the only owner
        if ($company->isOwner($user) && $role !== 'owner') {
            $ownerCount = $company->users()->wherePivot('role', 'owner')->count();
            if ($ownerCount <= 1) {
                return false;
            }
        }

        $company->users()->updateExistingPivot($user->id, [
            'role' => $role,
        ]);

        return true;
    }
}
