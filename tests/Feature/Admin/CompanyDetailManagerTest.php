<?php

use App\Livewire\Admin\CompanyDetailManager;
use App\Models\Company;
use App\Models\CompanyModule;
use App\Models\Module;
use App\Models\PlatformInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createAdminForDetailTest(): User
{
    return User::factory()->create([
        'is_admin' => true,
        'onboarding_completed' => true,
    ]);
}

describe('CompanyDetailManager Component', function () {
    test('renders successfully for admin', function () {
        $admin = createAdminForDetailTest();
        $adminCompany = Company::factory()->create();
        $adminCompany->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $company = Company::factory()->create(['name' => 'Test Selskap AS']);

        $this->actingAs($admin);

        Livewire::test(CompanyDetailManager::class, ['companyId' => $company->id])
            ->assertOk()
            ->assertSet('name', 'Test Selskap AS');
    });

    test('loads company info into form on mount', function () {
        $admin = createAdminForDetailTest();
        $adminCompany = Company::factory()->create();
        $adminCompany->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $company = Company::factory()->create([
            'name' => 'Min Bedrift AS',
            'email' => 'post@min-bedrift.no',
            'city' => 'Oslo',
        ]);

        $this->actingAs($admin);

        Livewire::test(CompanyDetailManager::class, ['companyId' => $company->id])
            ->assertSet('name', 'Min Bedrift AS')
            ->assertSet('email', 'post@min-bedrift.no')
            ->assertSet('city', 'Oslo');
    });

    test('can save company information', function () {
        $admin = createAdminForDetailTest();
        $adminCompany = Company::factory()->create();
        $adminCompany->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $company = Company::factory()->create(['name' => 'Gammelt Navn AS']);

        $this->actingAs($admin);

        Livewire::test(CompanyDetailManager::class, ['companyId' => $company->id])
            ->set('name', 'Nytt Navn AS')
            ->set('city', 'Bergen')
            ->call('save')
            ->assertHasNoErrors();

        expect($company->fresh()->name)->toBe('Nytt Navn AS')
            ->and($company->fresh()->city)->toBe('Bergen');
    });

    test('save validates required name', function () {
        $admin = createAdminForDetailTest();
        $adminCompany = Company::factory()->create();
        $adminCompany->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $company = Company::factory()->create();

        $this->actingAs($admin);

        Livewire::test(CompanyDetailManager::class, ['companyId' => $company->id])
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    });

    test('can open module modal', function () {
        $admin = createAdminForDetailTest();
        $adminCompany = Company::factory()->create();
        $adminCompany->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $company = Company::factory()->create();

        $this->actingAs($admin);

        Livewire::test(CompanyDetailManager::class, ['companyId' => $company->id])
            ->assertSet('showModuleModal', false)
            ->call('openModuleModal')
            ->assertSet('showModuleModal', true);
    });

    test('can enable module with expiry date', function () {
        $admin = createAdminForDetailTest();
        $adminCompany = Company::factory()->create();
        $adminCompany->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $company = Company::factory()->create();
        $module = Module::factory()->create(['is_premium' => true, 'is_active' => true]);

        $this->actingAs($admin);

        $component = Livewire::test(CompanyDetailManager::class, ['companyId' => $company->id])
            ->call('openModuleModal')
            ->set("moduleStates.{$module->id}", true)
            ->set("moduleExpiries.{$module->id}", '2030-12-31')
            ->call('saveModules');

        $companyModule = CompanyModule::where('company_id', $company->id)
            ->where('module_id', $module->id)
            ->first();

        expect($companyModule)->not->toBeNull()
            ->and($companyModule->is_enabled)->toBeTrue()
            ->and($companyModule->expires_at->format('Y-m-d'))->toBe('2030-12-31');
    });

    test('can create an invoice', function () {
        $admin = createAdminForDetailTest();
        $adminCompany = Company::factory()->create();
        $adminCompany->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $company = Company::factory()->create();

        $this->actingAs($admin);

        Livewire::test(CompanyDetailManager::class, ['companyId' => $company->id])
            ->call('openInvoiceModal')
            ->set('invoiceDescription', 'Månedlig lisens mars 2026')
            ->set('invoiceAmount', '299')
            ->set('invoiceDueDate', now()->addDays(14)->format('Y-m-d'))
            ->call('createInvoice')
            ->assertSet('showInvoiceModal', false)
            ->assertHasNoErrors();

        $invoice = PlatformInvoice::where('company_id', $company->id)->first();
        expect($invoice)->not->toBeNull()
            ->and($invoice->description)->toBe('Månedlig lisens mars 2026')
            ->and($invoice->amount)->toBe(29900)
            ->and($invoice->invoice_number)->toStartWith('KON-');
    });

    test('can mark invoice as paid', function () {
        $admin = createAdminForDetailTest();
        $adminCompany = Company::factory()->create();
        $adminCompany->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $company = Company::factory()->create();
        $invoice = PlatformInvoice::factory()->create(['company_id' => $company->id]);

        $this->actingAs($admin);

        Livewire::test(CompanyDetailManager::class, ['companyId' => $company->id])
            ->call('markAsPaid', $invoice->id);

        expect($invoice->fresh()->isPaid())->toBeTrue();
    });

    test('can mark invoice as unpaid', function () {
        $admin = createAdminForDetailTest();
        $adminCompany = Company::factory()->create();
        $adminCompany->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $company = Company::factory()->create();
        $invoice = PlatformInvoice::factory()->paid()->create(['company_id' => $company->id]);

        $this->actingAs($admin);

        Livewire::test(CompanyDetailManager::class, ['companyId' => $company->id])
            ->call('markAsUnpaid', $invoice->id);

        expect($invoice->fresh()->isPaid())->toBeFalse();
    });

    test('invoice validates required fields', function () {
        $admin = createAdminForDetailTest();
        $adminCompany = Company::factory()->create();
        $adminCompany->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $company = Company::factory()->create();

        $this->actingAs($admin);

        Livewire::test(CompanyDetailManager::class, ['companyId' => $company->id])
            ->call('openInvoiceModal')
            ->set('invoiceDescription', '')
            ->set('invoiceAmount', '')
            ->call('createInvoice')
            ->assertHasErrors(['invoiceDescription', 'invoiceAmount']);
    });

    test('company detail page is accessible', function () {
        $admin = createAdminForDetailTest();
        $adminCompany = Company::factory()->create();
        $adminCompany->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $company = Company::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.company-detail', $company->id))
            ->assertOk();
    });
});
