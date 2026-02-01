<?php

use App\Models\Project;
use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\TimesheetService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = createTestCompanyContext();
    $this->actingAs($this->user);
});

// Timesheet Model Tests
test('timesheet has status constants', function () {
    expect(Timesheet::STATUS_DRAFT)->toBe('draft');
    expect(Timesheet::STATUS_SUBMITTED)->toBe('submitted');
    expect(Timesheet::STATUS_APPROVED)->toBe('approved');
    expect(Timesheet::STATUS_REJECTED)->toBe('rejected');
});

test('timesheet belongs to user', function () {
    $timesheet = Timesheet::factory()->create(['user_id' => $this->user->id]);

    expect($timesheet->user->id)->toBe($this->user->id);
});

test('timesheet has many entries', function () {
    $timesheet = Timesheet::factory()->create(['user_id' => $this->user->id]);
    TimesheetEntry::factory()->count(3)->create(['timesheet_id' => $timesheet->id]);

    expect($timesheet->entries)->toHaveCount(3);
});

test('timesheet week_start is cast to date', function () {
    $timesheet = Timesheet::factory()->create();

    expect($timesheet->week_start)->toBeInstanceOf(Carbon::class);
});

test('timesheet week_end is cast to date', function () {
    $timesheet = Timesheet::factory()->forWeek(Carbon::now()->subWeek())->create();

    expect($timesheet->week_end)->toBeInstanceOf(Carbon::class);
});

test('timesheet is editable when draft', function () {
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(2))->create();

    expect($timesheet->is_editable)->toBeTrue();
});

test('timesheet is editable when rejected', function () {
    $timesheet = Timesheet::factory()->rejected()->forWeek(Carbon::now()->subWeeks(3))->create();

    expect($timesheet->is_editable)->toBeTrue();
});

test('timesheet is not editable when submitted', function () {
    $timesheet = Timesheet::factory()->submitted()->forWeek(Carbon::now()->subWeeks(4))->create();

    expect($timesheet->is_editable)->toBeFalse();
});

test('timesheet is not editable when approved', function () {
    $timesheet = Timesheet::factory()->approved()->forWeek(Carbon::now()->subWeeks(5))->create();

    expect($timesheet->is_editable)->toBeFalse();
});

test('timesheet is submittable when editable and has hours', function () {
    $timesheet = Timesheet::factory()->draft()->withHours(8)->forWeek(Carbon::now()->subWeeks(6))->create();

    expect($timesheet->is_submittable)->toBeTrue();
});

test('timesheet is not submittable when no hours', function () {
    $timesheet = Timesheet::factory()->draft()->withHours(0)->forWeek(Carbon::now()->subWeeks(7))->create();

    expect($timesheet->is_submittable)->toBeFalse();
});

test('timesheet status_label returns correct Norwegian label', function () {
    $draft = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(10))->create();
    $submitted = Timesheet::factory()->submitted()->forWeek(Carbon::now()->subWeeks(11))->create();
    $approved = Timesheet::factory()->approved()->forWeek(Carbon::now()->subWeeks(12))->create();
    $rejected = Timesheet::factory()->rejected()->forWeek(Carbon::now()->subWeeks(13))->create();

    expect($draft->status_label)->toBe('Utkast');
    expect($submitted->status_label)->toBe('Innsendt');
    expect($approved->status_label)->toBe('Godkjent');
    expect($rejected->status_label)->toBe('Avvist');
});

test('timesheet week_number returns correct ISO week', function () {
    $timesheet = Timesheet::factory()->forWeek(Carbon::parse('2026-02-02'))->create();

    expect($timesheet->week_number)->toBe(6); // Week 6 of 2026
});

test('timesheet week_label returns formatted week label', function () {
    $timesheet = Timesheet::factory()->forWeek(Carbon::parse('2026-02-02'))->create();

    expect($timesheet->week_label)->toContain('Uke 6');
    expect($timesheet->week_label)->toContain('2026');
});

