<?php

use App\Models\AnnualAccount;
use App\Models\AnnualAccountNote;
use App\Models\CashFlowStatement;
use App\Models\Company;
use App\Models\User;
use App\Services\AnnualAccountService;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function setupAnnualAccountContext(): array
{
    $user = User::factory()->create(['onboarding_completed' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);
    app()->instance('current.company', $company);

    return ['user' => $user->fresh(), 'company' => $company];
}

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = setupAnnualAccountContext();
    $this->actingAs($this->user);
});

function mockReportService(array $incomeStatement = [], array $balanceSheet = []): ReportService
{
    $defaultIncomeStatement = [
        'total_revenue' => 5000000,
        'operating_profit' => 800000,
        'profit_before_tax' => 750000,
        'net_profit' => 585000,
    ];

    $defaultBalanceSheet = [
        'total_assets' => 3000000,
        'total_equity' => 1200000,
        'total_liabilities' => 1800000,
    ];

    $mock = Mockery::mock(ReportService::class);
    $mock->shouldReceive('getIncomeStatement')
        ->andReturn(array_merge($defaultIncomeStatement, $incomeStatement));
    $mock->shouldReceive('getBalanceSheet')
        ->andReturn(array_merge($defaultBalanceSheet, $balanceSheet));

    return $mock;
}

describe('createAnnualAccount', function () {
    test('creates annual account with correct fiscal year and period', function () {
        $service = new AnnualAccountService(mockReportService());

        $annualAccount = $service->createAnnualAccount(2024, $this->user->id);

        expect($annualAccount)->toBeInstanceOf(AnnualAccount::class)
            ->and($annualAccount->fiscal_year)->toBe(2024)
            ->and($annualAccount->period_start->format('Y-m-d'))->toBe('2024-01-01')
            ->and($annualAccount->period_end->format('Y-m-d'))->toBe('2024-12-31')
            ->and($annualAccount->status)->toBe('draft')
            ->and($annualAccount->created_by)->toBe($this->user->id);
    });

    test('creates annual account with populated financial data from accounting', function () {
        $service = new AnnualAccountService(mockReportService([
            'total_revenue' => 10000000,
            'operating_profit' => 2000000,
            'profit_before_tax' => 1800000,
            'net_profit' => 1404000,
        ], [
            'total_assets' => 5000000,
            'total_equity' => 2000000,
            'total_liabilities' => 3000000,
        ]));

        $annualAccount = $service->createAnnualAccount(2024, $this->user->id);

        expect((float) $annualAccount->revenue)->toBe(10000000.00)
            ->and((float) $annualAccount->operating_profit)->toBe(2000000.00)
            ->and((float) $annualAccount->profit_before_tax)->toBe(1800000.00)
            ->and((float) $annualAccount->total_assets)->toBe(5000000.00)
            ->and((float) $annualAccount->total_equity)->toBe(2000000.00)
            ->and((float) $annualAccount->total_liabilities)->toBe(3000000.00);
    });

    test('initializes standard notes for the annual account', function () {
        $service = new AnnualAccountService(mockReportService());

        $annualAccount = $service->createAnnualAccount(2024, $this->user->id);
        $annualAccount->load('accountNotes');

        $noteCount = count(AnnualAccountNote::NOTE_TYPES);
        expect($annualAccount->accountNotes)->toHaveCount($noteCount);

        $requiredNotes = $annualAccount->accountNotes->filter(fn ($n) => $n->is_required);
        expect($requiredNotes->count())->toBeGreaterThanOrEqual(2);
    });

    test('determines company size as small for small revenue and assets', function () {
        $service = new AnnualAccountService(mockReportService([
            'total_revenue' => 50000000,
        ], [
            'total_assets' => 25000000,
        ]));

        $annualAccount = $service->createAnnualAccount(2024, $this->user->id);
        $annualAccount->average_employees = 30;
        $annualAccount->save();

        expect($annualAccount->determineSize())->toBe(AnnualAccount::SIZE_SMALL);
    });

    test('determines company size as large when thresholds are exceeded', function () {
        $service = new AnnualAccountService(mockReportService([
            'total_revenue' => 400000000,
        ], [
            'total_assets' => 200000000,
        ]));

        $annualAccount = $service->createAnnualAccount(2024, $this->user->id);
        $annualAccount->average_employees = 300;
        $annualAccount->save();

        expect($annualAccount->determineSize())->toBe(AnnualAccount::SIZE_LARGE);
    });
});

