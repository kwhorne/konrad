<?php

use App\Models\Company;
use App\Models\User;
use App\Models\VatCode;
use App\Models\VatReport;
use App\Models\VatReportLine;
use App\Services\VatReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function setupVatContext(): array
{
    $user = User::factory()->create(['onboarding_completed' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);
    app()->instance('current.company', $company);

    return ['user' => $user->fresh(), 'company' => $company];
}

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = setupVatContext();
    $this->actingAs($this->user);
    $this->service = app(VatReportService::class);
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

describe('updateLine', function () {
    it('updates line with manual override', function () {
        $report = VatReport::factory()->create(['created_by' => $this->user->id]);
        $vatCode = VatCode::factory()->create();
        $line = VatReportLine::factory()->create([
            'vat_report_id' => $report->id,
            'vat_code_id' => $vatCode->id,
            'base_amount' => 10000,
            'vat_amount' => 2500,
            'is_manual_override' => false,
        ]);

        $updated = $this->service->updateLine($line, 15000, 3750, 'Korrigert manuelt');

        expect((float) $updated->base_amount)->toBe(15000.00)
            ->and((float) $updated->vat_amount)->toBe(3750.00)
            ->and($updated->note)->toBe('Korrigert manuelt')
            ->and($updated->is_manual_override)->toBeTrue();
    });

    it('recalculates report totals after line update', function () {
        $report = VatReport::factory()->create([
            'created_by' => $this->user->id,
            'total_output_vat' => 0,
        ]);
        $vatCode = VatCode::factory()->output()->create();
        $line = VatReportLine::factory()->create([
            'vat_report_id' => $report->id,
            'vat_code_id' => $vatCode->id,
            'base_amount' => 10000,
            'vat_amount' => 2500,
        ]);

        $this->service->updateLine($line, 20000, 5000);

        $report->refresh();
        expect((float) $report->total_output_vat)->toBeGreaterThanOrEqual(5000.00);
    });
});

describe('calculateReport', function () {
    it('clears existing lines before calculation', function () {
        $report = VatReport::factory()->create(['created_by' => $this->user->id]);

        $vatCode1 = VatCode::factory()->create(['code' => 'C1']);
        $vatCode2 = VatCode::factory()->create(['code' => 'C2']);
        $vatCode3 = VatCode::factory()->create(['code' => 'C3']);

        VatReportLine::factory()->create([
            'vat_report_id' => $report->id,
            'vat_code_id' => $vatCode1->id,
        ]);
        VatReportLine::factory()->create([
            'vat_report_id' => $report->id,
            'vat_code_id' => $vatCode2->id,
        ]);
        VatReportLine::factory()->create([
            'vat_report_id' => $report->id,
            'vat_code_id' => $vatCode3->id,
        ]);

        expect($report->lines()->count())->toBe(3);

        $this->service->calculateReport($report);

        $report->refresh();
    });

    it('only creates lines for vat codes with amounts', function () {
        $report = VatReport::factory()->create(['created_by' => $this->user->id]);

        VatCode::factory()->create([
            'code' => '1',
            'category' => 'salg_norge',
            'rate' => 25,
        ]);

        $result = $this->service->calculateReport($report);

        expect($result)->toBeInstanceOf(VatReport::class);
    });
});

describe('getReportSummary', function () {
    it('groups lines by category', function () {
        $report = VatReport::factory()->create(['created_by' => $this->user->id]);

        $salgCode = VatCode::factory()->create([
            'code' => 'S1',
            'category' => 'salg_norge',
            'direction' => 'output',
        ]);
        $kjopCode = VatCode::factory()->create([
            'code' => 'K1',
            'category' => 'kjop_norge',
            'direction' => 'input',
        ]);

        VatReportLine::factory()->create([
            'vat_report_id' => $report->id,
            'vat_code_id' => $salgCode->id,
            'base_amount' => 10000,
            'vat_amount' => 2500,
        ]);

        VatReportLine::factory()->create([
            'vat_report_id' => $report->id,
            'vat_code_id' => $kjopCode->id,
            'base_amount' => 5000,
            'vat_amount' => 1250,
        ]);

        $summary = $this->service->getReportSummary($report);

        expect($summary)->toHaveKey('salg_norge')
            ->and($summary)->toHaveKey('kjop_norge')
            ->and($summary['salg_norge']['lines'])->toHaveCount(1)
            ->and($summary['kjop_norge']['lines'])->toHaveCount(1);
    });

    it('calculates totals per category', function () {
        $report = VatReport::factory()->create(['created_by' => $this->user->id]);

        $vatCode1 = VatCode::factory()->create(['category' => 'salg_norge', 'code' => '1']);
        $vatCode2 = VatCode::factory()->create(['category' => 'salg_norge', 'code' => '2']);

        VatReportLine::factory()->create([
            'vat_report_id' => $report->id,
            'vat_code_id' => $vatCode1->id,
            'base_amount' => 10000,
            'vat_amount' => 2500,
        ]);

        VatReportLine::factory()->create([
            'vat_report_id' => $report->id,
            'vat_code_id' => $vatCode2->id,
            'base_amount' => 15000,
            'vat_amount' => 3750,
        ]);

        $summary = $this->service->getReportSummary($report);

        expect($summary['salg_norge']['base_total'])->toBe(25000.0)
            ->and($summary['salg_norge']['vat_total'])->toBe(6250.0);
    });

    it('excludes empty categories', function () {
        $report = VatReport::factory()->create(['created_by' => $this->user->id]);

        $vatCode = VatCode::factory()->create(['category' => 'salg_norge']);

        VatReportLine::factory()->create([
            'vat_report_id' => $report->id,
            'vat_code_id' => $vatCode->id,
        ]);

        $summary = $this->service->getReportSummary($report);

        expect($summary)->toHaveKey('salg_norge')
            ->and($summary)->not->toHaveKey('kjop_norge')
            ->and($summary)->not->toHaveKey('import');
    });
});

describe('bimonthly period handling', function () {
    it('creates report with correct period dates for january-february', function () {
        $report = $this->service->createReport(2024, 1);

        expect($report->period_from->format('Y-m-d'))->toBe('2024-01-01')
            ->and($report->period_to->format('Y-m-d'))->toBe('2024-02-29');
    });

    it('creates report with correct period dates for march-april', function () {
        $report = $this->service->createReport(2024, 2);

        expect($report->period_from->format('Y-m-d'))->toBe('2024-03-01')
            ->and($report->period_to->format('Y-m-d'))->toBe('2024-04-30');
    });

    it('creates report with correct period dates for november-december', function () {
        $report = $this->service->createReport(2024, 6);

        expect($report->period_from->format('Y-m-d'))->toBe('2024-11-01')
            ->and($report->period_to->format('Y-m-d'))->toBe('2024-12-31');
    });
});

describe('report type variations', function () {
    it('creates alminnelig report type by default', function () {
        $report = $this->service->createReport(2024, 1);

        expect($report->report_type)->toBe('alminnelig');
    });

    it('creates primaer report type when specified', function () {
        $report = $this->service->createReport(2024, 1, 'primaer');

        expect($report->report_type)->toBe('primaer');
    });
});
