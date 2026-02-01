<?php

use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use App\Services\WorkOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($this->user)->create();
    $this->user->update(['current_company_id' => $this->company->id]);
    app()->instance('current.company', $this->company);
    $this->actingAs($this->user);
    $this->service = app(WorkOrderService::class);
});

it('creates a time entry line', function () {
    $workOrder = WorkOrder::factory()->create();

    $line = $this->service->saveLine($workOrder, [
        'line_type' => 'time',
        'product_id' => null,
        'description' => 'Development work',
        'quantity' => 4,
        'unit_price' => 500,
        'discount_percent' => 0,
        'performed_at' => '2026-01-15',
        'performed_by' => $this->user->id,
    ]);

    expect($line)->toBeInstanceOf(WorkOrderLine::class);
    expect($line->line_type)->toBe('time');
    expect($line->description)->toBe('Development work');
    expect($line->quantity)->toBe('4.00');
    expect($line->unit_price)->toBe('500.00');
    expect($line->performed_by)->toBe($this->user->id);
    expect($line->performed_at->format('Y-m-d'))->toBe('2026-01-15');
});

it('creates a product entry line', function () {
    $workOrder = WorkOrder::factory()->create();
    $product = Product::factory()->create();

    $line = $this->service->saveLine($workOrder, [
        'line_type' => 'product',
        'product_id' => $product->id,
        'description' => $product->name,
        'quantity' => 2,
        'unit_price' => 1000,
        'discount_percent' => 10,
        'performed_at' => null,
        'performed_by' => null,
    ]);

    expect($line->line_type)->toBe('product');
    expect($line->product_id)->toBe($product->id);
    expect($line->performed_at)->toBeNull();
    expect($line->performed_by)->toBeNull();
});

it('updates an existing line', function () {
    $workOrder = WorkOrder::factory()->create();
    $line = WorkOrderLine::factory()->timeEntry()->create([
        'work_order_id' => $workOrder->id,
        'description' => 'Original description',
        'quantity' => 2,
    ]);

    $updated = $this->service->saveLine($workOrder, [
        'line_type' => 'time',
        'product_id' => null,
        'description' => 'Updated description',
        'quantity' => 5,
        'unit_price' => 600,
        'discount_percent' => 0,
        'performed_at' => '2026-01-20',
        'performed_by' => $line->performed_by,
    ], $line->id);

    expect($updated->id)->toBe($line->id);
    expect($updated->description)->toBe('Updated description');
    expect($updated->quantity)->toBe('5.00');
});

it('deletes a line', function () {
    $workOrder = WorkOrder::factory()->create();
    $line = WorkOrderLine::factory()->create(['work_order_id' => $workOrder->id]);

    $this->service->deleteLine($line);

    expect(WorkOrderLine::find($line->id))->toBeNull();
});

it('populates line data from product', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 999.99,
    ]);

    $data = $this->service->populateFromProduct($product);

    expect($data['description'])->toBe('Test Product');
    expect((float) $data['unit_price'])->toBe(999.99);
});

it('returns time entry defaults', function () {
    $defaults = $this->service->getTimeEntryDefaults();

    expect($defaults['line_type'])->toBe('time');
    expect($defaults['performed_at'])->toBe(date('Y-m-d'));
    expect($defaults['performed_by'])->toBe($this->user->id);
});

it('returns product entry defaults', function () {
    $defaults = $this->service->getProductEntryDefaults();

    expect($defaults['line_type'])->toBe('product');
    expect($defaults['performed_at'])->toBeNull();
    expect($defaults['performed_by'])->toBeNull();
});

it('calculates total hours from time entries', function () {
    $workOrder = WorkOrder::factory()->create();

    WorkOrderLine::factory()->timeEntry()->create([
        'work_order_id' => $workOrder->id,
        'quantity' => 4,
    ]);
    WorkOrderLine::factory()->timeEntry()->create([
        'work_order_id' => $workOrder->id,
        'quantity' => 2.5,
    ]);
    WorkOrderLine::factory()->productEntry()->create([
        'work_order_id' => $workOrder->id,
        'quantity' => 10,
    ]);

    $workOrder->load('lines');
    $totalHours = $this->service->calculateTotalHours($workOrder);

    expect($totalHours)->toBe(6.5);
});