describe('initializeNotes', function () {
    test('creates all standard note types', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->forYear(2024)
            ->create(['created_by' => $this->user->id]);

        $service->initializeNotes($annualAccount, $this->user->id);

        $noteTypes = $annualAccount->accountNotes->pluck('note_type')->toArray();
        foreach (array_keys(AnnualAccountNote::NOTE_TYPES) as $type) {
            expect($noteTypes)->toContain($type);
        }
    });

    test('sets correct note numbers sequentially', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->forYear(2024)
            ->create(['created_by' => $this->user->id]);

        $service->initializeNotes($annualAccount, $this->user->id);

        $noteNumbers = $annualAccount->accountNotes->pluck('note_number')->sort()->values()->toArray();
        expect($noteNumbers)->toBe(range(1, count(AnnualAccountNote::NOTE_TYPES)));
    });

    test('adds default content to accounting principles note', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->forYear(2024)
            ->create(['created_by' => $this->user->id]);

        $service->initializeNotes($annualAccount, $this->user->id);

        $principlesNote = $annualAccount->accountNotes
            ->firstWhere('note_type', 'accounting_principles');

        expect($principlesNote->content)->toContain('regnskapsloven')
            ->and($principlesNote->content)->toContain('god regnskapsskikk');
    });
});

describe('initializeCashFlowStatement', function () {
    test('creates cash flow statement for annual account', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->forYear(2024)
            ->create([
                'created_by' => $this->user->id,
                'profit_before_tax' => 1000000,
            ]);

        $cashFlow = $service->initializeCashFlowStatement($annualAccount, $this->user->id);

        expect($cashFlow)->toBeInstanceOf(CashFlowStatement::class)
            ->and($cashFlow->annual_account_id)->toBe($annualAccount->id)
            ->and((float) $cashFlow->profit_before_tax)->toBe(1000000.00)
            ->and($cashFlow->created_by)->toBe($this->user->id);
    });
});

describe('updateNote', function () {
    test('updates note content', function () {
        $service = new AnnualAccountService(mockReportService());
        $note = AnnualAccountNote::factory()
            ->accountingPrinciples()
            ->create(['created_by' => $this->user->id]);

        $updatedNote = $service->updateNote($note, [
            'content' => 'Updated accounting principles content',
        ]);

        expect($updatedNote->content)->toBe('Updated accounting principles content');
    });

    test('updates note visibility', function () {
        $service = new AnnualAccountService(mockReportService());
        $note = AnnualAccountNote::factory()
            ->visible()
            ->create(['created_by' => $this->user->id]);

        $updatedNote = $service->updateNote($note, [
            'is_visible' => false,
        ]);

        expect($updatedNote->is_visible)->toBeFalse();
    });
});

describe('reorderNotes', function () {
    test('updates sort order of notes', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->forYear(2024)
            ->create(['created_by' => $this->user->id]);

        $note = AnnualAccountNote::factory()
            ->create([
                'annual_account_id' => $annualAccount->id,
                'note_number' => 1,
                'sort_order' => 5,
                'created_by' => $this->user->id,
            ]);

        $service->reorderNotes($annualAccount, [$note->id]);

        $note->refresh();

        expect($note->sort_order)->toBe(1);
    });
});

describe('approve', function () {
    test('changes status to approved', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->draft()
            ->create(['created_by' => $this->user->id]);

        $approved = $service->approve($annualAccount, $this->user->id);

        expect($approved->status)->toBe('approved')
            ->and($approved->approved_by)->toBe($this->user->id)
            ->and($approved->board_approval_date)->not->toBeNull();
    });
});

