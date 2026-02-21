<?php

use App\Livewire\Admin\SystemManager;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createAdminForSystemTest(): User
{
    return User::factory()->create([
        'is_admin' => true,
        'onboarding_completed' => true,
    ]);
}

describe('SystemManager Component', function () {
    test('renders successfully for admin', function () {
        $admin = createAdminForSystemTest();
        $company = Company::factory()->create();
        $company->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $this->actingAs($admin);

        Livewire::test(SystemManager::class)
            ->assertOk();
    });

    test('can clear cache', function () {
        $admin = createAdminForSystemTest();
        $company = Company::factory()->create();
        $company->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $this->actingAs($admin);

        Livewire::test(SystemManager::class)
            ->call('clearCache')
            ->assertDispatched('toast-show');
    });

    test('can load logs', function () {
        $admin = createAdminForSystemTest();
        $company = Company::factory()->create();
        $company->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $this->actingAs($admin);

        Livewire::test(SystemManager::class)
            ->assertSet('showLogs', false)
            ->call('loadLogs')
            ->assertSet('showLogs', true);
    });

    test('can hide logs', function () {
        $admin = createAdminForSystemTest();
        $company = Company::factory()->create();
        $company->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $this->actingAs($admin);

        Livewire::test(SystemManager::class)
            ->call('loadLogs')
            ->assertSet('showLogs', true)
            ->call('hideLogs')
            ->assertSet('showLogs', false)
            ->assertSet('logContent', '');
    });

    test('system page is accessible by admin', function () {
        $admin = createAdminForSystemTest();
        $company = Company::factory()->create();
        $company->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $this->actingAs($admin)
            ->get(route('admin.system'))
            ->assertOk();
    });
});
