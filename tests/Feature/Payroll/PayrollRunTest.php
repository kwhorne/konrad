<?php

use App\Models\PayrollEntry;
use App\Models\PayrollRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = createTestCompanyContext();
    $this->actingAs($this->user);
});

// Status constants tests
test('payroll run has status constants', function () {
    expect(PayrollRun::STATUS_DRAFT)->toBe('draft');
    expect(PayrollRun::STATUS_CALCULATED)->toBe('calculated');
    expect(PayrollRun::STATUS_APPROVED)->toBe('approved');
    expect(PayrollRun::STATUS_PAID)->toBe('paid');
    expect(PayrollRun::STATUS_REPORTED)->toBe('reported');
});

// Factory tests
test('payroll run factory creates valid model', function () {
    $run = PayrollRun::factory()->create([
        'company_id' => $this->company->id,
    ]);

    expect($run->company_id)->toBe($this->company->id);
    expect($run->status)->toBe(PayrollRun::STATUS_DRAFT);
});

test('payroll run factory forMonth works correctly', function () {
    $run = PayrollRun::factory()->forMonth(2026, 3)->create([
        'company_id' => $this->company->id,
    ]);

    expect($run->year)->toBe(2026);
    expect($run->month)->toBe(3);
    expect($run->period_start->format('Y-m-d'))->toBe('2026-03-01');
    expect($run->period_end->format('Y-m-d'))->toBe('2026-03-31');
});

// Status label tests
test('status label returns correct Norwegian labels', function () {
    $draft = PayrollRun::factory()->forMonth(2025, 1)->draft()->create(['company_id' => $this->company->id]);
    $calculated = PayrollRun::factory()->forMonth(2025, 2)->calculated()->create(['company_id' => $this->company->id]);
    $approved = PayrollRun::factory()->forMonth(2025, 3)->approved()->create(['company_id' => $this->company->id]);
    $paid = PayrollRun::factory()->forMonth(2025, 4)->paid()->create(['company_id' => $this->company->id]);
    $reported = PayrollRun::factory()->forMonth(2025, 5)->reported()->create(['company_id' => $this->company->id]);

    expect($draft->status_label)->toBe('Utkast');
    expect($calculated->status_label)->toBe('Beregnet');
    expect($approved->status_label)->toBe('Godkjent');
    expect($paid->status_label)->toBe('Utbetalt');
    expect($reported->status_label)->toBe('Rapportert');
});

// Status color tests
test('status color returns correct colors', function () {
    $draft = PayrollRun::factory()->forMonth(2024, 1)->draft()->create(['company_id' => $this->company->id]);
    $calculated = PayrollRun::factory()->forMonth(2024, 2)->calculated()->create(['company_id' => $this->company->id]);
    $approved = PayrollRun::factory()->forMonth(2024, 3)->approved()->create(['company_id' => $this->company->id]);

    expect($draft->status_color)->toBe('zinc');
    expect($calculated->status_color)->toBe('amber');
    expect($approved->status_color)->toBe('blue');
});

// Period label tests
test('period label returns formatted month and year', function () {
    $run = PayrollRun::factory()->forMonth(2026, 1)->create(['company_id' => $this->company->id]);

    expect($run->period_label)->toBe('Januar 2026');
});

test('period label works for all months', function () {
    $months = [
        1 => 'Januar',
        2 => 'Februar',
        3 => 'Mars',
        4 => 'April',
        5 => 'Mai',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'August',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    foreach ($months as $num => $name) {
        $run = PayrollRun::factory()->forMonth(2026, $num)->create(['company_id' => $this->company->id]);
        expect($run->period_label)->toContain($name);
    }
});

// Is editable tests
test('draft run is editable', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);

    expect($run->is_editable)->toBeTrue();
});

test('calculated run is editable', function () {
    $run = PayrollRun::factory()->calculated()->create(['company_id' => $this->company->id]);

    expect($run->is_editable)->toBeTrue();
});

test('approved run is not editable', function () {
    $run = PayrollRun::factory()->approved()->create(['company_id' => $this->company->id]);

    expect($run->is_editable)->toBeFalse();
});

test('paid run is not editable', function () {
    $run = PayrollRun::factory()->paid()->create(['company_id' => $this->company->id]);

    expect($run->is_editable)->toBeFalse();
});

// Approve workflow tests
test('can approve calculated run', function () {
    $run = PayrollRun::factory()->calculated()->create(['company_id' => $this->company->id]);
    $approver = User::factory()->create();

    $result = $run->approve($approver);

    expect($result)->toBeTrue();
    expect($run->fresh()->status)->toBe(PayrollRun::STATUS_APPROVED);
    expect($run->fresh()->approved_by)->toBe($approver->id);
    expect($run->fresh()->approved_at)->not->toBeNull();
});

test('cannot approve draft run', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);
    $approver = User::factory()->create();

    $result = $run->approve($approver);

    expect($result)->toBeFalse();
    expect($run->fresh()->status)->toBe(PayrollRun::STATUS_DRAFT);
});

test('cannot approve already approved run', function () {
    $run = PayrollRun::factory()->approved()->create(['company_id' => $this->company->id]);
    $approver = User::factory()->create();

    $result = $run->approve($approver);

    expect($result)->toBeFalse();
});