// Timesheet Workflow Tests
test('timesheet can be submitted', function () {
    $timesheet = Timesheet::factory()->draft()->withHours(8)->forWeek(Carbon::now()->subWeeks(14))->create(['user_id' => $this->user->id]);

    $result = $timesheet->submit($this->user);

    expect($result)->toBeTrue();
    expect($timesheet->fresh()->status)->toBe(Timesheet::STATUS_SUBMITTED);
    expect($timesheet->fresh()->submitted_by)->toBe($this->user->id);
    expect($timesheet->fresh()->submitted_at)->not->toBeNull();
});

test('timesheet cannot be submitted without hours', function () {
    $timesheet = Timesheet::factory()->draft()->withHours(0)->forWeek(Carbon::now()->subWeeks(15))->create(['user_id' => $this->user->id]);

    $result = $timesheet->submit($this->user);

    expect($result)->toBeFalse();
    expect($timesheet->fresh()->status)->toBe(Timesheet::STATUS_DRAFT);
});

test('timesheet cannot be submitted when already submitted', function () {
    $timesheet = Timesheet::factory()->submitted()->forWeek(Carbon::now()->subWeeks(16))->create(['user_id' => $this->user->id]);

    $result = $timesheet->submit($this->user);

    expect($result)->toBeFalse();
});

test('timesheet can be approved', function () {
    $manager = User::factory()->create();
    $timesheet = Timesheet::factory()->submitted()->forWeek(Carbon::now()->subWeeks(17))->create(['user_id' => $this->user->id]);

    $result = $timesheet->approve($manager);

    expect($result)->toBeTrue();
    expect($timesheet->fresh()->status)->toBe(Timesheet::STATUS_APPROVED);
    expect($timesheet->fresh()->approved_by)->toBe($manager->id);
    expect($timesheet->fresh()->approved_at)->not->toBeNull();
});

test('timesheet cannot be approved when draft', function () {
    $manager = User::factory()->create();
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(18))->create(['user_id' => $this->user->id]);

    $result = $timesheet->approve($manager);

    expect($result)->toBeFalse();
});

test('timesheet can be rejected with reason', function () {
    $manager = User::factory()->create();
    $timesheet = Timesheet::factory()->submitted()->forWeek(Carbon::now()->subWeeks(19))->create(['user_id' => $this->user->id]);
    $reason = 'Timer mangler for fredag';

    $result = $timesheet->reject($manager, $reason);

    expect($result)->toBeTrue();
    expect($timesheet->fresh()->status)->toBe(Timesheet::STATUS_REJECTED);
    expect($timesheet->fresh()->rejected_by)->toBe($manager->id);
    expect($timesheet->fresh()->rejection_reason)->toBe($reason);
});

test('rejected timesheet can be reopened', function () {
    $timesheet = Timesheet::factory()->rejected()->forWeek(Carbon::now()->subWeeks(20))->create(['user_id' => $this->user->id]);

    $result = $timesheet->reopen();

    expect($result)->toBeTrue();
    expect($timesheet->fresh()->status)->toBe(Timesheet::STATUS_DRAFT);
});

test('draft timesheet cannot be reopened', function () {
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(21))->create(['user_id' => $this->user->id]);

    $result = $timesheet->reopen();

    expect($result)->toBeFalse();
});

// Timesheet Entry Tests
test('timesheet entry belongs to timesheet', function () {
    $timesheet = Timesheet::factory()->forWeek(Carbon::now()->subWeeks(22))->create(['user_id' => $this->user->id]);
    $entry = TimesheetEntry::factory()->create(['timesheet_id' => $timesheet->id]);

    expect($entry->timesheet->id)->toBe($timesheet->id);
});

test('timesheet entry can belong to project', function () {
    $project = Project::factory()->create();
    $entry = TimesheetEntry::factory()->forProject($project)->create();

    expect($entry->project->id)->toBe($project->id);
});

test('timesheet entry can belong to work order', function () {
    $workOrder = WorkOrder::factory()->create();
    $entry = TimesheetEntry::factory()->forWorkOrder($workOrder)->create();

    expect($entry->workOrder->id)->toBe($workOrder->id);
});

