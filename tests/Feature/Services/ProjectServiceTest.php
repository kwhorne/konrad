<?php

use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectLine;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(ProjectService::class);
});

it('creates a project line', function () {
    $project = Project::factory()->create();

    $line = $this->service->saveLine($project, [
        'product_id' => null,
        'description' => 'Consulting services',
        'quantity' => 10,
        'unit_price' => 1500,
        'discount_percent' => 5,
    ]);

    expect($line)->toBeInstanceOf(ProjectLine::class);
    expect($line->description)->toBe('Consulting services');
    expect($line->quantity)->toBe('10.00');
    expect($line->unit_price)->toBe('1500.00');
    expect($line->discount_percent)->toBe('5.00');
});

it('creates a line with product', function () {
    $project = Project::factory()->create();
    $product = Product::factory()->create();

    $line = $this->service->saveLine($project, [
        'product_id' => $product->id,
        'description' => $product->name,
        'quantity' => 3,
        'unit_price' => $product->price,
        'discount_percent' => 0,
    ]);

    expect($line->product_id)->toBe($product->id);
});

it('updates an existing line', function () {
    $project = Project::factory()->create();
    $line = ProjectLine::factory()->create([
        'project_id' => $project->id,
        'description' => 'Original',
        'quantity' => 1,
    ]);

    $updated = $this->service->saveLine($project, [
        'product_id' => null,
        'description' => 'Updated description',
        'quantity' => 5,
        'unit_price' => 2000,
        'discount_percent' => 10,
    ], $line->id);

    expect($updated->id)->toBe($line->id);
    expect($updated->description)->toBe('Updated description');
    expect($updated->quantity)->toBe('5.00');
});

it('deletes a line', function () {
    $project = Project::factory()->create();
    $line = ProjectLine::factory()->create(['project_id' => $project->id]);

    $this->service->deleteLine($line);

    expect(ProjectLine::find($line->id))->toBeNull();
});

it('populates line data from product', function () {
    $product = Product::factory()->create([
        'name' => 'Premium Service',
        'price' => 2500.00,
    ]);

    $data = $this->service->populateFromProduct($product);

    expect($data['description'])->toBe('Premium Service');
    expect((float) $data['unit_price'])->toBe(2500.00);
});

it('calculates total from lines', function () {
    $project = Project::factory()->create();

    ProjectLine::factory()->create([
        'project_id' => $project->id,
        'quantity' => 2,
        'unit_price' => 1000,
        'discount_percent' => 0,
    ]);
    ProjectLine::factory()->create([
        'project_id' => $project->id,
        'quantity' => 1,
        'unit_price' => 500,
        'discount_percent' => 20,
    ]);

    $project->load('lines');
    $total = $this->service->calculateTotal($project);

    // 2*1000 = 2000, 1*500*0.8 = 400, total = 2400
    expect($total)->toBe(2400.0);
});

it('calculates budget variance', function () {
    $project = Project::factory()->create(['budget' => 5000]);

    ProjectLine::factory()->create([
        'project_id' => $project->id,
        'quantity' => 2,
        'unit_price' => 1000,
        'discount_percent' => 0,
    ]);

    $project->load('lines');
    $variance = $this->service->calculateBudgetVariance($project);

    // Budget 5000 - spent 2000 = 3000 under budget
    expect($variance)->toBe(3000.0);
});

it('returns null variance when no budget', function () {
    $project = Project::factory()->create(['budget' => null]);

    $variance = $this->service->calculateBudgetVariance($project);

    expect($variance)->toBeNull();
});

it('checks if project is within budget', function () {
    $underBudget = Project::factory()->create(['budget' => 10000]);
    ProjectLine::factory()->create([
        'project_id' => $underBudget->id,
        'quantity' => 1,
        'unit_price' => 5000,
        'discount_percent' => 0,
    ]);
    $underBudget->load('lines');

    $overBudget = Project::factory()->create(['budget' => 1000]);
    ProjectLine::factory()->create([
        'project_id' => $overBudget->id,
        'quantity' => 1,
        'unit_price' => 2000,
        'discount_percent' => 0,
    ]);
    $overBudget->load('lines');

    $noBudget = Project::factory()->create(['budget' => null]);

    expect($this->service->isWithinBudget($underBudget))->toBeTrue();
    expect($this->service->isWithinBudget($overBudget))->toBeFalse();
    expect($this->service->isWithinBudget($noBudget))->toBeNull();
});

it('calculates budget usage percentage', function () {
    $project = Project::factory()->create(['budget' => 10000]);

    ProjectLine::factory()->create([
        'project_id' => $project->id,
        'quantity' => 1,
        'unit_price' => 2500,
        'discount_percent' => 0,
    ]);

    $project->load('lines');
    $usagePercent = $this->service->calculateBudgetUsagePercent($project);

    // 2500 / 10000 = 25%
    expect($usagePercent)->toBe(25.0);
});

it('returns null usage percent when no budget', function () {
    $project = Project::factory()->create(['budget' => null]);

    $usagePercent = $this->service->calculateBudgetUsagePercent($project);

    expect($usagePercent)->toBeNull();
});

it('checks if project is overdue', function () {
    $overdueProject = Project::factory()->create([
        'end_date' => now()->subDay(),
    ]);

    $notDueProject = Project::factory()->create([
        'end_date' => now()->addDay(),
    ]);

    $noEndDateProject = Project::factory()->create([
        'end_date' => null,
    ]);

    expect($this->service->isOverdue($overdueProject))->toBeTrue();
    expect($this->service->isOverdue($notDueProject))->toBeFalse();
    expect($this->service->isOverdue($noEndDateProject))->toBeFalse();
});

it('gets line summary', function () {
    $project = Project::factory()->create(['budget' => 5000]);

    ProjectLine::factory()->create([
        'project_id' => $project->id,
        'quantity' => 2,
        'unit_price' => 500,
        'discount_percent' => 0,
    ]);
    ProjectLine::factory()->create([
        'project_id' => $project->id,
        'quantity' => 1,
        'unit_price' => 1000,
        'discount_percent' => 0,
    ]);

    $project->load('lines');
    $summary = $this->service->getLineSummary($project);

    expect($summary['line_count'])->toBe(2);
    expect($summary['total'])->toBe(2000.0);
    expect($summary['budget'])->toBe(5000.0);
    expect($summary['variance'])->toBe(3000.0);
    expect($summary['usage_percent'])->toBe(40.0);
});
