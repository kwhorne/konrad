<?php

use App\Livewire\CompanyProfileManager;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createUserWithCompanyForProfileTest(string $role = 'owner'): array
{
    $user = User::factory()->create(['onboarding_completed' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);

    if ($role !== 'owner') {
        $company->users()->updateExistingPivot($user->id, ['role' => $role]);
    }

    return ['user' => $user->fresh(), 'company' => $company];
}

describe('CompanyProfileManager Component', function () {
    test('renders successfully for company owner', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForProfileTest('owner');
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(CompanyProfileManager::class)
            ->assertOk()
            ->assertSet('name', $company->name);
    });

    test('loads company data on mount', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForProfileTest('owner');
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(CompanyProfileManager::class)
            ->assertSet('name', $company->name)
            ->assertSet('organization_number', $company->organization_number)
            ->assertSet('email', $company->email)
            ->assertSet('phone', $company->phone);
    });

    test('owner can save company settings', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForProfileTest('owner');
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(CompanyProfileManager::class)
            ->set('name', 'Updated Company Name')
            ->set('email', 'updated@example.com')
            ->call('save')
            ->assertDispatched('toast');

        expect($company->fresh()->name)->toBe('Updated Company Name')
            ->and($company->fresh()->email)->toBe('updated@example.com');
    });

    test('manager can save company settings', function () {
        $owner = User::factory()->create(['onboarding_completed' => true]);
        $company = Company::factory()->withOwner($owner)->create();

        $manager = User::factory()->create(['onboarding_completed' => true]);
        $company->users()->attach($manager->id, ['role' => 'manager', 'joined_at' => now()]);
        $manager->update(['current_company_id' => $company->id]);

        app()->instance('current.company', $company);

        $this->actingAs($manager);

        Livewire::test(CompanyProfileManager::class)
            ->set('name', 'Manager Updated Name')
            ->call('save')
            ->assertDispatched('toast');

        expect($company->fresh()->name)->toBe('Manager Updated Name');
    });

    test('member cannot save company settings', function () {
        $owner = User::factory()->create(['onboarding_completed' => true]);
        $company = Company::factory()->withOwner($owner)->create();

        $member = User::factory()->create(['onboarding_completed' => true]);
        $company->users()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);
        $member->update(['current_company_id' => $company->id]);

        app()->instance('current.company', $company);

        $originalName = $company->name;

        $this->actingAs($member);

        Livewire::test(CompanyProfileManager::class)
            ->set('name', 'Should Not Change')
            ->call('save')
            ->assertDispatched('toast', message: 'Du har ikke tilgang til å endre selskapsinnstillinger.', variant: 'danger');

        expect($company->fresh()->name)->toBe($originalName);
    });

    test('validates required fields', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForProfileTest('owner');
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(CompanyProfileManager::class)
            ->set('name', '')
            ->set('organization_number', '')
            ->call('save')
            ->assertHasErrors(['name', 'organization_number']);
    });

    test('validates email format', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForProfileTest('owner');
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(CompanyProfileManager::class)
            ->set('email', 'invalid-email')
            ->call('save')
            ->assertHasErrors(['email']);
    });

    test('validates website format', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForProfileTest('owner');
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(CompanyProfileManager::class)
            ->set('website', 'not-a-url')
            ->call('save')
            ->assertHasErrors(['website']);
    });

    test('canManage is passed to view correctly', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForProfileTest('owner');
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(CompanyProfileManager::class)
            ->assertViewHas('canManage', true);
    });

    test('canManage is false for members', function () {
        $owner = User::factory()->create(['onboarding_completed' => true]);
        $company = Company::factory()->withOwner($owner)->create();

        $member = User::factory()->create(['onboarding_completed' => true]);
        $company->users()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);
        $member->update(['current_company_id' => $company->id]);

        app()->instance('current.company', $company);

        $this->actingAs($member);

        Livewire::test(CompanyProfileManager::class)
            ->assertViewHas('canManage', false);
    });
});

describe('Company Settings Page', function () {
    test('company settings route redirects to settings page for owner', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForProfileTest('owner');

        $response = $this->actingAs($user)->get(route('company.settings'));

        $response->assertRedirect(route('settings'));
    });

    test('settings page shows company tab for owner', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForProfileTest('owner');

        $response = $this->actingAs($user)->get(route('settings'));

        $response->assertOk()
            ->assertSeeLivewire(CompanyProfileManager::class);
    });

    test('settings page shows company tab for manager', function () {
        $owner = User::factory()->create(['onboarding_completed' => true]);
        $company = Company::factory()->withOwner($owner)->create();

        $manager = User::factory()->create(['onboarding_completed' => true]);
        $company->users()->attach($manager->id, ['role' => 'manager', 'joined_at' => now()]);
        $manager->update(['current_company_id' => $company->id]);

        $response = $this->actingAs($manager)->get(route('settings'));

        $response->assertOk()
            ->assertSeeLivewire(CompanyProfileManager::class);
    });
});