test('timesheet entry target_type returns correct type', function () {
    $project = Project::factory()->create();
    $workOrder = WorkOrder::factory()->create();

    $projectEntry = TimesheetEntry::factory()->forProject($project)->create();
    $workOrderEntry = TimesheetEntry::factory()->forWorkOrder($workOrder)->create();
    $otherEntry = TimesheetEntry::factory()->create(['project_id' => null, 'work_order_id' => null]);

    expect($projectEntry->target_type)->toBe('project');
    expect($workOrderEntry->target_type)->toBe('work_order');
    expect($otherEntry->target_type)->toBeNull();
});

test('timesheet entry target_label returns correct label', function () {
    $project = Project::factory()->create(['project_number' => 'P-001', 'name' => 'Test Project']);

    $entry = TimesheetEntry::factory()->forProject($project)->create();

    expect($entry->target_label)->toContain('P-001');
    expect($entry->target_label)->toContain('Test Project');
});

test('timesheet recalculates total hours when entry saved', function () {
    $timesheet = Timesheet::factory()->forWeek(Carbon::now()->subWeeks(30))->create(['user_id' => $this->user->id, 'total_hours' => 0]);

    TimesheetEntry::factory()->withHours(4)->forDate($timesheet->week_start)->create(['timesheet_id' => $timesheet->id]);
    TimesheetEntry::factory()->withHours(4)->forDate($timesheet->week_start->copy()->addDay())->create(['timesheet_id' => $timesheet->id]);

    expect((float) $timesheet->fresh()->total_hours)->toBe(8.0);
});

test('timesheet recalculates total hours when entry deleted', function () {
    $timesheet = Timesheet::factory()->forWeek(Carbon::now()->subWeeks(31))->create(['user_id' => $this->user->id, 'total_hours' => 0]);
    $entry1 = TimesheetEntry::factory()->withHours(4)->forDate($timesheet->week_start)->create(['timesheet_id' => $timesheet->id]);
    TimesheetEntry::factory()->withHours(4)->forDate($timesheet->week_start->copy()->addDay())->create(['timesheet_id' => $timesheet->id]);

    expect((float) $timesheet->fresh()->total_hours)->toBe(8.0);

    $entry1->delete();

    expect((float) $timesheet->fresh()->total_hours)->toBe(4.0);
});

// Scopes Tests
test('draft scope filters correctly', function () {
    Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(40))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(41))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->submitted()->forWeek(Carbon::now()->subWeeks(42))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->submitted()->forWeek(Carbon::now()->subWeeks(43))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->submitted()->forWeek(Carbon::now()->subWeeks(44))->create(['user_id' => $this->user->id]);

    expect(Timesheet::draft()->count())->toBe(2);
});

test('submitted scope filters correctly', function () {
    Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(50))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(51))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->submitted()->forWeek(Carbon::now()->subWeeks(52))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->submitted()->forWeek(Carbon::now()->subWeeks(53))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->submitted()->forWeek(Carbon::now()->subWeeks(54))->create(['user_id' => $this->user->id]);

    expect(Timesheet::submitted()->count())->toBe(3);
});

test('approved scope filters correctly', function () {
    Timesheet::factory()->approved()->forWeek(Carbon::now()->subWeeks(60))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->approved()->forWeek(Carbon::now()->subWeeks(61))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(62))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(63))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(64))->create(['user_id' => $this->user->id]);

    expect(Timesheet::approved()->count())->toBe(2);
});

test('for_user scope filters correctly', function () {
    $otherUser = User::factory()->create();
    Timesheet::factory()->forWeek(Carbon::now()->subWeeks(70))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->forWeek(Carbon::now()->subWeeks(71))->create(['user_id' => $this->user->id]);
    Timesheet::factory()->forWeek(Carbon::now()->subWeeks(72))->create(['user_id' => $otherUser->id]);
    Timesheet::factory()->forWeek(Carbon::now()->subWeeks(73))->create(['user_id' => $otherUser->id]);
    Timesheet::factory()->forWeek(Carbon::now()->subWeeks(74))->create(['user_id' => $otherUser->id]);

    expect(Timesheet::forUser($this->user)->count())->toBe(2);
});

