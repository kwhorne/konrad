<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helper to set up a user with a company
function createUserWithCompany(): array
{
    $user = User::factory()->create(['onboarding_completed' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);

    return ['user' => $user->fresh(), 'company' => $company];
}

// Company Creation Tests
describe('Company Creation', function () {
    test('can create a company', function () {
        $company = Company::factory()->create();

        expect($company)->toBeInstanceOf(Company::class)
            ->and($company->name)->not->toBeEmpty()
            ->and($company->organization_number)->toHaveLength(9);
    });

    test('company has users relationship', function () {
        $user = User::factory()->create();
        $company = Company::factory()->withOwner($user)->create();

        expect($company->users)->toHaveCount(1)
            ->and($company->users->first()->id)->toBe($user->id);
    });

    test('company owner is correctly identified', function () {
        $user = User::factory()->create();
        $company = Company::factory()->withOwner($user)->create();

        expect($company->isOwner($user))->toBeTrue()
            ->and($company->owner()->id)->toBe($user->id);
    });
});

// User-Company Relationship Tests
describe('User-Company Relationships', function () {
    test('user can belong to multiple companies', function () {
        $user = User::factory()->create();
        $company1 = Company::factory()->withOwner($user)->create();
        $company2 = Company::factory()->create();

        $company2->users()->attach($user->id, [
            'role' => 'member',
            'joined_at' => now(),
        ]);

        expect($user->companies)->toHaveCount(2);
    });

    test('user has correct role in company', function () {
        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $member = User::factory()->create();

        $company = Company::factory()->withOwner($owner)->create();

        $company->users()->attach($manager->id, ['role' => 'manager', 'joined_at' => now()]);
        $company->users()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        expect($owner->roleIn($company))->toBe('owner')
            ->and($manager->roleIn($company))->toBe('manager')
            ->and($member->roleIn($company))->toBe('member');
    });

    test('user can manage company if owner or manager', function () {
        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $member = User::factory()->create();

        $company = Company::factory()->withOwner($owner)->create();
        $company->users()->attach($manager->id, ['role' => 'manager', 'joined_at' => now()]);
        $company->users()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        expect($owner->canManage($company))->toBeTrue()
            ->and($manager->canManage($company))->toBeTrue()
            ->and($member->canManage($company))->toBeFalse();
    });

    test('user needs onboarding if no company', function () {
        $user = User::factory()->create(['onboarding_completed' => false]);

        expect($user->needsOnboarding())->toBeTrue();
    });

    test('user does not need onboarding if has company', function () {
        ['user' => $user] = createUserWithCompany();

        expect($user->needsOnboarding())->toBeFalse();
    });
});

// CompanyService Tests
describe('CompanyService', function () {
    test('creates company and assigns owner', function () {
        $user = User::factory()->create();
        $service = app(CompanyService::class);

        $company = $service->createCompany([
            'name' => 'Test Company AS',
            'organization_number' => '123456789',
        ], $user);

        expect($company)->toBeInstanceOf(Company::class)
            ->and($company->isOwner($user))->toBeTrue()
            ->and($user->fresh()->current_company_id)->toBe($company->id)
            ->and($user->fresh()->onboarding_completed)->toBeTrue();
    });

    test('invites existing user to company', function () {
        $owner = User::factory()->create();
        $existingUser = User::factory()->create();
        $company = Company::factory()->withOwner($owner)->create();
        $service = app(CompanyService::class);

        $result = $service->inviteUser($company, $existingUser->email, 'member');

        expect($result['is_new'])->toBeFalse()
            ->and($result['user']->id)->toBe($existingUser->id)
            ->and($company->hasUser($existingUser))->toBeTrue();
    });

    test('invites new user to company and creates account', function () {
        $owner = User::factory()->create();
        $company = Company::factory()->withOwner($owner)->create();
        $service = app(CompanyService::class);

        $result = $service->inviteUser($company, 'newuser@example.com', 'member', 'New User');

        expect($result['is_new'])->toBeTrue()
            ->and($result['user']->email)->toBe('newuser@example.com')
            ->and($result['user']->name)->toBe('New User')
            ->and($company->hasUser($result['user']))->toBeTrue();
    });

    test('switches user company', function () {
        $user = User::factory()->create();
        $company1 = Company::factory()->withOwner($user)->create();
        $company2 = Company::factory()->create();
        $company2->users()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);
        $user->update(['current_company_id' => $company1->id]);

        $service = app(CompanyService::class);
        $result = $service->switchCompany($user, $company2);

        expect($result)->toBeTrue()
            ->and($user->fresh()->current_company_id)->toBe($company2->id);
    });

    test('cannot switch to company user does not belong to', function () {
        $user = User::factory()->create();
        $company1 = Company::factory()->withOwner($user)->create();
        $user->update(['current_company_id' => $company1->id]);
        $company2 = Company::factory()->create(); // User not a member

        $service = app(CompanyService::class);
        $result = $service->switchCompany($user, $company2);

        expect($result)->toBeFalse()
            ->and($user->fresh()->current_company_id)->toBe($company1->id);
    });

    test('removes user from company', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $company = Company::factory()->withOwner($owner)->create();
        $company->users()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        $service = app(CompanyService::class);
        $result = $service->removeUser($company, $member);

        expect($result)->toBeTrue()
            ->and($company->hasUser($member))->toBeFalse();
    });

    test('cannot remove only owner from company', function () {
        $owner = User::factory()->create();
        $company = Company::factory()->withOwner($owner)->create();

        $service = app(CompanyService::class);
        $result = $service->removeUser($company, $owner);

        expect($result)->toBeFalse()
            ->and($company->hasUser($owner))->toBeTrue();
    });
});