it('calculates total amount from lines', function () {
    $workOrder = WorkOrder::factory()->create();

    WorkOrderLine::factory()->create([
        'work_order_id' => $workOrder->id,
        'quantity' => 2,
        'unit_price' => 100,
        'discount_percent' => 0,
    ]);
    WorkOrderLine::factory()->create([
        'work_order_id' => $workOrder->id,
        'quantity' => 1,
        'unit_price' => 500,
        'discount_percent' => 10,
    ]);

    $workOrder->load('lines');
    $totalAmount = $this->service->calculateTotalAmount($workOrder);

    // 2*100 = 200, 1*500*0.9 = 450, total = 650
    expect($totalAmount)->toBe(650.0);
});

it('calculates budget variance', function () {
    $workOrder = WorkOrder::factory()->create(['budget' => 1000]);

    WorkOrderLine::factory()->create([
        'work_order_id' => $workOrder->id,
        'quantity' => 2,
        'unit_price' => 200,
        'discount_percent' => 0,
    ]);

    $workOrder->load('lines');
    $variance = $this->service->calculateBudgetVariance($workOrder);

    // Budget 1000 - spent 400 = 600 under budget
    expect($variance)->toBe(600.0);
});

it('returns null variance when no budget', function () {
    $workOrder = WorkOrder::factory()->create(['budget' => null]);

    $variance = $this->service->calculateBudgetVariance($workOrder);

    expect($variance)->toBeNull();
});

it('checks if work order is overdue', function () {
    $overdueWorkOrder = WorkOrder::factory()->create([
        'due_date' => now()->subDay(),
        'completed_at' => null,
    ]);

    $notDueWorkOrder = WorkOrder::factory()->create([
        'due_date' => now()->addDay(),
        'completed_at' => null,
    ]);

    $completedWorkOrder = WorkOrder::factory()->create([
        'due_date' => now()->subDay(),
        'completed_at' => now(),
    ]);

    expect($this->service->isOverdue($overdueWorkOrder))->toBeTrue();
    expect($this->service->isOverdue($notDueWorkOrder))->toBeFalse();
    expect($this->service->isOverdue($completedWorkOrder))->toBeFalse();
});

it('marks work order as completed', function () {
    $workOrder = WorkOrder::factory()->create(['completed_at' => null]);

    $completed = $this->service->markAsCompleted($workOrder);

    expect($completed->completed_at)->not->toBeNull();
});

it('reopens a completed work order', function () {
    $workOrder = WorkOrder::factory()->create(['completed_at' => now()]);

    $reopened = $this->service->reopen($workOrder);

    expect($reopened->completed_at)->toBeNull();
});

it('assigns work order to user', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['assigned_to' => null]);

    $assigned = $this->service->assignTo($workOrder, $user->id);

    expect($assigned->assigned_to)->toBe($user->id);
});

it('gets time entries grouped by user', function () {
    $user1 = User::factory()->create(['name' => 'User One']);
    $user2 = User::factory()->create(['name' => 'User Two']);
    $workOrder = WorkOrder::factory()->create();

    WorkOrderLine::factory()->timeEntry()->create([
        'work_order_id' => $workOrder->id,
        'performed_by' => $user1->id,
        'quantity' => 3,
        'unit_price' => 500,
        'discount_percent' => 0,
    ]);
    WorkOrderLine::factory()->timeEntry()->create([
        'work_order_id' => $workOrder->id,
        'performed_by' => $user1->id,
        'quantity' => 2,
        'unit_price' => 500,
        'discount_percent' => 0,
    ]);
    WorkOrderLine::factory()->timeEntry()->create([
        'work_order_id' => $workOrder->id,
        'performed_by' => $user2->id,
        'quantity' => 4,
        'unit_price' => 600,
        'discount_percent' => 0,
    ]);

    $workOrder->load('lines.performedByUser');
    $summary = $this->service->getTimeEntriesByUser($workOrder);

    expect($summary)->toHaveCount(2);

    $user1Summary = collect($summary)->firstWhere('user_id', $user1->id);
    expect($user1Summary['total_hours'])->toBe(5.0);
    expect($user1Summary['total_amount'])->toBe(2500.0);

    $user2Summary = collect($summary)->firstWhere('user_id', $user2->id);
    expect($user2Summary['total_hours'])->toBe(4.0);
    expect($user2Summary['total_amount'])->toBe(2400.0);
});