test('for_week scope filters correctly', function () {
    Timesheet::factory()->thisWeek()->create(['user_id' => $this->user->id]);
    Timesheet::factory()->lastWeek()->create(['user_id' => $this->user->id]);

    expect(Timesheet::forWeek(Carbon::now())->count())->toBe(1);
});

// TimesheetService Tests
test('service get or create returns existing timesheet', function () {
    $service = app(TimesheetService::class);
    $existing = Timesheet::factory()->thisWeek()->create(['user_id' => $this->user->id]);

    $timesheet = $service->getOrCreateTimesheet($this->user, Carbon::now());

    expect($timesheet->id)->toBe($existing->id);
});

test('service get or create creates new timesheet', function () {
    $service = app(TimesheetService::class);
    $weekDate = Carbon::now()->subWeeks(80);

    expect(Timesheet::where('user_id', $this->user->id)->forWeek($weekDate)->count())->toBe(0);

    $timesheet = $service->getOrCreateTimesheet($this->user, $weekDate);

    expect($timesheet)->not->toBeNull();
    expect($timesheet->user_id)->toBe($this->user->id);
    expect($timesheet->status)->toBe(Timesheet::STATUS_DRAFT);
});

test('service save entry creates new entry', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(81))->create(['user_id' => $this->user->id]);

    $entry = $service->saveEntry($timesheet, [
        'entry_date' => $timesheet->week_start->format('Y-m-d'),
        'hours' => 7.5,
        'description' => 'Test work',
    ]);

    expect($entry)->not->toBeNull();
    expect((float) $entry->hours)->toBe(7.5);
    expect($entry->description)->toBe('Test work');
});

test('service save entry updates existing entry', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(82))->create(['user_id' => $this->user->id]);
    $entry = TimesheetEntry::factory()->withHours(4)->forDate($timesheet->week_start)->create(['timesheet_id' => $timesheet->id]);

    $updated = $service->saveEntry($timesheet, [
        'entry_date' => $entry->entry_date->format('Y-m-d'),
        'hours' => 8,
        'description' => 'Updated entry',
    ], $entry->id);

    expect((float) $updated->hours)->toBe(8.0);
});

test('service save entry throws when timesheet not editable', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->submitted()->forWeek(Carbon::now()->subWeeks(83))->create(['user_id' => $this->user->id]);

    expect(fn () => $service->saveEntry($timesheet, [
        'entry_date' => $timesheet->week_start->format('Y-m-d'),
        'hours' => 7.5,
        'description' => 'Test',
    ]))->toThrow(\App\Exceptions\TimesheetValidationException::class, 'Timeseddel kan ikke redigeres.');
});

test('service delete entry removes entry', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(84))->create(['user_id' => $this->user->id]);
    $entry = TimesheetEntry::factory()->forDate($timesheet->week_start)->create(['timesheet_id' => $timesheet->id]);

    $result = $service->deleteEntry($entry);

    expect($result)->toBeTrue();
    expect(TimesheetEntry::find($entry->id))->toBeNull();
});

test('service delete entry fails when timesheet not editable', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->submitted()->forWeek(Carbon::now()->subWeeks(85))->create(['user_id' => $this->user->id]);
    $entry = TimesheetEntry::factory()->forDate($timesheet->week_start)->create(['timesheet_id' => $timesheet->id]);

    $result = $service->deleteEntry($entry);

    expect($result)->toBeFalse();
});

test('service can approve timesheets returns true for manager', function () {
    $service = app(TimesheetService::class);

    // User is owner/manager of their company
    $result = $service->canApproveTimesheets($this->user, $this->company);

    expect($result)->toBeTrue();
});

test('service can approve timesheets returns false for regular user', function () {
    $service = app(TimesheetService::class);

    // Create another user who is not a manager
    $regularUser = User::factory()->create();
    $this->company->users()->attach($regularUser, ['role' => 'member']);

    $result = $service->canApproveTimesheets($regularUser, $this->company);

    expect($result)->toBeFalse();
});