// Global Scope Tests
describe('Company Global Scope', function () {
    test('models are scoped to current company', function () {
        ['user' => $user1, 'company' => $company1] = createUserWithCompany();
        ['user' => $user2, 'company' => $company2] = createUserWithCompany();

        // Set company context
        app()->instance('current.company', $company1);

        // Create contacts - let contact_number be auto-generated
        $contact1 = Contact::factory()->create(['company_id' => $company1->id]);
        $contact2 = Contact::factory()->create(['company_id' => $company2->id]);

        // Query should only return company 1's contacts
        $contacts = Contact::all();

        expect($contacts)->toHaveCount(1)
            ->and($contacts->first()->id)->toBe($contact1->id);
    });

    test('models automatically get company_id on creation', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompany();

        app()->instance('current.company', $company);

        // Use factory to ensure all required fields are present
        $contactData = Contact::factory()->make()->toArray();
        unset($contactData['company_id']); // Remove company_id to test auto-assignment
        $createdContact = Contact::create($contactData);

        expect($createdContact->company_id)->toBe($company->id);
    });

    test('can bypass global scope with withoutCompanyScope', function () {
        ['company' => $company1] = createUserWithCompany();
        ['company' => $company2] = createUserWithCompany();

        app()->instance('current.company', $company1);

        // Create contacts - let contact_number be auto-generated
        Contact::factory()->create(['company_id' => $company1->id]);
        Contact::factory()->create(['company_id' => $company2->id]);

        $allContacts = Contact::withoutCompanyScope()->get();

        expect($allContacts)->toHaveCount(2);
    });
});

// Middleware Tests
describe('Company Middleware', function () {
    test('user without company is redirected to onboarding', function () {
        $user = User::factory()->create(['onboarding_completed' => false]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('onboarding.index'));
    });

    test('user with company can access dashboard', function () {
        ['user' => $user] = createUserWithCompany();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    });

    test('onboarding page is accessible for users without company', function () {
        $user = User::factory()->create(['onboarding_completed' => false]);

        $response = $this->actingAs($user)->get(route('onboarding.index'));

        $response->assertOk();
    });

    test('company manager middleware blocks non-managers', function () {
        $owner = User::factory()->create(['onboarding_completed' => true]);
        $member = User::factory()->create(['onboarding_completed' => true]);

        $company = Company::factory()->withOwner($owner)->create();
        $company->users()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        $owner->update(['current_company_id' => $company->id]);
        $member->update(['current_company_id' => $company->id]);

        // Owner can access
        $this->actingAs($owner)->get(route('company.users'))->assertOk();

        // Member cannot access
        $this->actingAs($member)->get(route('company.users'))->assertForbidden();
    });
});

// Company Model Tests
describe('Company Model', function () {
    test('formatted organization number is correct', function () {
        $company = Company::factory()->create(['organization_number' => '123456789']);

        expect($company->formatted_organization_number)->toBe('123 456 789');
    });

    test('full address is formatted correctly', function () {
        $company = Company::factory()->create([
            'address' => 'Testgata 1',
            'postal_code' => '0123',
            'city' => 'Oslo',
        ]);

        expect($company->full_address)->toBe('Testgata 1, 0123 Oslo');
    });

    test('company managers returns only managers', function () {
        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $member = User::factory()->create();

        $company = Company::factory()->withOwner($owner)->create();
        $company->users()->attach($manager->id, ['role' => 'manager', 'joined_at' => now()]);
        $company->users()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        expect($company->managers())->toHaveCount(1)
            ->and($company->managers()->first()->id)->toBe($manager->id);
    });

    test('company members returns only members', function () {
        $owner = User::factory()->create();
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();

        $company = Company::factory()->withOwner($owner)->create();
        $company->users()->attach($member1->id, ['role' => 'member', 'joined_at' => now()]);
        $company->users()->attach($member2->id, ['role' => 'member', 'joined_at' => now()]);

        expect($company->members())->toHaveCount(2);
    });
});
