<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectLine;

class ProjectService
{
    /**
     * Save a project line (create or update).
     *
     * @param  array<string, mixed>  $data
     */
    public function saveLine(Project $project, array $data, ?int $lineId = null): ProjectLine
    {
        $lineData = [
            'project_id' => $project->id,
            'product_id' => $data['product_id'] ?: null,
            'description' => $data['description'] ?? null,
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'discount_percent' => $data['discount_percent'] ?? 0,
        ];

        if ($lineId) {
            $line = ProjectLine::findOrFail($lineId);
            $lineData['sort_order'] = $line->sort_order;
            $line->update($lineData);

            return $line->fresh();
        }

        $lineData['sort_order'] = ProjectLine::where('project_id', $project->id)->count();

        return ProjectLine::create($lineData);
    }

    /**
     * Delete a project line.
     */
    public function deleteLine(ProjectLine $line): void
    {
        $line->delete();
    }

    /**
     * Populate line data from a product.
     *
     * @return array<string, mixed>
     */
    public function populateFromProduct(Product $product): array
    {
        return [
            'description' => $product->name,
            'unit_price' => $product->price,
        ];
    }

    /**
     * Calculate total amount from project lines.
     */
    public function calculateTotal(Project $project): float
    {
        return $project->lines->sum(fn ($line) => $line->line_total);
    }

    /**
     * Calculate budget variance (positive = under budget, negative = over budget).
     */
    public function calculateBudgetVariance(Project $project): ?float
    {
        if ($project->budget === null) {
            return null;
        }

        return (float) $project->budget - $this->calculateTotal($project);
    }

    /**
     * Check if project is within budget.
     */
    public function isWithinBudget(Project $project): ?bool
    {
        $variance = $this->calculateBudgetVariance($project);

        if ($variance === null) {
            return null;
        }

        return $variance >= 0;
    }

    /**
     * Calculate budget usage percentage.
     */
    public function calculateBudgetUsagePercent(Project $project): ?float
    {
        if ($project->budget === null || $project->budget == 0) {
            return null;
        }

        return ($this->calculateTotal($project) / (float) $project->budget) * 100;
    }

    /**
     * Check if project is overdue based on end_date.
     */
    public function isOverdue(Project $project): bool
    {
        if (! $project->end_date) {
            return false;
        }

        return $project->end_date->isPast();
    }

    /**
     * Get project line summary.
     *
     * @return array{line_count: int, total: float, budget: float|null, variance: float|null, usage_percent: float|null}
     */
    public function getLineSummary(Project $project): array
    {
        return [
            'line_count' => $project->lines->count(),
            'total' => $this->calculateTotal($project),
            'budget' => $project->budget ? (float) $project->budget : null,
            'variance' => $this->calculateBudgetVariance($project),
            'usage_percent' => $this->calculateBudgetUsagePercent($project),
        ];
    }
}
