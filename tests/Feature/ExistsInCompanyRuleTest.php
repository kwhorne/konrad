<?php

use App\Models\Account;
use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use App\Models\VatRate;
use App\Rules\ExistsInCompany;
use App\Rules\UserInCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function createUserWithCompanyForRuleTest(): array
{
    $user = User::factory()->create(['onboarding_completed' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);

    return ['user' => $user->fresh(), 'company' => $company];
}

// ExistsInCompany Rule Tests
describe('ExistsInCompany Rule', function () {
    test('passes when resource exists and belongs to current company', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company);

        $contact = Contact::factory()->create([
            'company_id' => $company->id,
            'contact_number' => 'CON'.uniqid(),
        ]);

        $validator = Validator::make(
            ['contact_id' => $contact->id],
            ['contact_id' => ['required', new ExistsInCompany('contacts')]]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('fails when resource exists but belongs to different company', function () {
        ['user' => $user1, 'company' => $company1] = createUserWithCompanyForRuleTest();
        ['company' => $company2] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company1);

        // Create contact belonging to company2
        $contact = Contact::factory()->create([
            'company_id' => $company2->id,
            'contact_number' => 'CON'.uniqid(),
        ]);

        $validator = Validator::make(
            ['contact_id' => $contact->id],
            ['contact_id' => ['required', new ExistsInCompany('contacts')]]
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('contact_id'))->toBe('Den valgte verdien er ugyldig.');
    });

    test('fails when resource does not exist', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company);

        $validator = Validator::make(
            ['contact_id' => 999999],
            ['contact_id' => ['required', new ExistsInCompany('contacts')]]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('passes with null value when nullable', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company);

        $validator = Validator::make(
            ['contact_id' => null],
            ['contact_id' => ['nullable', new ExistsInCompany('contacts')]]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('fails when no company is set', function () {
        // Rebind to return null to simulate no company selected
        app()->bind('current.company', fn () => null);

        $validator = Validator::make(
            ['contact_id' => 1],
            ['contact_id' => ['required', new ExistsInCompany('contacts')]]
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('contact_id'))->toBe('Ingen selskap er valgt.');
    });

    test('works with custom column name', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company);

        $account = Account::factory()->create(['company_id' => $company->id]);

        $validator = Validator::make(
            ['account_id' => $account->id],
            ['account_id' => ['required', new ExistsInCompany('accounts', 'id')]]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('prevents IDOR attack on accounts', function () {
        ['user' => $user1, 'company' => $company1] = createUserWithCompanyForRuleTest();
        ['company' => $company2] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company1);

        // Create an account belonging to company2 (the attacker's target)
        $targetAccount = Account::factory()->create(['company_id' => $company2->id]);

        // Attacker tries to reference this account from company1
        $validator = Validator::make(
            ['parent_id' => $targetAccount->id],
            ['parent_id' => ['nullable', new ExistsInCompany('accounts')]]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('prevents IDOR attack on vat_rates', function () {
        ['user' => $user1, 'company' => $company1] = createUserWithCompanyForRuleTest();
        ['company' => $company2] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company1);

        // Create a vat rate belonging to company2
        $targetVatRate = VatRate::factory()->create(['company_id' => $company2->id]);

        // Attacker tries to use this vat rate from company1
        $validator = Validator::make(
            ['vat_rate_id' => $targetVatRate->id],
            ['vat_rate_id' => ['required', new ExistsInCompany('vat_rates')]]
        );

        expect($validator->fails())->toBeTrue();
    });
});

// UserInCompany Rule Tests
describe('UserInCompany Rule', function () {
    test('passes when user exists and belongs to current company', function () {
        ['user' => $owner, 'company' => $company] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company);

        // The owner is already attached to the company
        $validator = Validator::make(
            ['user_id' => $owner->id],
            ['user_id' => ['required', new UserInCompany]]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('passes when member user belongs to current company', function () {
        ['user' => $owner, 'company' => $company] = createUserWithCompanyForRuleTest();
        $member = User::factory()->create();
        $company->users()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        app()->instance('current.company', $company);

        $validator = Validator::make(
            ['user_id' => $member->id],
            ['user_id' => ['required', new UserInCompany]]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('fails when user exists but does not belong to current company', function () {
        ['user' => $owner1, 'company' => $company1] = createUserWithCompanyForRuleTest();
        ['user' => $owner2, 'company' => $company2] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company1);

        // owner2 belongs to company2, not company1
        $validator = Validator::make(
            ['user_id' => $owner2->id],
            ['user_id' => ['required', new UserInCompany]]
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('user_id'))->toBe('Den valgte brukeren tilhører ikke dette selskapet.');
    });

    test('fails when user does not exist', function () {
        ['user' => $owner, 'company' => $company] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company);

        $validator = Validator::make(
            ['user_id' => 999999],
            ['user_id' => ['required', new UserInCompany]]
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('user_id'))->toBe('Den valgte brukeren finnes ikke.');
    });

    test('passes with null value when nullable', function () {
        ['user' => $owner, 'company' => $company] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company);

        $validator = Validator::make(
            ['user_id' => null],
            ['user_id' => ['nullable', new UserInCompany]]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('fails when no company is set', function () {
        $user = User::factory()->create();
        // Rebind to return null to simulate no company selected
        app()->bind('current.company', fn () => null);

        $validator = Validator::make(
            ['user_id' => $user->id],
            ['user_id' => ['required', new UserInCompany]]
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('user_id'))->toBe('Ingen selskap er valgt.');
    });

    test('prevents IDOR attack on responsible_user_id', function () {
        ['user' => $owner1, 'company' => $company1] = createUserWithCompanyForRuleTest();
        ['user' => $owner2, 'company' => $company2] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company1);

        // Attacker tries to assign owner2 (who belongs to company2) as responsible
        $validator = Validator::make(
            ['responsible_user_id' => $owner2->id],
            ['responsible_user_id' => ['nullable', new UserInCompany]]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('prevents IDOR attack on account_manager_id', function () {
        ['user' => $owner1, 'company' => $company1] = createUserWithCompanyForRuleTest();
        ['user' => $owner2, 'company' => $company2] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company1);

        // Attacker tries to assign owner2 as account manager
        $validator = Validator::make(
            ['account_manager_id' => $owner2->id],
            ['account_manager_id' => ['nullable', new UserInCompany]]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('prevents IDOR attack on assigned_to', function () {
        ['user' => $owner1, 'company' => $company1] = createUserWithCompanyForRuleTest();
        ['user' => $owner2, 'company' => $company2] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $company1);

        // Attacker tries to assign work to owner2 (from another company)
        $validator = Validator::make(
            ['assigned_to' => $owner2->id],
            ['assigned_to' => ['nullable', new UserInCompany]]
        );

        expect($validator->fails())->toBeTrue();
    });
});

// Integration Test - Simulates an actual IDOR attack
describe('IDOR Attack Simulation', function () {
    test('attacker cannot access another company contact via form submission', function () {
        // Setup: Two companies with their contacts
        ['user' => $attacker, 'company' => $attackerCompany] = createUserWithCompanyForRuleTest();
        ['company' => $victimCompany] = createUserWithCompanyForRuleTest();

        // Create contacts with explicit unique contact numbers to avoid collision
        $victimContact = Contact::factory()->create([
            'company_id' => $victimCompany->id,
            'contact_number' => 'CON-VICTIM-'.uniqid(),
        ]);

        $attackerContact = Contact::factory()->create([
            'company_id' => $attackerCompany->id,
            'contact_number' => 'CON-ATTACKER-'.uniqid(),
        ]);

        // Now set context to attacker company
        app()->instance('current.company', $attackerCompany);

        // Attacker tries to use victim's contact_id in a form
        $attackData = ['contact_id' => $victimContact->id];
        $legitimateData = ['contact_id' => $attackerContact->id];

        $attackValidator = Validator::make(
            $attackData,
            ['contact_id' => ['required', new ExistsInCompany('contacts')]]
        );

        $legitimateValidator = Validator::make(
            $legitimateData,
            ['contact_id' => ['required', new ExistsInCompany('contacts')]]
        );

        expect($attackValidator->fails())->toBeTrue('IDOR attack should be blocked')
            ->and($legitimateValidator->passes())->toBeTrue('Legitimate access should work');
    });

    test('attacker cannot assign user from another company', function () {
        // Setup: Two companies with their users
        ['user' => $attacker, 'company' => $attackerCompany] = createUserWithCompanyForRuleTest();
        ['user' => $victim, 'company' => $victimCompany] = createUserWithCompanyForRuleTest();

        app()->instance('current.company', $attackerCompany);

        // Attacker tries to assign the victim as responsible user
        $attackData = ['responsible_user_id' => $victim->id];
        $legitimateData = ['responsible_user_id' => $attacker->id];

        $attackValidator = Validator::make(
            $attackData,
            ['responsible_user_id' => ['nullable', new UserInCompany]]
        );

        $legitimateValidator = Validator::make(
            $legitimateData,
            ['responsible_user_id' => ['nullable', new UserInCompany]]
        );

        expect($attackValidator->fails())->toBeTrue('IDOR attack should be blocked')
            ->and($legitimateValidator->passes())->toBeTrue('Legitimate access should work');
    });
});
