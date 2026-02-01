<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ProjectType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($this->user)->create();
    $this->user->update(['current_company_id' => $this->company->id]);
    app()->instance('current.company', $this->company);
    $this->actingAs($this->user);
});

// Project Number Generation
test('project has auto-generated number', function () {
    $project = Project::factory()->create();

    $year = date('Y');
    expect($project->project_number)->toMatch("/^P-{$year}-\d{4}$/");
});

test('project numbers increment correctly', function () {
    $project1 = Project::factory()->create();
    $project2 = Project::factory()->create();

    preg_match('/P-\d{4}-(\d+)/', $project1->project_number, $matches1);
    preg_match('/P-\d{4}-(\d+)/', $project2->project_number, $matches2);

    expect((int) $matches2[1])->toBe((int) $matches1[1] + 1);
});

test('project number is not overwritten if provided', function () {
    $project = Project::factory()->create(['project_number' => 'CUSTOM-001']);

    expect($project->project_number)->toBe('CUSTOM-001');
});

// Relationships
test('project belongs to contact', function () {
    $contact = Contact::factory()->customer()->create();
    $project = Project::factory()->create(['contact_id' => $contact->id]);

    expect($project->contact->id)->toBe($contact->id);
});

test('project belongs to project type', function () {
    $type = ProjectType::factory()->create(['name' => 'Nybygg']);
    $project = Project::factory()->create(['project_type_id' => $type->id]);

    expect($project->projectType->id)->toBe($type->id);
    expect($project->projectType->name)->toBe('Nybygg');
});

test('project belongs to project status', function () {
    $status = ProjectStatus::factory()->create(['name' => 'Pågår']);
    $project = Project::factory()->create(['project_status_id' => $status->id]);

    expect($project->projectStatus->id)->toBe($status->id);
    expect($project->projectStatus->name)->toBe('Pågår');
});

// Budget Variance
test('project budget variance is positive when under budget', function () {
    $project = Project::factory()->create(['budget' => 100000]);

    // No lines, so total is 0
    expect($project->budget_variance)->toBe(100000.0);
});

test('project budget variance is null when no budget set', function () {
    $project = Project::factory()->create(['budget' => null]);

    expect($project->budget_variance)->toBeNull();
});

// Scopes
test('active scope filters correctly', function () {
    Project::factory()->count(3)->create(['is_active' => true]);
    Project::factory()->count(2)->create(['is_active' => false]);

    expect(Project::active()->count())->toBe(3);
});

test('ordered scope sorts by sort_order ascending then created_at descending', function () {
    $project1 = Project::factory()->create(['sort_order' => 0]);
    $project2 = Project::factory()->create(['sort_order' => 1]);
    $project3 = Project::factory()->create(['sort_order' => 2]);

    $projects = Project::ordered()->pluck('id')->toArray();

    // sort_order ascending, so 0 first, then 1, then 2
    // For same sort_order, created_at descending (newest first)
    expect($projects[0])->toBe($project1->id);
    expect($projects[1])->toBe($project2->id);
    expect($projects[2])->toBe($project3->id);
});

// Date Casts
test('project start_date is cast to date', function () {
    $project = Project::factory()->create(['start_date' => '2024-01-15']);

    expect($project->start_date)->toBeInstanceOf(Carbon\Carbon::class);
    expect($project->start_date->format('Y-m-d'))->toBe('2024-01-15');
});

test('project end_date is cast to date', function () {
    $project = Project::factory()->create(['end_date' => '2024-12-31']);

    expect($project->end_date)->toBeInstanceOf(Carbon\Carbon::class);
    expect($project->end_date->format('Y-m-d'))->toBe('2024-12-31');
});

// Decimal Casts
test('project budget is decimal', function () {
    $project = Project::factory()->create(['budget' => 123456.78]);

    expect((float) $project->budget)->toBe(123456.78);
});

test('project estimated_hours is decimal', function () {
    $project = Project::factory()->create(['estimated_hours' => 150.5]);

    expect((float) $project->estimated_hours)->toBe(150.5);
});

// Boolean Casts
test('project is_active is boolean', function () {
    $project = Project::factory()->create(['is_active' => true]);

    expect($project->is_active)->toBeTrue();
    expect($project->is_active)->toBeBool();
});
