<?php

use App\Models\Company;
use App\Models\CompanyModule;
use App\Models\Module;
use App\Models\User;
use App\Services\AdminAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAdminForAnalyticsTest(): User
{
    return User::factory()->create([
        'is_admin' => true,
        'onboarding_completed' => true,
    ]);
}

describe('Admin Analytics Page', function () {
    test('admin can access analytics page', function () {
        $admin = createAdminForAnalyticsTest();
        $company = Company::factory()->create();
        $company->users()->attach($admin->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $this->actingAs($admin)
            ->get(route('admin.analytics'))
            ->assertOk();
    });

    test('non-admin cannot access analytics page', function () {
        $user = User::factory()->create(['onboarding_completed' => true]);
        $company = Company::factory()->create();
        $company->users()->attach($user->id, ['role' => 'owner', 'is_default' => true, 'joined_at' => now()]);

        $this->actingAs($user)
            ->get(route('admin.analytics'))
            ->assertForbidden();
    });
});

describe('AdminAnalyticsService', function () {
    test('calculates MRR from active enabled modules', function () {
        $service = app(AdminAnalyticsService::class);

        $module = Module::factory()->create([
            'is_premium' => true,
            'is_active' => true,
            'price_monthly' => 50000,
        ]);

        $company = Company::factory()->create(['is_active' => true]);

        CompanyModule::create([
            'company_id' => $company->id,
            'module_id' => $module->id,
            'is_enabled' => true,
            'enabled_at' => now(),
            'expires_at' => null,
            'enabled_by' => 'admin',
        ]);

        $mrr = $service->calculateMrr();

        expect($mrr)->toBe(50000);
    });

    test('excludes expired modules from MRR', function () {
        $service = app(AdminAnalyticsService::class);

        $module = Module::factory()->create([
            'is_premium' => true,
            'is_active' => true,
            'price_monthly' => 50000,
        ]);

        $company = Company::factory()->create(['is_active' => true]);

        CompanyModule::create([
            'company_id' => $company->id,
            'module_id' => $module->id,
            'is_enabled' => true,
            'enabled_at' => now()->subMonth(),
            'expires_at' => now()->subDay(),
            'enabled_by' => 'admin',
        ]);

        $mrr = $service->calculateMrr();

        expect($mrr)->toBe(0);
    });

    test('getMetrics returns all required keys', function () {
        $service = app(AdminAnalyticsService::class);
        $metrics = $service->getMetrics();

        expect($metrics)->toHaveKeys([
            'totalCompanies',
            'activeCompanies',
            'newCompaniesThisMonth',
            'churnThisMonth',
            'totalUsers',
            'newUsersThisWeek',
            'newUsersThisMonth',
            'mrr',
            'arr',
            'activePremiumSubscriptions',
            'modulePopularity',
            'revenuePerModule',
            'recentSignups',
        ]);
    });

    test('ARR is 12x MRR', function () {
        $service = app(AdminAnalyticsService::class);

        $module = Module::factory()->create([
            'is_premium' => true,
            'is_active' => true,
            'price_monthly' => 10000,
        ]);

        $company = Company::factory()->create(['is_active' => true]);

        CompanyModule::create([
            'company_id' => $company->id,
            'module_id' => $module->id,
            'is_enabled' => true,
            'enabled_at' => now(),
            'expires_at' => null,
            'enabled_by' => 'admin',
        ]);

        $metrics = $service->getMetrics();

        expect($metrics['arr'])->toBe($metrics['mrr'] * 12);
    });
});
