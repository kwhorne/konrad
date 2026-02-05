<?php

use App\Livewire\AltinnDashboard;
use App\Models\AltinnSubmission;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $owner = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($owner)->create();
    app()->instance('current.company', $this->company);

    $this->economyUser = User::factory()->create([
        'is_economy' => true,
        'is_admin' => false,
        'current_company_id' => $this->company->id,
    ]);
    $this->company->users()->attach($this->economyUser->id, ['role' => 'member', 'joined_at' => now()]);

    $this->regularUser = User::factory()->create([
        'is_economy' => false,
        'is_admin' => false,
        'current_company_id' => $this->company->id,
    ]);
    $this->company->users()->attach($this->regularUser->id, ['role' => 'member', 'joined_at' => now()]);

    $this->admin = User::factory()->create([
        'is_admin' => true,
        'is_economy' => false,
        'current_company_id' => $this->company->id,
    ]);
    $this->company->users()->attach($this->admin->id, ['role' => 'member', 'joined_at' => now()]);
});

describe('AltinnDashboard authorization', function () {
    test('economy user can access dashboard', function () {
        $this->actingAs($this->economyUser);

        Livewire::test(AltinnDashboard::class)
            ->assertOk();
    });

    test('admin can access dashboard', function () {
        $this->actingAs($this->admin);

        Livewire::test(AltinnDashboard::class)
            ->assertOk();
    });

    test('regular user cannot access dashboard', function () {
        $this->actingAs($this->regularUser);

        Livewire::test(AltinnDashboard::class)
            ->assertForbidden();
    });
});

describe('AltinnDashboard cross-company data isolation', function () {
    test('submission history does not leak data from other companies', function () {
        $this->actingAs($this->economyUser);

        // Create submission for our company
        $ownSubmission = AltinnSubmission::create([
            'company_id' => $this->company->id,
            'submission_type' => AltinnSubmission::TYPE_SKATTEMELDING,
            'year' => now()->year,
            'status' => AltinnSubmission::STATUS_DRAFT,
            'created_by' => $this->economyUser->id,
        ]);

        // Create submission for a different company (different type to avoid unique constraint)
        $otherCompany = Company::factory()->create();
        $otherSubmission = AltinnSubmission::create([
            'company_id' => $otherCompany->id,
            'submission_type' => AltinnSubmission::TYPE_ARSREGNSKAP,
            'year' => now()->year,
            'status' => AltinnSubmission::STATUS_SUBMITTED,
            'created_by' => $this->economyUser->id,
        ]);

        $component = Livewire::test(AltinnDashboard::class);
        $submissions = $component->viewData('submissions');

        // Should only see our company's submission
        expect($submissions)->toHaveCount(1)
            ->and($submissions->first()->id)->toBe($ownSubmission->id);
    });
});
