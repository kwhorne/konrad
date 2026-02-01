<?php

use App\Models\Company;
use App\Models\User;
use App\Models\VatCode;
use App\Models\VatReport;
use App\Models\VatReportLine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['onboarding_completed' => true]);
    $this->company = Company::factory()->withOwner($this->user)->create();
    $this->user->update(['current_company_id' => $this->company->id]);
    app()->instance('current.company', $this->company);
    $this->actingAs($this->user);
});

// Period Name - Bimonthly
test('vat report bimonthly period name returns correct norwegian text', function (int $period, string $expectedName) {
    $report = VatReport::factory()->create([
        'period_type' => 'bimonthly',
        'year' => 2024,
        'period' => $period,
    ]);

    expect($report->period_name)->toBe($expectedName.' 2024');
})->with([
    [1, 'Januar - Februar'],
    [2, 'Mars - April'],
    [3, 'Mai - Juni'],
    [4, 'Juli - August'],
    [5, 'September - Oktober'],
    [6, 'November - Desember'],
]);

// Period Name - Monthly
test('vat report monthly period name returns correct norwegian month', function (int $period, string $expectedMonth) {
    $report = VatReport::factory()->monthly()->create([
        'year' => 2024,
        'period' => $period,
    ]);

    expect($report->period_name)->toBe($expectedMonth.' 2024');
})->with([
    [1, 'Januar'],
    [2, 'Februar'],
    [3, 'Mars'],
    [4, 'April'],
    [5, 'Mai'],
    [6, 'Juni'],
    [7, 'Juli'],
    [8, 'August'],
    [9, 'September'],
    [10, 'Oktober'],
    [11, 'November'],
    [12, 'Desember'],
]);

// Report Type Name
test('vat report type name returns correct norwegian translation', function (string $type, string $expectedName) {
    $report = VatReport::factory()->create(['report_type' => $type]);

    expect($report->report_type_name)->toBe($expectedName);
})->with([
    ['alminnelig', 'Alminnelig næring'],
    ['primaer', 'Primærnæring'],
]);

// Period Type Name
test('vat report period type name returns correct norwegian translation', function (string $type, string $expectedName) {
    $report = VatReport::factory()->create(['period_type' => $type]);

    expect($report->period_type_name)->toBe($expectedName);
})->with([
    ['bimonthly', 'Tomånedlig'],
    ['monthly', 'Månedlig'],
    ['annual', 'Årlig'],
]);

// Status Name
test('vat report status name returns correct norwegian translation', function (string $status, string $expectedName) {
    $report = VatReport::factory()->create(['status' => $status]);

    expect($report->status_name)->toBe($expectedName);
})->with([
    ['draft', 'Utkast'],
    ['calculated', 'Beregnet'],
    ['submitted', 'Sendt'],
    ['accepted', 'Godkjent'],
    ['rejected', 'Avvist'],
]);

// Status Color
test('vat report status color returns correct color', function (string $status, string $expectedColor) {
    $report = VatReport::factory()->create(['status' => $status]);

    expect($report->status_color)->toBe($expectedColor);
})->with([
    ['draft', 'zinc'],
    ['calculated', 'yellow'],
    ['submitted', 'blue'],
    ['accepted', 'green'],
    ['rejected', 'red'],
]);

// Relationships
test('vat report belongs to creator', function () {
    $creator = User::factory()->create();
    $report = VatReport::factory()->create(['created_by' => $creator->id]);

    expect($report->creator->id)->toBe($creator->id);
});

test('vat report belongs to submitter when submitted', function () {
    $submitter = User::factory()->create();
    $report = VatReport::factory()->submitted()->create(['submitted_by' => $submitter->id]);

    expect($report->submitter->id)->toBe($submitter->id);
});

test('vat report can have lines', function () {
    $report = VatReport::factory()->create();

    VatReportLine::factory()->count(3)->create([
        'vat_report_id' => $report->id,
    ]);

    expect($report->lines)->toHaveCount(3);
});

