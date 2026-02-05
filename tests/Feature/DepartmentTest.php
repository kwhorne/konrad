<?php

use App\Livewire\CompanyUserManager;
use App\Models\AccountingSettings;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createUserWithCompanyForDepartmentTest(string $role = 'owner'): array
{
    $user = User::factory()->create(['onboarding_completed' => true, 'is_economy' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);

    if ($role !== 'owner') {
        $company->users()->updateExistingPivot($user->id, ['role' => $role]);
    }

    return ['user' => $user->fresh(), 'company' => $company];
}

describe('Department Model', function () {
    test('department belongs to company', function () {
        $company = Company::factory()->create();
        $department = Department::factory()->create(['company_id' => $company->id]);

        expect($department->company->id)->toBe($company->id);
    });

    test('department scopes work correctly', function () {
        $company = Company::factory()->create();
        Department::factory()->create(['company_id' => $company->id, 'is_active' => true, 'sort_order' => 2]);
        Department::factory()->create(['company_id' => $company->id, 'is_active' => true, 'sort_order' => 1]);
        Department::factory()->create(['company_id' => $company->id, 'is_active' => false, 'sort_order' => 3]);

        $active = Department::query()->forCompany($company->id)->active()->get();
        expect($active)->toHaveCount(2);

        $ordered = Department::query()->forCompany($company->id)->ordered()->get();
        expect($ordered->first()->sort_order)->toBe(1);
    });
});

describe('AccountingSettings Model', function () {
    test('accounting settings belongs to company', function () {
        $company = Company::factory()->create();
        $settings = AccountingSettings::factory()->create(['company_id' => $company->id]);

        expect($settings->company->id)->toBe($company->id);
    });

    test('getOrCreate creates settings if not exists', function () {
        $company = Company::factory()->create();

        expect(AccountingSettings::where('company_id', $company->id)->exists())->toBeFalse();

        $settings = AccountingSettings::getOrCreate($company->id);

        expect($settings)->toBeInstanceOf(AccountingSettings::class)
            ->and($settings->company_id)->toBe($company->id)
            ->and($settings->departments_enabled)->toBeFalse();
    });

    test('getOrCreate returns existing settings', function () {
        $company = Company::factory()->create();
        $existing = AccountingSettings::factory()
            ->withDepartmentsEnabled()
            ->create(['company_id' => $company->id]);

        $settings = AccountingSettings::getOrCreate($company->id);

        expect($settings->id)->toBe($existing->id)
            ->and($settings->departments_enabled)->toBeTrue();
    });
});

describe('User Department in Company', function () {
    test('user can have department in company pivot', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();

        $department = Department::factory()->create(['company_id' => $company->id]);
        $company->users()->updateExistingPivot($user->id, ['department_id' => $department->id]);

        $pivotDepartmentId = $company->users()
            ->wherePivot('user_id', $user->id)
            ->first()
            ->pivot
            ->department_id;

        expect($pivotDepartmentId)->toBe($department->id);
    });

    test('user departmentInCurrentCompany returns correct department', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();

        $department = Department::factory()->create(['company_id' => $company->id]);
        $company->users()->updateExistingPivot($user->id, ['department_id' => $department->id]);

        $user->refresh();
        $userDepartment = $user->departmentInCurrentCompany();

        expect($userDepartment)->not->toBeNull()
            ->and($userDepartment->id)->toBe($department->id);
    });

    test('user departmentInCurrentCompany returns null when not assigned', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();

        $userDepartment = $user->departmentInCurrentCompany();

        expect($userDepartment)->toBeNull();
    });

    test('current_department_id attribute works', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();

        $department = Department::factory()->create(['company_id' => $company->id]);
        $company->users()->updateExistingPivot($user->id, ['department_id' => $department->id]);

        $user->refresh();

        expect($user->current_department_id)->toBe($department->id);
    });
});

describe('CompanyService Department Update', function () {
    test('can update user department', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();

        $department = Department::factory()->create(['company_id' => $company->id]);
        $service = app(CompanyService::class);

        $result = $service->updateUserDepartment($company, $user, $department->id);

        expect($result)->toBeTrue();

        $pivotDepartmentId = $company->users()
            ->wherePivot('user_id', $user->id)
            ->first()
            ->pivot
            ->department_id;

        expect($pivotDepartmentId)->toBe($department->id);
    });

    test('can clear user department', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();

        $department = Department::factory()->create(['company_id' => $company->id]);
        $company->users()->updateExistingPivot($user->id, ['department_id' => $department->id]);

        $service = app(CompanyService::class);
        $result = $service->updateUserDepartment($company, $user, null);

        expect($result)->toBeTrue();

        $pivotDepartmentId = $company->users()
            ->wherePivot('user_id', $user->id)
            ->first()
            ->pivot
            ->department_id;

        expect($pivotDepartmentId)->toBeNull();
    });

    test('cannot assign department from different company', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();

        $otherCompany = Company::factory()->create();
        $otherDepartment = Department::factory()->create(['company_id' => $otherCompany->id]);

        $service = app(CompanyService::class);
        $result = $service->updateUserDepartment($company, $user, $otherDepartment->id);

        expect($result)->toBeFalse();
    });

    test('cannot update department for user not in company', function () {
        $company = Company::factory()->create();
        $outsideUser = User::factory()->create();
        $department = Department::factory()->create(['company_id' => $company->id]);

        $service = app(CompanyService::class);
        $result = $service->updateUserDepartment($company, $outsideUser, $department->id);

        expect($result)->toBeFalse();
    });
});