// Livewire Component Tests
test('timesheet manager page is accessible', function () {
    $response = $this->get(route('timesheets.index'));

    $response->assertStatus(200);
    $response->assertSee('Timeregistrering');
});

test('timesheet history page is accessible', function () {
    $response = $this->get(route('timesheets.history'));

    $response->assertStatus(200);
    $response->assertSee('Mine timer');
});

test('timesheet approval page requires manager role', function () {
    // Create a regular user
    $regularUser = User::factory()->create(['onboarding_completed' => true]);
    $this->company->users()->attach($regularUser, ['role' => 'member']);
    $regularUser->update(['current_company_id' => $this->company->id]);

    $this->actingAs($regularUser);

    $response = $this->get(route('timesheets.approval'));

    $response->assertForbidden();
});

test('timesheet approval page is accessible for manager', function () {
    $response = $this->get(route('timesheets.approval'));

    $response->assertStatus(200);
    $response->assertSee('Godkjenn timer');
});

// Quick Entry Modal Tests
test('quick entry modal can register hours with project', function () {
    $project = Project::factory()->create(['company_id' => $this->company->id]);

    Livewire::test(\App\Livewire\TimesheetManager::class)
        ->call('openQuickEntryModal')
        ->assertSet('showQuickEntryModal', true)
        ->set('quickEntryType', 'project')
        ->set('quickEntryProjectId', $project->id)
        ->set('quickEntryHours', 4)
        ->call('saveQuickEntry')
        ->assertSet('showQuickEntryModal', false)
        ->assertHasNoErrors();

    expect(TimesheetEntry::where('project_id', $project->id)->where('hours', 4)->exists())->toBeTrue();
});

test('quick entry modal can register hours with work order', function () {
    $project = Project::factory()->create(['company_id' => $this->company->id]);
    $workOrder = WorkOrder::factory()->create(['company_id' => $this->company->id, 'project_id' => $project->id]);

    Livewire::test(\App\Livewire\TimesheetManager::class)
        ->call('openQuickEntryModal')
        ->set('quickEntryType', 'workorder')
        ->set('quickEntryWorkOrderId', $workOrder->id)
        ->set('quickEntryHours', 2.5)
        ->call('saveQuickEntry')
        ->assertHasNoErrors();

    expect(TimesheetEntry::where('work_order_id', $workOrder->id)->where('hours', 2.5)->exists())->toBeTrue();
});

test('quick entry modal can register hours with description and note', function () {
    Livewire::test(\App\Livewire\TimesheetManager::class)
        ->call('openQuickEntryModal')
        ->set('quickEntryType', 'other')
        ->set('quickEntryDescription', 'Internt møte')
        ->set('quickEntryHours', 1.5)
        ->set('quickEntryNote', 'Planlegging av sprint')
        ->call('saveQuickEntry')
        ->assertHasNoErrors();

    expect(TimesheetEntry::where('description', 'LIKE', '%Internt møte%')->where('hours', 1.5)->exists())->toBeTrue();
});

test('quick entry modal validates required fields', function () {
    Livewire::test(\App\Livewire\TimesheetManager::class)
        ->call('openQuickEntryModal')
        ->set('quickEntryType', 'project')
        ->set('quickEntryProjectId', null)
        ->set('quickEntryHours', null)
        ->call('saveQuickEntry')
        ->assertHasErrors(['quickEntryProjectId', 'quickEntryHours']);
});

test('quick entry modal pre-populates project from row', function () {
    $project = Project::factory()->create(['company_id' => $this->company->id]);

    // First create an entry so we have a row
    $component = Livewire::test(\App\Livewire\TimesheetManager::class)
        ->call('openQuickEntryModal')
        ->set('quickEntryType', 'project')
        ->set('quickEntryProjectId', $project->id)
        ->set('quickEntryHours', 2)
        ->call('saveQuickEntry');

    // Now open modal with row index 0 and verify it pre-populates the project
    $component
        ->call('openQuickEntryModal', null, 0)
        ->assertSet('quickEntryType', 'project')
        ->assertSet('quickEntryProjectId', $project->id);
});