// Mark as paid workflow tests
test('can mark approved run as paid', function () {
    $run = PayrollRun::factory()->approved()->create(['company_id' => $this->company->id]);

    $result = $run->markAsPaid();

    expect($result)->toBeTrue();
    expect($run->fresh()->status)->toBe(PayrollRun::STATUS_PAID);
    expect($run->fresh()->paid_at)->not->toBeNull();
});

test('cannot mark draft run as paid', function () {
    $run = PayrollRun::factory()->draft()->create(['company_id' => $this->company->id]);

    $result = $run->markAsPaid();

    expect($result)->toBeFalse();
    expect($run->fresh()->status)->toBe(PayrollRun::STATUS_DRAFT);
});

test('cannot mark calculated run as paid', function () {
    $run = PayrollRun::factory()->calculated()->create(['company_id' => $this->company->id]);

    $result = $run->markAsPaid();

    expect($result)->toBeFalse();
});

// Mark as reported workflow tests
test('can mark paid run as reported', function () {
    $run = PayrollRun::factory()->paid()->create(['company_id' => $this->company->id]);

    $result = $run->markAsReported();

    expect($result)->toBeTrue();
    expect($run->fresh()->status)->toBe(PayrollRun::STATUS_REPORTED);
});

test('cannot mark approved run as reported', function () {
    $run = PayrollRun::factory()->approved()->create(['company_id' => $this->company->id]);

    $result = $run->markAsReported();

    expect($result)->toBeFalse();
});

// Relationship tests
test('payroll run has many entries', function () {
    $run = PayrollRun::factory()->create(['company_id' => $this->company->id]);
    PayrollEntry::factory()->count(3)->create([
        'company_id' => $this->company->id,
        'payroll_run_id' => $run->id,
    ]);

    expect($run->entries)->toHaveCount(3);
});

test('payroll run belongs to created by user', function () {
    $run = PayrollRun::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
    ]);

    expect($run->createdByUser->id)->toBe($this->user->id);
});

test('payroll run belongs to approved by user', function () {
    $approver = User::factory()->create();
    $run = PayrollRun::factory()->approved()->create([
        'company_id' => $this->company->id,
        'approved_by' => $approver->id,
    ]);

    expect($run->approvedByUser->id)->toBe($approver->id);
});

// Recalculate totals tests
test('recalculates totals from entries', function () {
    $run = PayrollRun::factory()->create(['company_id' => $this->company->id]);

    PayrollEntry::factory()->create([
        'company_id' => $this->company->id,
        'payroll_run_id' => $run->id,
        'bruttolonn' => 50000,
        'forskuddstrekk' => 15000,
        'nettolonn' => 35000,
        'feriepenger_grunnlag' => 50000,
        'arbeidsgiveravgift' => 7050,
        'otp_belop' => 1000,
    ]);

    PayrollEntry::factory()->create([
        'company_id' => $this->company->id,
        'payroll_run_id' => $run->id,
        'bruttolonn' => 60000,
        'forskuddstrekk' => 18000,
        'nettolonn' => 42000,
        'feriepenger_grunnlag' => 60000,
        'arbeidsgiveravgift' => 8460,
        'otp_belop' => 1200,
    ]);

    $run->recalculateTotals();

    expect((float) $run->total_bruttolonn)->toBe(110000.0);
    expect((float) $run->total_forskuddstrekk)->toBe(33000.0);
    expect((float) $run->total_nettolonn)->toBe(77000.0);
    expect((float) $run->total_feriepenger_grunnlag)->toBe(110000.0);
    expect((float) $run->total_arbeidsgiveravgift)->toBe(15510.0);
    expect((float) $run->total_otp)->toBe(2200.0);
});

// Total employer cost tests
test('total employer cost sums bruttolonn, aga, and otp', function () {
    $run = PayrollRun::factory()->withTotals(100000, 30000, 14100, 2000)->create([
        'company_id' => $this->company->id,
    ]);

    // 100000 + 14100 + 2000 = 116100
    expect($run->total_employer_cost)->toBe(116100.0);
});

// Scope tests
test('forYear scope filters correctly', function () {
    PayrollRun::factory()->forMonth(2025, 1)->create(['company_id' => $this->company->id]);
    PayrollRun::factory()->forMonth(2025, 2)->create(['company_id' => $this->company->id]);
    PayrollRun::factory()->forMonth(2026, 1)->create(['company_id' => $this->company->id]);

    expect(PayrollRun::forYear(2025)->count())->toBe(2);
    expect(PayrollRun::forYear(2026)->count())->toBe(1);
});

test('ordered scope sorts by year and month descending', function () {
    PayrollRun::factory()->forMonth(2025, 1)->create(['company_id' => $this->company->id]);
    PayrollRun::factory()->forMonth(2026, 3)->create(['company_id' => $this->company->id]);
    PayrollRun::factory()->forMonth(2026, 1)->create(['company_id' => $this->company->id]);

    $runs = PayrollRun::ordered()->get();

    expect($runs->first()->year)->toBe(2026);
    expect($runs->first()->month)->toBe(3);
    expect($runs->last()->year)->toBe(2025);
    expect($runs->last()->month)->toBe(1);
});

// Unique constraint test
test('cannot create duplicate run for same month', function () {
    PayrollRun::factory()->forMonth(2026, 1)->create(['company_id' => $this->company->id]);

    expect(fn () => PayrollRun::factory()->forMonth(2026, 1)->create(['company_id' => $this->company->id]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
