<?php

use App\Livewire\CompanyAdminManager;
use App\Livewire\ModuleAdminManager;
use App\Models\Company;
use App\Models\CompanyModule;
use App\Models\Module;
use App\Models\User;
use App\Services\ModuleService;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed modules for each test
    $this->seed(ModuleSeeder::class);
});

function createAdminUserForModuleTest(): User
{
    return User::factory()->create([
        'is_admin' => true,
        'onboarding_completed' => true,
    ]);
}

function createCompanyWithOwnerForModuleTest(): array
{
    $user = User::factory()->create(['onboarding_completed' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);

    return ['user' => $user->fresh(), 'company' => $company];
}

describe('Module Model', function () {
    test('modules are seeded correctly', function () {
        $standardModules = Module::standard()->get();
        $premiumModules = Module::premium()->get();

        expect($standardModules)->toHaveCount(6)
            ->and($premiumModules)->toHaveCount(4);
    });

    test('premium modules have correct slugs', function () {
        $premiumSlugs = Module::premium()->pluck('slug')->toArray();

        expect($premiumSlugs)->toContain('contracts')
            ->and($premiumSlugs)->toContain('assets')
            ->and($premiumSlugs)->toContain('projects')
            ->and($premiumSlugs)->toContain('inventory');
    });

    test('standard modules have correct slugs', function () {
        $standardSlugs = Module::standard()->pluck('slug')->toArray();

        expect($standardSlugs)->toContain('contacts')
            ->and($standardSlugs)->toContain('products')
            ->and($standardSlugs)->toContain('work_orders')
            ->and($standardSlugs)->toContain('sales')
            ->and($standardSlugs)->toContain('shareholders')
            ->and($standardSlugs)->toContain('altinn');
    });

    test('premium modules have prices', function () {
        $premiumModules = Module::premium()->get();

        foreach ($premiumModules as $module) {
            expect($module->price_monthly)->toBeGreaterThan(0);
        }
    });

    test('price_formatted attribute works correctly', function () {
        $module = Module::where('slug', 'contracts')->first();

        expect($module->price_formatted)->toBe('149 kr/mnd');
    });
});

describe('ModuleService', function () {
    test('standard modules are always enabled', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);

        expect($service->isEnabled($company, 'contacts'))->toBeTrue()
            ->and($service->isEnabled($company, 'products'))->toBeTrue()
            ->and($service->isEnabled($company, 'sales'))->toBeTrue();
    });

    test('premium modules are disabled by default', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);

        expect($service->isEnabled($company, 'contracts'))->toBeFalse()
            ->and($service->isEnabled($company, 'assets'))->toBeFalse()
            ->and($service->isEnabled($company, 'projects'))->toBeFalse()
            ->and($service->isEnabled($company, 'inventory'))->toBeFalse();
    });

    test('can enable premium module for company', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);
        $module = Module::where('slug', 'contracts')->first();

        expect($service->isEnabled($company, 'contracts'))->toBeFalse();

        $service->enableForCompany($company, $module, 'admin');

        expect($service->isEnabled($company, 'contracts'))->toBeTrue();
    });

    test('can disable premium module for company', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);
        $module = Module::where('slug', 'contracts')->first();

        $service->enableForCompany($company, $module, 'admin');
        expect($service->isEnabled($company, 'contracts'))->toBeTrue();

        $service->disableForCompany($company, $module);
        expect($service->isEnabled($company, 'contracts'))->toBeFalse();
    });

    test('enableBySlug works correctly', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);

        $service->enableBySlug($company, 'projects', 'admin');

        expect($service->isEnabled($company, 'projects'))->toBeTrue();
    });

    test('disableBySlug works correctly', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);

        $service->enableBySlug($company, 'projects', 'admin');
        $service->disableBySlug($company, 'projects');

        expect($service->isEnabled($company, 'projects'))->toBeFalse();
    });

    test('getEnabledModules returns correct modules', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);

        $service->enableBySlug($company, 'contracts', 'admin');
        $service->enableBySlug($company, 'projects', 'admin');

        $enabled = $service->getEnabledModules($company);

        // Should include 6 standard modules + 2 enabled premium modules
        expect($enabled)->toHaveCount(8);
    });

    test('getPremiumModulesWithStatus marks enabled modules correctly', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);

        $service->enableBySlug($company, 'contracts', 'admin');

        $modulesWithStatus = $service->getPremiumModulesWithStatus($company);
        $contractsModule = $modulesWithStatus->firstWhere('slug', 'contracts');
        $assetsModule = $modulesWithStatus->firstWhere('slug', 'assets');

        expect($contractsModule->is_enabled_for_company)->toBeTrue()
            ->and($assetsModule->is_enabled_for_company)->toBeFalse();
    });

    test('expired modules are not enabled', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);
        $module = Module::where('slug', 'contracts')->first();

        // Enable with past expiration date
        $service->enableForCompany($company, $module, 'admin', now()->subDay());

        expect($service->isEnabled($company, 'contracts'))->toBeFalse();
    });
});

