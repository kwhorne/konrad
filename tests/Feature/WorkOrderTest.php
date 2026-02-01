<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderPriority;
use App\Models\WorkOrderStatus;
use App\Models\WorkOrderType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($this->user)->create();
    $this->user->update(['current_company_id' => $this->company->id]);
    app()->instance('current.company', $this->company);
    $this->actingAs($this->user);
});

// Work Order Number Generation
test('work order has auto-generated number', function () {
    $workOrder = WorkOrder::factory()->create();

    $year = date('Y');
    expect($workOrder->work_order_number)->toMatch("/^WO-{$year}-\d{4}$/");
});

test('work order numbers increment correctly', function () {
    $workOrder1 = WorkOrder::factory()->create();
    $workOrder2 = WorkOrder::factory()->create();

    preg_match('/WO-\d{4}-(\d+)/', $workOrder1->work_order_number, $matches1);
    preg_match('/WO-\d{4}-(\d+)/', $workOrder2->work_order_number, $matches2);

    expect((int) $matches2[1])->toBe((int) $matches1[1] + 1);
});

// Relationships
test('work order belongs to contact', function () {
    $contact = Contact::factory()->create();
    $workOrder = WorkOrder::factory()->create(['contact_id' => $contact->id]);

    expect($workOrder->contact->id)->toBe($contact->id);
});

test('work order belongs to project', function () {
    $project = Project::factory()->create();
    $workOrder = WorkOrder::factory()->create(['project_id' => $project->id]);

    expect($workOrder->project->id)->toBe($project->id);
});

test('work order belongs to type', function () {
    $type = WorkOrderType::factory()->create(['name' => 'Reparasjon']);
    $workOrder = WorkOrder::factory()->create(['work_order_type_id' => $type->id]);

    expect($workOrder->workOrderType->id)->toBe($type->id);
    expect($workOrder->workOrderType->name)->toBe('Reparasjon');
});

test('work order belongs to status', function () {
    $status = WorkOrderStatus::factory()->create(['name' => 'Pågår']);
    $workOrder = WorkOrder::factory()->create(['work_order_status_id' => $status->id]);

    expect($workOrder->workOrderStatus->id)->toBe($status->id);
    expect($workOrder->workOrderStatus->name)->toBe('Pågår');
});

test('work order belongs to priority', function () {
    $priority = WorkOrderPriority::factory()->create(['name' => 'Høy']);
    $workOrder = WorkOrder::factory()->create(['work_order_priority_id' => $priority->id]);

    expect($workOrder->workOrderPriority->id)->toBe($priority->id);
    expect($workOrder->workOrderPriority->name)->toBe('Høy');
});

test('work order belongs to assigned user', function () {
    $assignedUser = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['assigned_to' => $assignedUser->id]);

    expect($workOrder->assignedUser->id)->toBe($assignedUser->id);
});

test('work order belongs to creator', function () {
    $creator = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['created_by' => $creator->id]);

    expect($workOrder->creator->id)->toBe($creator->id);
});

// Is Overdue
test('work order is overdue when past due date and not completed', function () {
    $workOrder = WorkOrder::factory()->create([
        'due_date' => now()->subDays(5),
        'completed_at' => null,
    ]);

    expect($workOrder->is_overdue)->toBeTrue();
});

test('work order is not overdue when completed', function () {
    $workOrder = WorkOrder::factory()->create([
        'due_date' => now()->subDays(5),
        'completed_at' => now(),
    ]);

    expect($workOrder->is_overdue)->toBeFalse();
});

test('work order is not overdue when due date is in future', function () {
    $workOrder = WorkOrder::factory()->create([
        'due_date' => now()->addDays(5),
        'completed_at' => null,
    ]);

    expect($workOrder->is_overdue)->toBeFalse();
});

// Budget Variance
test('work order budget variance is positive when under budget', function () {
    $workOrder = WorkOrder::factory()->create(['budget' => 10000]);

    // No lines, so total_amount is 0
    expect($workOrder->budget_variance)->toBe(10000.0);
});

test('work order budget variance is null when no budget set', function () {
    $workOrder = WorkOrder::factory()->create(['budget' => null]);

    expect($workOrder->budget_variance)->toBeNull();
});

// Scopes
test('active scope filters correctly', function () {
    WorkOrder::factory()->count(3)->create(['is_active' => true]);
    WorkOrder::factory()->count(2)->create(['is_active' => false]);

    expect(WorkOrder::active()->count())->toBe(3);
});

test('overdue scope filters correctly', function () {
    WorkOrder::factory()->count(2)->create([
        'due_date' => now()->subDays(5),
        'completed_at' => null,
    ]);
    WorkOrder::factory()->count(3)->create([
        'due_date' => now()->addDays(5),
        'completed_at' => null,
    ]);

    expect(WorkOrder::overdue()->count())->toBe(2);
});

test('pending scope filters correctly', function () {
    WorkOrder::factory()->count(2)->create(['completed_at' => null]);
    WorkOrder::factory()->count(3)->create(['completed_at' => now()]);

    expect(WorkOrder::pending()->count())->toBe(2);
});

test('completed scope filters correctly', function () {
    WorkOrder::factory()->count(2)->create(['completed_at' => null]);
    WorkOrder::factory()->count(3)->create(['completed_at' => now()]);

    expect(WorkOrder::completed()->count())->toBe(3);
});

// Date Casts
test('work order scheduled_date is cast to date', function () {
    $workOrder = WorkOrder::factory()->create(['scheduled_date' => '2024-06-15']);

    expect($workOrder->scheduled_date)->toBeInstanceOf(Carbon\Carbon::class);
    expect($workOrder->scheduled_date->format('Y-m-d'))->toBe('2024-06-15');
});

test('work order due_date is cast to date', function () {
    $workOrder = WorkOrder::factory()->create(['due_date' => '2024-06-30']);

    expect($workOrder->due_date)->toBeInstanceOf(Carbon\Carbon::class);
    expect($workOrder->due_date->format('Y-m-d'))->toBe('2024-06-30');
});

// Decimal Casts
test('work order estimated_hours is decimal', function () {
    $workOrder = WorkOrder::factory()->create(['estimated_hours' => 8.5]);

    expect((float) $workOrder->estimated_hours)->toBe(8.5);
});

test('work order budget is decimal', function () {
    $workOrder = WorkOrder::factory()->create(['budget' => 12500.50]);

    expect((float) $workOrder->budget)->toBe(12500.50);
});

// Boolean Casts
test('work order is_active is boolean', function () {
    $workOrder = WorkOrder::factory()->create(['is_active' => true]);

    expect($workOrder->is_active)->toBeTrue();
    expect($workOrder->is_active)->toBeBool();
});