test('quick entry modal pre-populates work order from row', function () {
    $project = Project::factory()->create(['company_id' => $this->company->id]);
    $workOrder = WorkOrder::factory()->create(['company_id' => $this->company->id, 'project_id' => $project->id]);

    // First create an entry so we have a row
    $component = Livewire::test(\App\Livewire\TimesheetManager::class)
        ->call('openQuickEntryModal')
        ->set('quickEntryType', 'workorder')
        ->set('quickEntryWorkOrderId', $workOrder->id)
        ->set('quickEntryHours', 3)
        ->call('saveQuickEntry');

    // Now open modal with row index 0 and verify it pre-populates the work order
    $component
        ->call('openQuickEntryModal', null, 0)
        ->assertSet('quickEntryType', 'workorder')
        ->assertSet('quickEntryWorkOrderId', $workOrder->id);
});

// Business Rule Validation Tests
test('service save entry rejects hours below minimum', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(90))->create(['user_id' => $this->user->id]);

    expect(fn () => $service->saveEntry($timesheet, [
        'entry_date' => $timesheet->week_start->format('Y-m-d'),
        'hours' => 0.25,
        'description' => 'Test',
    ]))->toThrow(\App\Exceptions\TimesheetValidationException::class, 'Timer må være mellom 0.5 og 24.');
});

test('service save entry rejects hours above maximum per entry', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(91))->create(['user_id' => $this->user->id]);

    expect(fn () => $service->saveEntry($timesheet, [
        'entry_date' => $timesheet->week_start->format('Y-m-d'),
        'hours' => 25,
        'description' => 'Test',
    ]))->toThrow(\App\Exceptions\TimesheetValidationException::class, 'Timer må være mellom 0.5 og 24.');
});

test('service save entry accepts minimum hours', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(92))->create(['user_id' => $this->user->id]);

    $entry = $service->saveEntry($timesheet, [
        'entry_date' => $timesheet->week_start->format('Y-m-d'),
        'hours' => 0.5,
        'description' => 'Test minimum',
    ]);

    expect((float) $entry->hours)->toBe(0.5);
});

test('service save entry accepts maximum hours per entry', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(93))->create(['user_id' => $this->user->id]);

    $entry = $service->saveEntry($timesheet, [
        'entry_date' => $timesheet->week_start->format('Y-m-d'),
        'hours' => 24,
        'description' => 'Test maximum',
    ]);

    expect((float) $entry->hours)->toBe(24.0);
});

test('service save entry rejects daily total exceeding 24 hours', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(94))->create(['user_id' => $this->user->id]);
    $date = $timesheet->week_start->format('Y-m-d');

    // Create entries totaling 20 hours
    $service->saveEntry($timesheet, ['entry_date' => $date, 'hours' => 10, 'description' => 'Entry 1']);
    $service->saveEntry($timesheet, ['entry_date' => $date, 'hours' => 10, 'description' => 'Entry 2']);

    // Try to add 5 more hours (20 + 5 = 25 > 24)
    expect(fn () => $service->saveEntry($timesheet, [
        'entry_date' => $date,
        'hours' => 5,
        'description' => 'Entry 3',
    ]))->toThrow(\App\Exceptions\TimesheetValidationException::class);
});

test('service save entry allows daily total up to 24 hours', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(95))->create(['user_id' => $this->user->id]);
    $date = $timesheet->week_start->format('Y-m-d');

    // Create entries totaling 20 hours
    $service->saveEntry($timesheet, ['entry_date' => $date, 'hours' => 10, 'description' => 'Entry 1']);
    $service->saveEntry($timesheet, ['entry_date' => $date, 'hours' => 10, 'description' => 'Entry 2']);

    // Try to add 4 more hours (20 + 4 = 24 exactly)
    $entry = $service->saveEntry($timesheet, [
        'entry_date' => $date,
        'hours' => 4,
        'description' => 'Entry 3',
    ]);

    expect((float) $entry->hours)->toBe(4.0);
    // Use Carbon for date comparison to ensure consistent behavior across SQLite and MySQL
    $totalHours = TimesheetEntry::withoutCompanyScope()
        ->where('timesheet_id', $timesheet->id)
        ->where('entry_date', Carbon::parse($date))
        ->sum('hours');
    expect((float) $totalHours)->toBe(24.0);
});

