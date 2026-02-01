<?php

use App\Models\Project;
use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\TimesheetReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = createTestCompanyContext();
    $this->actingAs($this->user);
});

// Report Page Access Tests
test('report page requires manager role', function () {
    // Create a regular user
    $regularUser = User::factory()->create(['onboarding_completed' => true]);
    $this->company->users()->attach($regularUser, ['role' => 'member']);
    $regularUser->update(['current_company_id' => $this->company->id]);

    $this->actingAs($regularUser);

    $response = $this->get(route('timesheets.reports'));

    $response->assertForbidden();
});

test('report page is accessible for manager', function () {
    $response = $this->get(route('timesheets.reports'));

    $response->assertStatus(200);
    $response->assertSee('Timerapporter');
});

// TimesheetReportService Tests
test('service returns hours by project', function () {
    $service = app(TimesheetReportService::class);

    $project1 = Project::factory()->create(['company_id' => $this->company->id, 'name' => 'Project Alpha']);
    $project2 = Project::factory()->create(['company_id' => $this->company->id, 'name' => 'Project Beta']);

    $timesheet = Timesheet::factory()->draft()->thisWeek()->create(['user_id' => $this->user->id]);

    // Create entries for project 1 (15 hours)
    TimesheetEntry::factory()->withHours(8)->forProject($project1)->forDate($timesheet->week_start)->create(['timesheet_id' => $timesheet->id]);
    TimesheetEntry::factory()->withHours(7)->forProject($project1)->forDate($timesheet->week_start->copy()->addDay())->create(['timesheet_id' => $timesheet->id]);

    // Create entries for project 2 (5 hours)
    TimesheetEntry::factory()->withHours(5)->forProject($project2)->forDate($timesheet->week_start)->create(['timesheet_id' => $timesheet->id]);

    $results = $service->getHoursByProject($this->company);

    expect($results)->toHaveCount(2);
    expect($results->first()['project_name'])->toBe('Project Alpha');
    expect($results->first()['total_hours'])->toBe(15.0);
    expect($results->last()['project_name'])->toBe('Project Beta');
    expect($results->last()['total_hours'])->toBe(5.0);
});

test('service returns hours by employee', function () {
    $service = app(TimesheetReportService::class);

    $employee1 = User::factory()->create(['name' => 'Alice']);
    $employee2 = User::factory()->create(['name' => 'Bob']);
    $this->company->users()->attach([$employee1->id, $employee2->id], ['role' => 'member']);

    $timesheet1 = Timesheet::factory()->draft()->thisWeek()->create(['user_id' => $employee1->id, 'company_id' => $this->company->id]);
    $timesheet2 = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeek())->create(['user_id' => $employee2->id, 'company_id' => $this->company->id]);

    TimesheetEntry::factory()->withHours(20)->forDate($timesheet1->week_start)->create(['timesheet_id' => $timesheet1->id, 'company_id' => $this->company->id]);
    TimesheetEntry::factory()->withHours(10)->forDate($timesheet2->week_start)->create(['timesheet_id' => $timesheet2->id, 'company_id' => $this->company->id]);

    $results = $service->getHoursByEmployee($this->company);

    expect($results)->toHaveCount(2);
    // Results are sorted by total_hours desc
    expect($results->first()['user_name'])->toBe('Alice');
    expect($results->first()['total_hours'])->toBe(20.0);
});

test('service returns hours by work order', function () {
    $service = app(TimesheetReportService::class);

    $project = Project::factory()->create(['company_id' => $this->company->id]);
    $workOrder1 = WorkOrder::factory()->create(['company_id' => $this->company->id, 'project_id' => $project->id, 'title' => 'WO One']);
    $workOrder2 = WorkOrder::factory()->create(['company_id' => $this->company->id, 'project_id' => $project->id, 'title' => 'WO Two']);

    $timesheet = Timesheet::factory()->draft()->thisWeek()->create(['user_id' => $this->user->id]);

    TimesheetEntry::factory()->withHours(12)->forWorkOrder($workOrder1)->forDate($timesheet->week_start)->create(['timesheet_id' => $timesheet->id]);
    TimesheetEntry::factory()->withHours(8)->forWorkOrder($workOrder2)->forDate($timesheet->week_start)->create(['timesheet_id' => $timesheet->id]);

    $results = $service->getHoursByWorkOrder($this->company);

    expect($results)->toHaveCount(2);
    expect($results->first()['work_order_title'])->toBe('WO One');
    expect($results->first()['total_hours'])->toBe(12.0);
});

