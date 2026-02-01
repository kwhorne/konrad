<?php

use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Services\CompanyAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = createTestCompanyContext();
    $this->user->update(['is_economy' => true]);
    $this->actingAs($this->user);
});

test('analysis page requires economy access', function () {
    $regularUser = \App\Models\User::factory()->create(['onboarding_completed' => true]);
    $this->company->users()->attach($regularUser, ['role' => 'member']);
    $regularUser->update(['current_company_id' => $this->company->id, 'is_economy' => false]);

    $this->actingAs($regularUser);

    $response = $this->get(route('economy.analysis'));

    $response->assertForbidden();
});

test('analysis page is accessible for economy users', function () {
    $response = $this->get(route('economy.analysis'));

    $response->assertStatus(200);
    $response->assertSee('Selskapsanalyse');
});

test('analysis service gathers financial data', function () {
    $service = app(CompanyAnalysisService::class);

    $data = $service->gatherFinancialData($this->company);

    expect($data)->toHaveKeys([
        'company',
        'period',
        'revenue',
        'expenses',
        'receivables',
        'payables',
        'cashflow',
        'profitability',
        'key_customers',
        'key_suppliers',
    ]);

    expect($data['company']['name'])->toBe($this->company->name);
});

test('analysis service calculates revenue correctly', function () {
    $service = app(CompanyAnalysisService::class);

    // Create invoice status
    $paidStatus = InvoiceStatus::create([
        'company_id' => $this->company->id,
        'name' => 'Betalt',
        'code' => 'paid',
        'color' => 'green',
        'is_active' => true,
    ]);

    // Create invoices
    Invoice::factory()->count(3)->create([
        'company_id' => $this->company->id,
        'invoice_status_id' => $paidStatus->id,
        'invoice_date' => now(),
        'total' => 10000,
    ]);

    $data = $service->gatherFinancialData($this->company);

    expect($data['revenue']['current_year'])->toBe(30000.0);
    expect($data['revenue']['invoice_count'])->toBe(3);
});

test('livewire component renders initial state', function () {
    \Livewire\Livewire::test(\App\Livewire\CompanyAnalysisManager::class)
        ->assertStatus(200)
        ->assertSee('Selskapsanalyse')
        ->assertSee('Start analyse');
});

test('livewire component can reset analysis', function () {
    \Livewire\Livewire::test(\App\Livewire\CompanyAnalysisManager::class)
        ->set('hasAnalysis', true)
        ->set('analysis', ['health_score' => 75, 'summary' => 'Test'])
        ->call('resetAnalysis')
        ->assertSet('hasAnalysis', false)
        ->assertSet('analysis', null);
});