describe('validate', function () {
    test('returns valid when all requirements are met', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->withBalancedEquation()
            ->approved()
            ->create([
                'created_by' => $this->user->id,
                'average_employees' => 0,
            ]);

        AnnualAccountNote::factory()
            ->accountingPrinciples()
            ->visible()
            ->create([
                'annual_account_id' => $annualAccount->id,
                'created_by' => $this->user->id,
            ]);

        AnnualAccountNote::factory()
            ->equity()
            ->visible()
            ->create([
                'annual_account_id' => $annualAccount->id,
                'created_by' => $this->user->id,
            ]);

        $result = $service->validate($annualAccount);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty();
    });

    test('returns error when balance equation is invalid', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->withUnbalancedEquation()
            ->create(['created_by' => $this->user->id]);

        $result = $service->validate($annualAccount);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Balansen stemmer ikke: Eiendeler != Egenkapital + Gjeld');
    });

    test('returns error when required note is missing', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->withBalancedEquation()
            ->create([
                'created_by' => $this->user->id,
                'average_employees' => 0,
            ]);

        $result = $service->validate($annualAccount);

        expect($result['valid'])->toBeFalse()
            ->and(collect($result['errors'])->filter(fn ($e) => str_contains($e, 'Mangler påkrevd note')))->not->toBeEmpty();
    });

    test('returns error when required note has empty content', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->withBalancedEquation()
            ->create([
                'created_by' => $this->user->id,
                'average_employees' => 0,
            ]);

        AnnualAccountNote::factory()
            ->accountingPrinciples()
            ->visible()
            ->empty()
            ->create([
                'annual_account_id' => $annualAccount->id,
                'created_by' => $this->user->id,
            ]);

        AnnualAccountNote::factory()
            ->equity()
            ->visible()
            ->create([
                'annual_account_id' => $annualAccount->id,
                'created_by' => $this->user->id,
            ]);

        $result = $service->validate($annualAccount);

        expect($result['valid'])->toBeFalse()
            ->and(collect($result['errors'])->filter(fn ($e) => str_contains($e, 'har ingen innhold')))->not->toBeEmpty();
    });

    test('returns error when cash flow statement is required but missing', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->medium()
            ->withBalancedEquation()
            ->create(['created_by' => $this->user->id]);

        $result = $service->validate($annualAccount);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Kontantstrømoppstilling er påkrevd for mellomstore og store foretak.');
    });

    test('returns warning when board approval date is missing', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->small()
            ->withBalancedEquation()
            ->create([
                'created_by' => $this->user->id,
                'board_approval_date' => null,
                'average_employees' => 0,
            ]);

        AnnualAccountNote::factory()
            ->accountingPrinciples()
            ->visible()
            ->create([
                'annual_account_id' => $annualAccount->id,
                'created_by' => $this->user->id,
            ]);

        AnnualAccountNote::factory()
            ->equity()
            ->visible()
            ->create([
                'annual_account_id' => $annualAccount->id,
                'created_by' => $this->user->id,
            ]);

        $result = $service->validate($annualAccount);

        expect($result['warnings'])->toContain('Styregodkjenningsdato er ikke registrert.');
    });

    test('returns warning when auditor is missing for medium/large company', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->medium()
            ->withBalancedEquation()
            ->create([
                'created_by' => $this->user->id,
                'auditor_name' => null,
                'board_approval_date' => now(),
            ]);

        CashFlowStatement::factory()->create([
            'annual_account_id' => $annualAccount->id,
            'created_by' => $this->user->id,
        ]);

        $result = $service->validate($annualAccount);

        expect($result['warnings'])->toContain('Revisor er ikke registrert.');
    });
});

describe('getSummary', function () {
    test('returns null when no annual account exists for year', function () {
        $service = new AnnualAccountService(mockReportService());

        $summary = $service->getSummary(2024);

        expect($summary)->toBeNull();
    });

    test('returns summary with all expected fields', function () {
        $service = new AnnualAccountService(mockReportService());
        $annualAccount = AnnualAccount::factory()
            ->forYear(2024)
            ->withBalancedEquation()
            ->create(['created_by' => $this->user->id]);

        AnnualAccountNote::factory()
            ->visible()
            ->create([
                'annual_account_id' => $annualAccount->id,
                'created_by' => $this->user->id,
            ]);

        $summary = $service->getSummary(2024);

        expect($summary)->toHaveKeys([
            'annual_account',
            'notes_count',
            'has_cash_flow',
            'validation',
            'deadline',
            'days_until_deadline',
            'is_overdue',
        ])
            ->and($summary['annual_account']->id)->toBe($annualAccount->id)
            ->and($summary['notes_count'])->toBe(1);
    });
});

describe('cloneFromPreviousYear', function () {
    test('returns null when no previous year exists', function () {
        $service = new AnnualAccountService(mockReportService());

        $result = $service->cloneFromPreviousYear(2025, $this->user->id);

        expect($result)->toBeNull();
    });

    test('creates new account based on previous year', function () {
        $service = new AnnualAccountService(mockReportService());

        $previousAccount = AnnualAccount::factory()
            ->forYear(2023)
            ->create(['created_by' => $this->user->id]);

        $newAccount = $service->cloneFromPreviousYear(2024, $this->user->id);

        expect($newAccount)->toBeInstanceOf(AnnualAccount::class)
            ->and($newAccount->fiscal_year)->toBe(2024)
            ->and($newAccount->period_start->format('Y-m-d'))->toBe('2024-01-01')
            ->and($newAccount->period_end->format('Y-m-d'))->toBe('2024-12-31');

        $newAccount->load('accountNotes');
        expect($newAccount->accountNotes)->not->toBeEmpty();
    });
});

