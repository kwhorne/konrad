<?php

use App\Models\User;
use App\Models\VatReport;
use App\Services\VatReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(VatReportService::class);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('deletes a draft report', function () {
    $report = VatReport::factory()->create(['status' => 'draft']);
    $id = $report->id;

    $result = $this->service->deleteReport($report);

    expect($result)->toBeTrue();
    expect(VatReport::find($id))->toBeNull();
});

it('cannot delete submitted report', function () {
    $report = VatReport::factory()->submitted()->create();

    $result = $this->service->deleteReport($report);

    expect($result)->toBeFalse();
    expect(VatReport::find($report->id))->not->toBeNull();
});

it('cannot delete accepted report', function () {
    $report = VatReport::factory()->accepted()->create();

    $result = $this->service->deleteReport($report);

    expect($result)->toBeFalse();
});

it('gets available years', function () {
    $years = $this->service->getAvailableYears();
    $currentYear = now()->year;

    expect($years)->toBeArray();
    expect($years)->toHaveCount(6); // Default 5 years back + current
    expect($years[0])->toBe($currentYear);
    expect($years[5])->toBe($currentYear - 5);
});

it('gets available years with custom years back', function () {
    $years = $this->service->getAvailableYears(2);
    $currentYear = now()->year;

    expect($years)->toHaveCount(3);
    expect($years[0])->toBe($currentYear);
    expect($years[2])->toBe($currentYear - 2);
});

it('checks if draft report can be deleted', function () {
    $draft = VatReport::factory()->create(['status' => 'draft']);
    $submitted = VatReport::factory()->submitted()->create();
    $accepted = VatReport::factory()->accepted()->create();

    expect($this->service->canDelete($draft))->toBeTrue();
    expect($this->service->canDelete($submitted))->toBeFalse();
    expect($this->service->canDelete($accepted))->toBeFalse();
});

it('checks if draft report can be submitted', function () {
    $draft = VatReport::factory()->create(['status' => 'draft']);
    $calculated = VatReport::factory()->calculated()->create();
    $submitted = VatReport::factory()->submitted()->create();

    expect($this->service->canSubmit($draft))->toBeTrue();
    expect($this->service->canSubmit($calculated))->toBeFalse();
    expect($this->service->canSubmit($submitted))->toBeFalse();
});

it('checks if submitted report can change status', function () {
    $draft = VatReport::factory()->create(['status' => 'draft']);
    $submitted = VatReport::factory()->submitted()->create();
    $accepted = VatReport::factory()->accepted()->create();

    expect($this->service->canChangeStatus($draft))->toBeFalse();
    expect($this->service->canChangeStatus($submitted))->toBeTrue();
    expect($this->service->canChangeStatus($accepted))->toBeFalse();
});

it('checks if report exists for period', function () {
    VatReport::factory()->create(['year' => 2026, 'period' => 1]);

    expect($this->service->reportExists(2026, 1))->toBeTrue();
    expect($this->service->reportExists(2026, 2))->toBeFalse();
    expect($this->service->reportExists(2025, 1))->toBeFalse();
});

it('creates a report for a period', function () {
    $report = $this->service->createReport(2026, 3);

    expect($report)->toBeInstanceOf(VatReport::class);
    expect($report->year)->toBe(2026);
    expect($report->period)->toBe(3);
    expect($report->status)->toBe('draft');
    expect($report->period_type)->toBe('bimonthly');
    expect($report->created_by)->toBe($this->user->id);
});

it('gets available periods excluding existing reports', function () {
    VatReport::factory()->create(['year' => 2026, 'period' => 1]);
    VatReport::factory()->create(['year' => 2026, 'period' => 3]);

    $periods = $this->service->getAvailablePeriods(2026);

    expect($periods)->toHaveCount(4);
    expect($periods->pluck('period')->toArray())->toBe([2, 4, 5, 6]);
});

it('gets all periods when none exist', function () {
    $periods = $this->service->getAvailablePeriods(2026);

    expect($periods)->toHaveCount(6);
    expect($periods->pluck('period')->toArray())->toBe([1, 2, 3, 4, 5, 6]);
});

it('gets current period correctly', function () {
    $current = $this->service->getCurrentPeriod();

    expect($current)->toHaveKeys(['year', 'period']);
    expect($current['year'])->toBe(now()->year);
    expect($current['period'])->toBeGreaterThanOrEqual(1);
    expect($current['period'])->toBeLessThanOrEqual(6);
});

it('submits a report with altinn reference', function () {
    $report = VatReport::factory()->create(['status' => 'draft']);

    $submitted = $this->service->submitReport($report, 'ALT-2026-12345');

    expect($submitted->status)->toBe('submitted');
    expect($submitted->altinn_reference)->toBe('ALT-2026-12345');
    expect($submitted->submitted_by)->toBe($this->user->id);
    expect($submitted->submitted_at)->not->toBeNull();
});

it('accepts a report', function () {
    $report = VatReport::factory()->submitted()->create();

    $accepted = $this->service->acceptReport($report);

    expect($accepted->status)->toBe('accepted');
});

it('rejects a report', function () {
    $report = VatReport::factory()->submitted()->create();

    $rejected = $this->service->rejectReport($report);

    expect($rejected->status)->toBe('rejected');
});

it('updates a report note', function () {
    $report = VatReport::factory()->create(['note' => null]);

    $updated = $this->service->updateNote($report, 'Test merknad');

    expect($updated->note)->toBe('Test merknad');
});

it('gets or creates draft report', function () {
    // First call creates
    $report1 = $this->service->getOrCreateDraftReport(2026, 4);

    expect($report1)->toBeInstanceOf(VatReport::class);
    expect($report1->year)->toBe(2026);
    expect($report1->period)->toBe(4);

    // Second call returns existing
    $report2 = $this->service->getOrCreateDraftReport(2026, 4);

    expect($report2->id)->toBe($report1->id);
});
