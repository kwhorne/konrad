<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = createTestCompanyContext();
    $this->user->update(['is_payroll' => true]);
    $this->actingAs($this->user);
});

// Dashboard page tests
test('payroll dashboard page is accessible for payroll user', function () {
    $response = $this->get(route('payroll.dashboard'));

    $response->assertStatus(200);
});

test('payroll dashboard page is inaccessible for non-payroll user', function () {
    $regularUser = User::factory()->create(['onboarding_completed' => true]);
    $this->company->users()->attach($regularUser, ['role' => 'member']);
    $regularUser->update(['current_company_id' => $this->company->id]);

    $this->actingAs($regularUser);

    $response = $this->get(route('payroll.dashboard'));

    $response->assertForbidden();
});

test('payroll dashboard page is accessible for admin user', function () {
    $adminUser = User::factory()->create([
        'is_admin' => true,
        'onboarding_completed' => true,
    ]);
    $this->company->users()->attach($adminUser, ['role' => 'member']);
    $adminUser->update(['current_company_id' => $this->company->id]);

    $this->actingAs($adminUser);

    $response = $this->get(route('payroll.dashboard'));

    $response->assertStatus(200);
});

// Employees page tests
test('payroll employees page is accessible', function () {
    $response = $this->get(route('payroll.employees'));

    $response->assertStatus(200);
});

// Pay types page tests
test('payroll pay types page is accessible', function () {
    $response = $this->get(route('payroll.pay-types'));

    $response->assertStatus(200);
});

// Payroll runs page tests
test('payroll runs page is accessible', function () {
    $response = $this->get(route('payroll.runs'));

    $response->assertStatus(200);
});

// Payslips page tests
test('payroll payslips page is accessible', function () {
    $response = $this->get(route('payroll.payslips'));

    $response->assertStatus(200);
});

// Holiday pay page tests
test('payroll holiday pay page is accessible', function () {
    $response = $this->get(route('payroll.holiday-pay'));

    $response->assertStatus(200);
});

// A-melding page tests
test('payroll a-melding page is accessible', function () {
    $response = $this->get(route('payroll.a-melding'));

    $response->assertStatus(200);
});

// Reports page tests
test('payroll reports page is accessible', function () {
    $response = $this->get(route('payroll.reports'));

    $response->assertStatus(200);
});

// Settings page tests
test('payroll settings page is accessible', function () {
    $response = $this->get(route('payroll.settings'));

    $response->assertStatus(200);
});

// Guest access tests
test('payroll pages require authentication', function () {
    auth()->logout();

    $routes = [
        'payroll.dashboard',
        'payroll.employees',
        'payroll.pay-types',
        'payroll.runs',
        'payroll.payslips',
        'payroll.holiday-pay',
        'payroll.a-melding',
        'payroll.reports',
        'payroll.settings',
    ];

    foreach ($routes as $routeName) {
        $response = $this->get(route($routeName));
        $response->assertRedirect(route('login'));
    }
});