describe('getYearComparison', function () {
    test('returns empty array when either year is missing', function () {
        $service = new AnnualAccountService(mockReportService());

        AnnualAccount::factory()
            ->forYear(2024)
            ->create(['created_by' => $this->user->id]);

        $comparison = $service->getYearComparison(2024, 2023);

        expect($comparison)->toBeEmpty();
    });

    test('returns comparison data for all metrics', function () {
        $service = new AnnualAccountService(mockReportService());

        AnnualAccount::factory()
            ->forYear(2023)
            ->create([
                'created_by' => $this->user->id,
                'revenue' => 1000000,
                'operating_profit' => 200000,
                'profit_before_tax' => 180000,
                'net_profit' => 140400,
                'total_assets' => 500000,
                'total_equity' => 200000,
            ]);

        AnnualAccount::factory()
            ->forYear(2024)
            ->create([
                'created_by' => $this->user->id,
                'revenue' => 1200000,
                'operating_profit' => 250000,
                'profit_before_tax' => 230000,
                'net_profit' => 179400,
                'total_assets' => 600000,
                'total_equity' => 250000,
            ]);

        $comparison = $service->getYearComparison(2024, 2023);

        expect($comparison)->toHaveKeys(['revenue', 'operating_profit', 'profit_before_tax', 'net_profit', 'total_assets', 'total_equity']);

        expect((float) $comparison['revenue']['current'])->toBe(1200000.00)
            ->and((float) $comparison['revenue']['previous'])->toBe(1000000.00)
            ->and((float) $comparison['revenue']['change'])->toBe(200000.00)
            ->and($comparison['revenue']['change_percent'])->toBe(20.0);
    });

    test('handles zero previous values without division error', function () {
        $service = new AnnualAccountService(mockReportService());

        AnnualAccount::factory()
            ->forYear(2023)
            ->create([
                'created_by' => $this->user->id,
                'revenue' => 0,
                'operating_profit' => 0,
            ]);

        AnnualAccount::factory()
            ->forYear(2024)
            ->create([
                'created_by' => $this->user->id,
                'revenue' => 100000,
                'operating_profit' => 20000,
            ]);

        $comparison = $service->getYearComparison(2024, 2023);

        expect($comparison['revenue']['change_percent'])->toBe(0.0)
            ->and($comparison['operating_profit']['change_percent'])->toBe(0.0);
    });
});

describe('company size determination', function () {
    test('small company meets 2 of 3 small thresholds', function () {
        $annualAccount = AnnualAccount::factory()
            ->create([
                'created_by' => $this->user->id,
                'revenue' => 60000000,
                'total_assets' => 30000000,
                'average_employees' => 40,
            ]);

        expect($annualAccount->determineSize())->toBe(AnnualAccount::SIZE_SMALL);
    });

    test('medium company falls between small and large', function () {
        $annualAccount = AnnualAccount::factory()
            ->create([
                'created_by' => $this->user->id,
                'revenue' => 150000000,
                'total_assets' => 100000000,
                'average_employees' => 100,
            ]);

        expect($annualAccount->determineSize())->toBe(AnnualAccount::SIZE_MEDIUM);
    });

    test('large company meets 2 of 3 large thresholds', function () {
        $annualAccount = AnnualAccount::factory()
            ->create([
                'created_by' => $this->user->id,
                'revenue' => 400000000,
                'total_assets' => 200000000,
                'average_employees' => 300,
            ]);

        expect($annualAccount->determineSize())->toBe(AnnualAccount::SIZE_LARGE);
    });
});

