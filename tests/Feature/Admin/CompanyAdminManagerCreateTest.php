<?php

use App\Livewire\CompanyAdminManager;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createAdminForCreateTest(): User
{
    return User::factory()->create([
        'is_admin' => true,
        'onboarding_completed' => true,
    ]);
}

describe('CompanyAdminManager Create Company', function () {
    test('can open create modal', function () {
        $admin = createAdminForCreateTest();
        $company = Company::factory()->create();
        $company->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $this->actingAs($admin);

        Livewire::test(CompanyAdminManager::class)
            ->assertSet('showCreateModal', false)
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true);
    });

    test('can close create modal', function () {
        $admin = createAdminForCreateTest();
        $company = Company::factory()->create();
        $company->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $this->actingAs($admin);

        Livewire::test(CompanyAdminManager::class)
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true)
            ->call('closeCreateModal')
            ->assertSet('showCreateModal', false);
    });

    test('can create a new company', function () {
        $admin = createAdminForCreateTest();
        $existingCompany = Company::factory()->create();
        $existingCompany->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $this->actingAs($admin);

        Livewire::test(CompanyAdminManager::class)
            ->call('openCreateModal')
            ->set('createName', 'Nytt Selskap AS')
            ->set('createOrganizationNumber', '987654321')
            ->set('createEmail', 'post@nytt-selskap.no')
            ->set('createCity', 'Trondheim')
            ->call('createCompany')
            ->assertSet('showCreateModal', false)
            ->assertHasNoErrors();

        expect(Company::withoutGlobalScopes()->where('name', 'Nytt Selskap AS')->exists())->toBeTrue();
        $created = Company::withoutGlobalScopes()->where('name', 'Nytt Selskap AS')->first();
        expect($created->organization_number)->toBe('987654321')
            ->and($created->email)->toBe('post@nytt-selskap.no')
            ->and($created->city)->toBe('Trondheim')
            ->and($created->is_active)->toBeTrue();
    });

    test('validates required name when creating company', function () {
        $admin = createAdminForCreateTest();
        $company = Company::factory()->create();
        $company->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $this->actingAs($admin);

        Livewire::test(CompanyAdminManager::class)
            ->call('openCreateModal')
            ->set('createName', '')
            ->call('createCompany')
            ->assertHasErrors(['createName']);
    });

    test('validates email format when creating company', function () {
        $admin = createAdminForCreateTest();
        $company = Company::factory()->create();
        $company->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $this->actingAs($admin);

        Livewire::test(CompanyAdminManager::class)
            ->call('openCreateModal')
            ->set('createName', 'Test Selskap AS')
            ->set('createEmail', 'not-an-email')
            ->call('createCompany')
            ->assertHasErrors(['createEmail']);
    });

    test('resets form when closing create modal', function () {
        $admin = createAdminForCreateTest();
        $company = Company::factory()->create();
        $company->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $this->actingAs($admin);

        Livewire::test(CompanyAdminManager::class)
            ->call('openCreateModal')
            ->set('createName', 'Test Navn')
            ->call('closeCreateModal')
            ->assertSet('createName', '');
    });
});