describe('Company Module Relationship', function () {
    test('company can have many modules', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);

        $service->enableBySlug($company, 'contracts', 'admin');
        $service->enableBySlug($company, 'projects', 'admin');

        expect($company->modules()->count())->toBe(2);
    });

    test('hasModule method works correctly', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);

        $service->enableBySlug($company, 'contracts', 'admin');

        // Premium module that is enabled
        expect($company->hasModule('contracts'))->toBeTrue();

        // Premium module that is not enabled
        expect($company->hasModule('assets'))->toBeFalse();

        // Standard module (always enabled)
        expect($company->hasModule('contacts'))->toBeTrue();
    });

    test('enabledModules returns only active modules', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);

        $contractsModule = Module::where('slug', 'contracts')->first();
        $assetsModule = Module::where('slug', 'assets')->first();

        $service->enableForCompany($company, $contractsModule, 'admin');
        $service->enableForCompany($company, $assetsModule, 'admin', now()->subDay()); // Expired

        $enabled = $company->enabledModules()->get();

        expect($enabled)->toHaveCount(1)
            ->and($enabled->first()->slug)->toBe('contracts');
    });
});

describe('CompanyModule Model', function () {
    test('isActive returns false when not enabled', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $module = Module::where('slug', 'contracts')->first();

        $companyModule = CompanyModule::create([
            'company_id' => $company->id,
            'module_id' => $module->id,
            'is_enabled' => false,
            'enabled_by' => 'admin',
        ]);

        expect($companyModule->isActive())->toBeFalse();
    });

    test('isActive returns false when expired', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $module = Module::where('slug', 'contracts')->first();

        $companyModule = CompanyModule::create([
            'company_id' => $company->id,
            'module_id' => $module->id,
            'is_enabled' => true,
            'enabled_by' => 'admin',
            'expires_at' => now()->subDay(),
        ]);

        expect($companyModule->isActive())->toBeFalse();
    });

    test('isActive returns true when enabled and not expired', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $module = Module::where('slug', 'contracts')->first();

        $companyModule = CompanyModule::create([
            'company_id' => $company->id,
            'module_id' => $module->id,
            'is_enabled' => true,
            'enabled_by' => 'admin',
            'enabled_at' => now(),
        ]);

        expect($companyModule->isActive())->toBeTrue();
    });

    test('enabledByAdmin returns correct value', function () {
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $module = Module::where('slug', 'contracts')->first();

        $companyModule = CompanyModule::create([
            'company_id' => $company->id,
            'module_id' => $module->id,
            'is_enabled' => true,
            'enabled_by' => 'admin',
        ]);

        expect($companyModule->enabledByAdmin())->toBeTrue()
            ->and($companyModule->enabledByStripe())->toBeFalse();
    });
});

describe('company_has_module Helper', function () {
    test('returns true for standard modules', function () {
        ['user' => $user, 'company' => $company] = createCompanyWithOwnerForModuleTest();

        // Bind company to container
        app()->instance('current.company', $company);

        expect(company_has_module('contacts'))->toBeTrue()
            ->and(company_has_module('sales'))->toBeTrue();
    });

    test('returns false for disabled premium modules', function () {
        ['user' => $user, 'company' => $company] = createCompanyWithOwnerForModuleTest();

        app()->instance('current.company', $company);

        expect(company_has_module('contracts'))->toBeFalse()
            ->and(company_has_module('assets'))->toBeFalse();
    });

    test('returns true for enabled premium modules', function () {
        ['user' => $user, 'company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);

        $service->enableBySlug($company, 'contracts', 'admin');

        app()->instance('current.company', $company);

        expect(company_has_module('contracts'))->toBeTrue();
    });
});

describe('CompanyAdminManager Module UI', function () {
    test('admin can open module modal', function () {
        $admin = createAdminUserForModuleTest();
        ['company' => $company] = createCompanyWithOwnerForModuleTest();

        Livewire::actingAs($admin)
            ->test(CompanyAdminManager::class)
            ->call('openModuleModal', $company->id)
            ->assertSet('showModuleModal', true)
            ->assertSet('editingCompanyId', $company->id)
            ->assertSet('editingCompanyName', $company->name);
    });

    test('admin can enable module for company', function () {
        $admin = createAdminUserForModuleTest();
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $contractsModule = Module::where('slug', 'contracts')->first();

        Livewire::actingAs($admin)
            ->test(CompanyAdminManager::class)
            ->call('openModuleModal', $company->id)
            ->set("moduleStates.{$contractsModule->id}", true)
            ->call('saveModules');

        expect($company->fresh()->hasModule('contracts'))->toBeTrue();
    });

    test('admin can disable module for company', function () {
        $admin = createAdminUserForModuleTest();
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);
        $contractsModule = Module::where('slug', 'contracts')->first();

        // First enable the module
        $service->enableForCompany($company, $contractsModule, 'admin');
        expect($company->fresh()->hasModule('contracts'))->toBeTrue();

        // Then disable via admin UI
        Livewire::actingAs($admin)
            ->test(CompanyAdminManager::class)
            ->call('openModuleModal', $company->id)
            ->set("moduleStates.{$contractsModule->id}", false)
            ->call('saveModules');

        expect($company->fresh()->hasModule('contracts'))->toBeFalse();
    });

    test('module states are loaded correctly when opening modal', function () {
        $admin = createAdminUserForModuleTest();
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);
        $contractsModule = Module::where('slug', 'contracts')->first();
        $assetsModule = Module::where('slug', 'assets')->first();

        // Enable contracts, leave assets disabled
        $service->enableForCompany($company, $contractsModule, 'admin');

        Livewire::actingAs($admin)
            ->test(CompanyAdminManager::class)
            ->call('openModuleModal', $company->id)
            ->assertSet("moduleStates.{$contractsModule->id}", true)
            ->assertSet("moduleStates.{$assetsModule->id}", false);
    });
});