// Recalculate Totals
test('vat report recalculates totals from lines', function () {
    $report = VatReport::factory()->create();
    $outputCode = VatCode::factory()->output()->create();
    $inputCode = VatCode::factory()->input()->create();

    VatReportLine::factory()->create([
        'vat_report_id' => $report->id,
        'vat_code_id' => $outputCode->id,
        'base_amount' => 10000,
        'vat_rate' => 25,
        'vat_amount' => 2500,
    ]);

    VatReportLine::factory()->create([
        'vat_report_id' => $report->id,
        'vat_code_id' => $inputCode->id,
        'base_amount' => 4000,
        'vat_rate' => 25,
        'vat_amount' => -1000,
    ]);

    $report->recalculateTotals();

    expect((float) $report->total_output_vat)->toBe(2500.0);
    expect((float) $report->total_input_vat)->toBe(1000.0);
    expect((float) $report->vat_payable)->toBe(1500.0);
    expect($report->status)->toBe('calculated');
    expect($report->calculated_at)->not->toBeNull();
});

// Bimonthly Period Dates
test('vat report gets correct bimonthly period dates', function (int $period, int $fromMonth, int $toMonth) {
    $dates = VatReport::getBimonthlyPeriodDates(2024, $period);

    expect($dates['from']->month)->toBe($fromMonth);
    expect($dates['from']->day)->toBe(1);
    expect($dates['to']->month)->toBe($toMonth);
    expect($dates['to']->day)->toBe($dates['to']->daysInMonth);
})->with([
    [1, 1, 2],
    [2, 3, 4],
    [3, 5, 6],
    [4, 7, 8],
    [5, 9, 10],
    [6, 11, 12],
]);

// Scopes
test('byYear scope filters correctly', function () {
    VatReport::factory()->create(['year' => 2022, 'period' => 1]);
    VatReport::factory()->create(['year' => 2022, 'period' => 2]);
    VatReport::factory()->create(['year' => 2021, 'period' => 1]);
    VatReport::factory()->create(['year' => 2021, 'period' => 2]);
    VatReport::factory()->create(['year' => 2021, 'period' => 3]);

    expect(VatReport::byYear(2022)->count())->toBe(2);
});

test('byStatus scope filters correctly', function () {
    VatReport::factory()->create(['status' => 'draft', 'year' => 2020, 'period' => 1]);
    VatReport::factory()->create(['status' => 'draft', 'year' => 2020, 'period' => 2]);
    VatReport::factory()->calculated()->create(['year' => 2020, 'period' => 3]);
    VatReport::factory()->calculated()->create(['year' => 2020, 'period' => 4]);
    VatReport::factory()->calculated()->create(['year' => 2020, 'period' => 5]);

    expect(VatReport::byStatus('draft')->count())->toBe(2);
    expect(VatReport::byStatus('calculated')->count())->toBe(3);
});

test('ordered scope sorts by year and period descending', function () {
    $old = VatReport::factory()->create(['year' => 2023, 'period' => 1]);
    $new = VatReport::factory()->create(['year' => 2024, 'period' => 6]);
    $middle = VatReport::factory()->create(['year' => 2024, 'period' => 1]);

    $reports = VatReport::ordered()->pluck('id')->toArray();

    expect($reports[0])->toBe($new->id);
    expect($reports[1])->toBe($middle->id);
    expect($reports[2])->toBe($old->id);
});

// Soft Deletes
test('vat report can be soft deleted', function () {
    $report = VatReport::factory()->create();

    $report->delete();

    expect($report->trashed())->toBeTrue();
    expect(VatReport::count())->toBe(0);
    expect(VatReport::withTrashed()->count())->toBe(1);
});

// Factory States
test('vat report factory calculated state works', function () {
    $report = VatReport::factory()->calculated()->create();

    expect($report->status)->toBe('calculated');
    expect($report->calculated_at)->not->toBeNull();
});

test('vat report factory submitted state works', function () {
    $report = VatReport::factory()->submitted()->create();

    expect($report->status)->toBe('submitted');
    expect($report->submitted_at)->not->toBeNull();
    expect($report->submitted_by)->not->toBeNull();
});

test('vat report factory accepted state works', function () {
    $report = VatReport::factory()->accepted()->create();

    expect($report->status)->toBe('accepted');
    expect($report->altinn_reference)->not->toBeNull();
});

test('vat report factory monthly state works', function () {
    $report = VatReport::factory()->monthly()->create();

    expect($report->period_type)->toBe('monthly');
});

test('vat report factory primaer state works', function () {
    $report = VatReport::factory()->primaer()->create();

    expect($report->report_type)->toBe('primaer');
});