describe('CompanyUserManager Department UI', function () {
    test('department column is hidden when departments disabled', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(CompanyUserManager::class)
            ->assertViewHas('departmentsEnabled', false)
            ->assertDontSee('Avdeling');
    });

    test('department column is shown when departments enabled', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        AccountingSettings::factory()
            ->withDepartmentsEnabled()
            ->create(['company_id' => $company->id]);
        Department::factory()->create(['company_id' => $company->id]);
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(CompanyUserManager::class)
            ->assertViewHas('departmentsEnabled', true)
            ->assertSee('Avdeling');
    });

    test('departments are passed to view when enabled', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        AccountingSettings::factory()
            ->withDepartmentsEnabled()
            ->create(['company_id' => $company->id]);
        $department = Department::factory()->create(['company_id' => $company->id, 'is_active' => true]);
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(CompanyUserManager::class)
            ->assertViewHas('departments', function ($departments) use ($department) {
                return $departments->contains('id', $department->id);
            });
    });

    test('manager can update user department', function () {
        ['user' => $owner, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        AccountingSettings::factory()
            ->withDepartmentsEnabled()
            ->create(['company_id' => $company->id]);
        $department = Department::factory()->create(['company_id' => $company->id]);

        $member = User::factory()->create(['onboarding_completed' => true]);
        $company->users()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        app()->instance('current.company', $company);

        $this->actingAs($owner);

        Livewire::test(CompanyUserManager::class)
            ->call('openEditRoleModal', $member->id)
            ->set('editingDepartmentId', $department->id)
            ->call('updateRole')
            ->assertDispatched('toast');

        $pivotDepartmentId = $company->users()
            ->wherePivot('user_id', $member->id)
            ->first()
            ->pivot
            ->department_id;

        expect($pivotDepartmentId)->toBe($department->id);
    });

    test('user department is displayed in table', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        AccountingSettings::factory()
            ->withDepartmentsEnabled()
            ->create(['company_id' => $company->id]);
        $department = Department::factory()->create([
            'company_id' => $company->id,
            'name' => 'Salgsavdeling',
        ]);
        $company->users()->updateExistingPivot($user->id, ['department_id' => $department->id]);

        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(CompanyUserManager::class)
            ->assertSee('Salgsavdeling');
    });
});

describe('DepartmentManager Component', function () {
    test('renders successfully', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\DepartmentManager::class)
            ->assertOk();
    });

    test('can create department', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\DepartmentManager::class)
            ->call('create')
            ->set('code', 'SAL')
            ->set('name', 'Salg')
            ->set('description', 'Salgsavdelingen')
            ->call('save')
            ->assertDispatched('toast');

        expect(Department::where('code', 'SAL')->exists())->toBeTrue();
    });

    test('can edit department', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'OLD',
            'name' => 'Old Name',
        ]);
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\DepartmentManager::class)
            ->call('edit', $department->id)
            ->set('code', 'NEW')
            ->set('name', 'New Name')
            ->call('save')
            ->assertDispatched('toast');

        expect($department->fresh()->code)->toBe('NEW')
            ->and($department->fresh()->name)->toBe('New Name');
    });

    test('can delete unused department', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        $department = Department::factory()->create(['company_id' => $company->id]);
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\DepartmentManager::class)
            ->call('delete', $department->id)
            ->assertDispatched('toast');

        expect(Department::find($department->id))->toBeNull();
    });

    test('shows warning when departments not enabled', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\DepartmentManager::class)
            ->assertViewHas('departmentsEnabled', false)
            ->assertSee('Avdelinger er ikke aktivert');
    });
});

describe('AccountingSettingsManager Component', function () {
    test('renders successfully', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\AccountingSettingsManager::class)
            ->assertOk();
    });

    test('can enable departments', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\AccountingSettingsManager::class)
            ->set('departments_enabled', true)
            ->call('save')
            ->assertDispatched('toast');

        $settings = AccountingSettings::forCompany($company->id);
        expect($settings->departments_enabled)->toBeTrue();
    });

    test('can set default department', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        $department = Department::factory()->create(['company_id' => $company->id]);
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\AccountingSettingsManager::class)
            ->set('departments_enabled', true)
            ->set('default_department_id', $department->id)
            ->call('save')
            ->assertDispatched('toast');

        $settings = AccountingSettings::forCompany($company->id);
        expect($settings->default_department_id)->toBe($department->id);
    });

    test('disabling departments clears related settings', function () {
        ['user' => $user, 'company' => $company] = createUserWithCompanyForDepartmentTest();
        $department = Department::factory()->create(['company_id' => $company->id]);
        AccountingSettings::factory()
            ->withRequiredDepartment()
            ->create([
                'company_id' => $company->id,
                'default_department_id' => $department->id,
            ]);
        app()->instance('current.company', $company);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\AccountingSettingsManager::class)
            ->set('departments_enabled', false)
            ->call('save')
            ->assertDispatched('toast');

        $settings = AccountingSettings::forCompany($company->id);
        expect($settings->departments_enabled)->toBeFalse()
            ->and($settings->require_department_on_vouchers)->toBeFalse()
            ->and($settings->default_department_id)->toBeNull();
    });
});