describe('annual account status methods', function () {
    test('isDraft returns true for draft status', function () {
        $annualAccount = AnnualAccount::factory()
            ->draft()
            ->create(['created_by' => $this->user->id]);

        expect($annualAccount->isDraft())->toBeTrue()
            ->and($annualAccount->isApproved())->toBeFalse()
            ->and($annualAccount->isSubmitted())->toBeFalse();
    });

    test('isApproved returns true for approved status', function () {
        $annualAccount = AnnualAccount::factory()
            ->approved()
            ->create(['created_by' => $this->user->id]);

        expect($annualAccount->isApproved())->toBeTrue()
            ->and($annualAccount->isDraft())->toBeFalse();
    });

    test('isSubmitted returns true for submitted and accepted status', function () {
        $annualAccount = AnnualAccount::factory()
            ->submitted()
            ->create(['created_by' => $this->user->id]);

        expect($annualAccount->isSubmitted())->toBeTrue();
    });

    test('canBeEdited returns true for editable statuses', function () {
        $draft = AnnualAccount::factory()
            ->draft()
            ->forYear(2021)
            ->create(['created_by' => $this->user->id]);
        $approved = AnnualAccount::factory()
            ->approved()
            ->forYear(2022)
            ->create(['created_by' => $this->user->id]);
        $submitted = AnnualAccount::factory()
            ->submitted()
            ->forYear(2023)
            ->create(['created_by' => $this->user->id]);

        expect($draft->canBeEdited())->toBeTrue()
            ->and($approved->canBeEdited())->toBeTrue()
            ->and($submitted->canBeEdited())->toBeFalse();
    });
});

describe('financial ratios', function () {
    test('calculates equity ratio correctly', function () {
        $annualAccount = AnnualAccount::factory()
            ->create([
                'created_by' => $this->user->id,
                'total_assets' => 1000000,
                'total_equity' => 400000,
            ]);

        expect($annualAccount->getEquityRatio())->toBe(40.00);
    });

    test('equity ratio returns 0 when total assets is 0', function () {
        $annualAccount = AnnualAccount::factory()
            ->create([
                'created_by' => $this->user->id,
                'total_assets' => 0,
                'total_equity' => 0,
            ]);

        expect($annualAccount->getEquityRatio())->toBe(0.0);
    });

    test('calculates profit margin correctly', function () {
        $annualAccount = AnnualAccount::factory()
            ->create([
                'created_by' => $this->user->id,
                'revenue' => 1000000,
                'net_profit' => 100000,
            ]);

        expect($annualAccount->getProfitMargin())->toBe(10.00);
    });

    test('calculates operating margin correctly', function () {
        $annualAccount = AnnualAccount::factory()
            ->create([
                'created_by' => $this->user->id,
                'revenue' => 1000000,
                'operating_profit' => 150000,
            ]);

        expect($annualAccount->getOperatingMargin())->toBe(15.00);
    });
});

describe('deadline and overdue calculations', function () {
    test('calculates deadline as July 31st of following year', function () {
        $annualAccount = AnnualAccount::factory()
            ->forYear(2024)
            ->create(['created_by' => $this->user->id]);

        $deadline = $annualAccount->getDeadline();

        expect($deadline->format('Y-m-d'))->toBe('2025-07-31');
    });

    test('isOverdue returns true when deadline has passed and not submitted', function () {
        $annualAccount = AnnualAccount::factory()
            ->forYear(2020)
            ->draft()
            ->create(['created_by' => $this->user->id]);

        expect($annualAccount->isOverdue())->toBeTrue();
    });

    test('isOverdue returns false when submitted', function () {
        $annualAccount = AnnualAccount::factory()
            ->forYear(2020)
            ->submitted()
            ->create(['created_by' => $this->user->id]);

        expect($annualAccount->isOverdue())->toBeFalse();
    });
});

describe('required notes by company size', function () {
    test('small company requires accounting principles and equity', function () {
        $annualAccount = AnnualAccount::factory()
            ->small()
            ->create([
                'created_by' => $this->user->id,
                'average_employees' => 0,
            ]);

        $required = $annualAccount->getRequiredNotes();

        expect($required)->toContain('accounting_principles')
            ->and($required)->toContain('equity')
            ->and($required)->not->toContain('fixed_assets')
            ->and($required)->not->toContain('related_parties');
    });

    test('medium company requires additional notes', function () {
        $annualAccount = AnnualAccount::factory()
            ->medium()
            ->create([
                'created_by' => $this->user->id,
                'average_employees' => 10,
            ]);

        $required = $annualAccount->getRequiredNotes();

        expect($required)->toContain('accounting_principles')
            ->and($required)->toContain('equity')
            ->and($required)->toContain('fixed_assets')
            ->and($required)->toContain('debt')
            ->and($required)->toContain('related_parties')
            ->and($required)->toContain('employees');
    });

    test('employees note required when average employees greater than 0', function () {
        $annualAccount = AnnualAccount::factory()
            ->small()
            ->create([
                'created_by' => $this->user->id,
                'average_employees' => 5,
            ]);

        $required = $annualAccount->getRequiredNotes();

        expect($required)->toContain('employees');
    });
});
