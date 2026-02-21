<?php

use App\Livewire\ProductManager;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($this->user)->create();
    $this->user->update(['current_company_id' => $this->company->id]);
    app()->instance('current.company', $this->company);
    $this->actingAs($this->user);
});

it('shows the new group button when form is hidden', function () {
    Livewire::test(ProductManager::class)
        ->call('openModal')
        ->assertSet('showNewGroupForm', false)
        ->assertSee('Ny gruppe');
});

it('opens inline group form', function () {
    Livewire::test(ProductManager::class)
        ->call('openModal')
        ->call('openNewGroupForm')
        ->assertSet('showNewGroupForm', true);
});

it('auto-generates code from group name', function () {
    Livewire::test(ProductManager::class)
        ->call('openModal')
        ->call('openNewGroupForm')
        ->set('newGroupName', 'Konsulenttjenester')
        ->assertSet('newGroupCode', 'KONSULENTTJENESTER');
});

it('creates a new product group and selects it', function () {
    Livewire::test(ProductManager::class)
        ->call('openModal')
        ->call('openNewGroupForm')
        ->set('newGroupName', 'Materiell')
        ->set('newGroupCode', 'MATERIELL')
        ->call('createGroup')
        ->assertSet('showNewGroupForm', false)
        ->assertSet('newGroupName', '')
        ->assertSet('newGroupCode', '');

    $group = ProductGroup::where('name', 'Materiell')->first();
    expect($group)->not->toBeNull();
    expect($group->code)->toBe('MATERIELL');
    expect($group->is_active)->toBeTrue();
});

it('auto-selects the newly created group', function () {
    $component = Livewire::test(ProductManager::class)
        ->call('openModal')
        ->call('openNewGroupForm')
        ->set('newGroupName', 'Nygruppe')
        ->set('newGroupCode', 'NYGRUPPE')
        ->call('createGroup');

    $group = ProductGroup::where('code', 'NYGRUPPE')->first();
    $component->assertSet('product_group_id', $group->id);
});

it('validates group name is required', function () {
    Livewire::test(ProductManager::class)
        ->call('openModal')
        ->call('openNewGroupForm')
        ->set('newGroupName', '')
        ->set('newGroupCode', 'KODE')
        ->call('createGroup')
        ->assertHasErrors(['newGroupName']);
});

it('validates group code is required', function () {
    Livewire::test(ProductManager::class)
        ->call('openModal')
        ->call('openNewGroupForm')
        ->set('newGroupName', 'Testnavn')
        ->set('newGroupCode', '')
        ->call('createGroup')
        ->assertHasErrors(['newGroupCode']);
});

it('validates group code is unique within company', function () {
    ProductGroup::factory()->create(['code' => 'DUPLIKAT']);

    Livewire::test(ProductManager::class)
        ->call('openModal')
        ->call('openNewGroupForm')
        ->set('newGroupName', 'Duplikat')
        ->set('newGroupCode', 'DUPLIKAT')
        ->call('createGroup')
        ->assertHasErrors(['newGroupCode']);
});

it('cancels inline group form', function () {
    Livewire::test(ProductManager::class)
        ->call('openModal')
        ->call('openNewGroupForm')
        ->set('newGroupName', 'Noe')
        ->call('cancelNewGroup')
        ->assertSet('showNewGroupForm', false)
        ->assertSet('newGroupName', '')
        ->assertSet('newGroupCode', '');
});

it('resets group form when main modal is closed', function () {
    Livewire::test(ProductManager::class)
        ->call('openModal')
        ->call('openNewGroupForm')
        ->set('newGroupName', 'Test')
        ->call('closeModal')
        ->assertSet('showNewGroupForm', false)
        ->assertSet('newGroupName', '');
});