test('service returns summary statistics', function () {
    $service = app(TimesheetReportService::class);

    $project = Project::factory()->create(['company_id' => $this->company->id]);
    $timesheet = Timesheet::factory()->approved()->thisWeek()->create(['user_id' => $this->user->id]);

    TimesheetEntry::factory()->withHours(8)->forProject($project)->forDate($timesheet->week_start)->create([
        'timesheet_id' => $timesheet->id,
        'is_billable' => true,
    ]);
    TimesheetEntry::factory()->withHours(2)->forProject($project)->forDate($timesheet->week_start)->create([
        'timesheet_id' => $timesheet->id,
        'is_billable' => false,
    ]);

    $summary = $service->getSummary($this->company);

    expect($summary['total_hours'])->toBe(10.0);
    expect($summary['billable_hours'])->toBe(8.0);
    expect($summary['non_billable_hours'])->toBe(2.0);
    expect($summary['employee_count'])->toBe(1);
    expect($summary['project_count'])->toBe(1);
});

test('service filters by date range', function () {
    $service = app(TimesheetReportService::class);

    $project = Project::factory()->create(['company_id' => $this->company->id]);

    // Timesheet from 2 weeks ago
    $oldTimesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(2))->create(['user_id' => $this->user->id]);
    TimesheetEntry::factory()->withHours(10)->forProject($project)->forDate($oldTimesheet->week_start)->create(['timesheet_id' => $oldTimesheet->id]);

    // Timesheet from this week
    $newTimesheet = Timesheet::factory()->draft()->thisWeek()->create(['user_id' => $this->user->id]);
    TimesheetEntry::factory()->withHours(5)->forProject($project)->forDate($newTimesheet->week_start)->create(['timesheet_id' => $newTimesheet->id]);

    // Get only this week
    $results = $service->getHoursByProject($this->company, Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek());

    expect($results)->toHaveCount(1);
    expect($results->first()['total_hours'])->toBe(5.0);
});

test('service returns hours by week', function () {
    $service = app(TimesheetReportService::class);

    $project = Project::factory()->create(['company_id' => $this->company->id]);

    // Create timesheets for different weeks
    $week1 = Timesheet::factory()->draft()->thisWeek()->create(['user_id' => $this->user->id]);
    $week2 = Timesheet::factory()->draft()->lastWeek()->create(['user_id' => $this->user->id]);

    TimesheetEntry::factory()->withHours(40)->forProject($project)->forDate($week1->week_start)->create(['timesheet_id' => $week1->id]);
    TimesheetEntry::factory()->withHours(35)->forProject($project)->forDate($week2->week_start)->create(['timesheet_id' => $week2->id]);

    $results = $service->getHoursByWeek($this->company);

    expect($results)->toHaveCount(2);
    // Results are sorted by week_start desc (most recent first)
    expect($results->first()['total_hours'])->toBe(40.0);
});

test('service returns project hours by employee', function () {
    $service = app(TimesheetReportService::class);

    $project = Project::factory()->create(['company_id' => $this->company->id]);

    $employee1 = User::factory()->create(['name' => 'Employee 1']);
    $employee2 = User::factory()->create(['name' => 'Employee 2']);
    $this->company->users()->attach([$employee1->id, $employee2->id], ['role' => 'member']);

    $ts1 = Timesheet::factory()->draft()->thisWeek()->create(['user_id' => $employee1->id, 'company_id' => $this->company->id]);
    $ts2 = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeek())->create(['user_id' => $employee2->id, 'company_id' => $this->company->id]);

    TimesheetEntry::factory()->withHours(15)->forProject($project)->forDate($ts1->week_start)->create(['timesheet_id' => $ts1->id, 'company_id' => $this->company->id]);
    TimesheetEntry::factory()->withHours(10)->forProject($project)->forDate($ts2->week_start)->create(['timesheet_id' => $ts2->id, 'company_id' => $this->company->id]);

    $results = $service->getProjectHoursByEmployee($this->company, $project->id);

    expect($results)->toHaveCount(2);
    expect($results->first()['total_hours'])->toBe(15.0);
    expect($results->last()['total_hours'])->toBe(10.0);
});

// Livewire Component Tests
test('report manager component renders', function () {
    \Livewire\Livewire::test(\App\Livewire\TimesheetReportManager::class)
        ->assertStatus(200)
        ->assertSee('Rapporttype');
});

test('report manager can change report type', function () {
    \Livewire\Livewire::test(\App\Livewire\TimesheetReportManager::class)
        ->assertSet('reportType', 'project')
        ->call('setReportType', 'employee')
        ->assertSet('reportType', 'employee');
});

test('report manager can set quick period', function () {
    \Livewire\Livewire::test(\App\Livewire\TimesheetReportManager::class)
        ->call('setQuickPeriod', 'this_month')
        ->assertSet('fromDate', Carbon::now()->startOfMonth()->format('Y-m-d'))
        ->assertSet('toDate', Carbon::now()->endOfMonth()->format('Y-m-d'));
});

test('report manager can view project details', function () {
    $project = Project::factory()->create(['company_id' => $this->company->id]);

    \Livewire\Livewire::test(\App\Livewire\TimesheetReportManager::class)
        ->call('viewProjectDetails', $project->id)
        ->assertSet('reportType', 'project_detail')
        ->assertSet('selectedProjectId', $project->id);
});