test('service save entry update considers existing hours correctly', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(96))->create(['user_id' => $this->user->id]);
    $date = $timesheet->week_start->format('Y-m-d');

    // Create an entry with 10 hours
    $entry = $service->saveEntry($timesheet, ['entry_date' => $date, 'hours' => 10, 'description' => 'Entry 1']);

    // Update to 24 hours (should work because we exclude the entry being updated)
    $updated = $service->saveEntry($timesheet, [
        'entry_date' => $date,
        'hours' => 24,
        'description' => 'Entry 1 updated',
    ], $entry->id);

    expect((float) $updated->hours)->toBe(24.0);
});

test('service save entry rejects date outside timesheet week', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(97))->create(['user_id' => $this->user->id]);

    // Try to create entry for next week
    $nextWeekDate = $timesheet->week_end->copy()->addDays(2)->format('Y-m-d');

    expect(fn () => $service->saveEntry($timesheet, [
        'entry_date' => $nextWeekDate,
        'hours' => 4,
        'description' => 'Wrong week',
    ]))->toThrow(\App\Exceptions\TimesheetValidationException::class, 'Dato');
});

test('service save entry rejects date before timesheet week', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(98))->create(['user_id' => $this->user->id]);

    // Try to create entry for previous week
    $prevWeekDate = $timesheet->week_start->copy()->subDays(2)->format('Y-m-d');

    expect(fn () => $service->saveEntry($timesheet, [
        'entry_date' => $prevWeekDate,
        'hours' => 4,
        'description' => 'Wrong week',
    ]))->toThrow(\App\Exceptions\TimesheetValidationException::class, 'Dato');
});

test('service save entry requires target (project, work order, or description)', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(99))->create(['user_id' => $this->user->id]);

    expect(fn () => $service->saveEntry($timesheet, [
        'entry_date' => $timesheet->week_start->format('Y-m-d'),
        'hours' => 4,
        'project_id' => null,
        'work_order_id' => null,
        'description' => null,
    ]))->toThrow(\App\Exceptions\TimesheetValidationException::class, 'prosjekt, arbeidsordre eller beskrivelse');
});

test('service save entry requires target even with empty description', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(100))->create(['user_id' => $this->user->id]);

    expect(fn () => $service->saveEntry($timesheet, [
        'entry_date' => $timesheet->week_start->format('Y-m-d'),
        'hours' => 4,
        'description' => '   ',
    ]))->toThrow(\App\Exceptions\TimesheetValidationException::class, 'prosjekt, arbeidsordre eller beskrivelse');
});

test('service save entry accepts project as target', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(101))->create(['user_id' => $this->user->id]);
    $project = Project::factory()->create(['company_id' => $this->company->id]);

    $entry = $service->saveEntry($timesheet, [
        'entry_date' => $timesheet->week_start->format('Y-m-d'),
        'hours' => 4,
        'project_id' => $project->id,
    ]);

    expect($entry->project_id)->toBe($project->id);
});

test('service save entry accepts work order as target', function () {
    $service = app(TimesheetService::class);
    $timesheet = Timesheet::factory()->draft()->forWeek(Carbon::now()->subWeeks(102))->create(['user_id' => $this->user->id]);
    $project = Project::factory()->create(['company_id' => $this->company->id]);
    $workOrder = WorkOrder::factory()->create(['company_id' => $this->company->id, 'project_id' => $project->id]);

    $entry = $service->saveEntry($timesheet, [
        'entry_date' => $timesheet->week_start->format('Y-m-d'),
        'hours' => 4,
        'work_order_id' => $workOrder->id,
    ]);

    expect($entry->work_order_id)->toBe($workOrder->id);
});