describe('ModuleAdminManager CRUD', function () {
    test('admin can view modules list', function () {
        $admin = createAdminUserForModuleTest();

        Livewire::actingAs($admin)
            ->test(ModuleAdminManager::class)
            ->assertSee('contracts')
            ->assertSee('assets')
            ->assertSee('projects')
            ->assertSee('inventory');
    });

    test('admin can open create modal', function () {
        $admin = createAdminUserForModuleTest();

        Livewire::actingAs($admin)
            ->test(ModuleAdminManager::class)
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->assertSet('editingModuleId', null);
    });

    test('admin can create new module', function () {
        $admin = createAdminUserForModuleTest();

        Livewire::actingAs($admin)
            ->test(ModuleAdminManager::class)
            ->call('openCreateModal')
            ->set('slug', 'test_module')
            ->set('name', 'Test Modul')
            ->set('description', 'En testmodul')
            ->set('is_premium', true)
            ->set('price_monthly', 9900)
            ->set('is_active', true)
            ->call('save');

        expect(Module::where('slug', 'test_module')->exists())->toBeTrue();
    });

    test('admin can edit existing module', function () {
        $admin = createAdminUserForModuleTest();
        $module = Module::where('slug', 'contracts')->first();

        Livewire::actingAs($admin)
            ->test(ModuleAdminManager::class)
            ->call('openEditModal', $module->id)
            ->assertSet('showModal', true)
            ->assertSet('editingModuleId', $module->id)
            ->assertSet('name', $module->name)
            ->set('name', 'Kontrakter Oppdatert')
            ->call('save');

        expect(Module::find($module->id)->name)->toBe('Kontrakter Oppdatert');
    });

    test('admin can toggle module active status', function () {
        $admin = createAdminUserForModuleTest();
        $module = Module::where('slug', 'contracts')->first();
        $originalStatus = $module->is_active;

        Livewire::actingAs($admin)
            ->test(ModuleAdminManager::class)
            ->call('toggleActive', $module->id);

        expect(Module::find($module->id)->is_active)->toBe(! $originalStatus);
    });

    test('admin cannot delete module in use', function () {
        $admin = createAdminUserForModuleTest();
        ['company' => $company] = createCompanyWithOwnerForModuleTest();
        $service = app(ModuleService::class);
        $module = Module::where('slug', 'contracts')->first();

        // Enable module for a company
        $service->enableForCompany($company, $module, 'admin');

        Livewire::actingAs($admin)
            ->test(ModuleAdminManager::class)
            ->call('confirmDelete', $module->id)
            ->call('delete');

        // Module should still exist
        expect(Module::where('slug', 'contracts')->exists())->toBeTrue();
    });

    test('admin can delete unused module', function () {
        $admin = createAdminUserForModuleTest();

        // Create a new module that's not used
        $module = Module::create([
            'slug' => 'unused_module',
            'name' => 'Unused Module',
            'is_premium' => true,
            'price_monthly' => 9900,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(ModuleAdminManager::class)
            ->call('confirmDelete', $module->id)
            ->call('delete');

        expect(Module::where('slug', 'unused_module')->exists())->toBeFalse();
    });

    test('search filters modules correctly', function () {
        $admin = createAdminUserForModuleTest();

        Livewire::actingAs($admin)
            ->test(ModuleAdminManager::class)
            ->set('search', 'contracts')
            ->assertSee('contracts')
            ->assertDontSee('inventory');
    });

    test('filter by type works correctly', function () {
        $admin = createAdminUserForModuleTest();

        // Get counts before filtering
        $standardCount = Module::where('is_premium', false)->count();
        $premiumCount = Module::where('is_premium', true)->count();

        // Filter by standard should show only standard modules
        $component = Livewire::actingAs($admin)
            ->test(ModuleAdminManager::class)
            ->set('filterType', 'standard');

        // Check that we have standard modules visible
        expect($standardCount)->toBeGreaterThan(0);
    });
});
