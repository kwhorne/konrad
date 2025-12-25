<?php

namespace App\Services;

use App\Models\AnnualAccount;
use App\Models\AnnualAccountNote;
use App\Models\CashFlowStatement;
use Carbon\Carbon;

class AnnualAccountService
{
    public function __construct(private ReportService $reportService) {}

    /**
     * Create a new annual account for a fiscal year.
     */
    public function createAnnualAccount(int $year, int $createdBy): AnnualAccount
    {
        $periodStart = Carbon::create($year, 1, 1);
        $periodEnd = Carbon::create($year, 12, 31);

        // Create the annual account
        $annualAccount = AnnualAccount::create([
            'fiscal_year' => $year,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'company_size' => AnnualAccount::SIZE_SMALL,
            'status' => 'draft',
            'created_by' => $createdBy,
        ]);

        // Populate from accounting data
        $this->populateFromAccounting($annualAccount);

        // Initialize standard notes
        $this->initializeNotes($annualAccount, $createdBy);

        // Create cash flow statement if needed
        if ($annualAccount->requiresCashFlowStatement()) {
            $this->initializeCashFlowStatement($annualAccount, $createdBy);
        }

        return $annualAccount->fresh(['notes', 'cashFlowStatement']);
    }

    /**
     * Populate annual account from accounting data.
     */
    public function populateFromAccounting(AnnualAccount $annualAccount): void
    {
        $periodStart = $annualAccount->period_start;
        $periodEnd = $annualAccount->period_end;

        // Get income statement data
        $incomeStatement = $this->reportService->getIncomeStatement($periodStart, $periodEnd);

        // Get balance sheet data
        $balanceSheet = $this->reportService->getBalanceSheet($periodEnd);

        // Update annual account with financial data
        $annualAccount->update([
            'revenue' => $incomeStatement['total_revenue'] ?? 0,
            'operating_profit' => $incomeStatement['operating_profit'] ?? 0,
            'profit_before_tax' => $incomeStatement['profit_before_tax'] ?? 0,
            'net_profit' => $incomeStatement['net_profit'] ?? 0,
            'total_assets' => $balanceSheet['total_assets'] ?? 0,
            'total_equity' => $balanceSheet['total_equity'] ?? 0,
            'total_liabilities' => $balanceSheet['total_liabilities'] ?? 0,
        ]);

        // Determine company size based on actual figures
        $size = $annualAccount->determineSize();
        $annualAccount->update(['company_size' => $size]);
    }

    /**
     * Initialize standard notes for the annual account.
     */
    public function initializeNotes(AnnualAccount $annualAccount, int $createdBy): void
    {
        $noteNumber = 1;

        foreach (AnnualAccountNote::NOTE_TYPES as $type => $info) {
            $content = $this->getDefaultNoteContent($type, $annualAccount);

            AnnualAccountNote::create([
                'annual_account_id' => $annualAccount->id,
                'note_number' => $noteNumber,
                'note_type' => $type,
                'title' => $info['title'],
                'content' => $content,
                'sort_order' => $info['order'],
                'is_required' => $info['required'],
                'is_visible' => $info['required'] || $this->shouldShowNote($type, $annualAccount),
                'created_by' => $createdBy,
            ]);

            $noteNumber++;
        }
    }

    /**
     * Get default content for a note type.
     */
    private function getDefaultNoteContent(string $type, AnnualAccount $annualAccount): string
    {
        return match ($type) {
            'accounting_principles' => AnnualAccountNote::getAccountingPrinciplesTemplate(),
            'employees' => AnnualAccountNote::getEmployeesTemplate(),
            'equity' => AnnualAccountNote::getEquityTemplate(),
            default => '',
        };
    }

    /**
     * Determine if a note should be shown by default.
     */
    private function shouldShowNote(string $type, AnnualAccount $annualAccount): bool
    {
        return match ($type) {
            'employees' => $annualAccount->average_employees > 0,
            'fixed_assets' => $annualAccount->company_size !== AnnualAccount::SIZE_SMALL,
            'debt' => $annualAccount->total_liabilities > 0,
            'tax' => $annualAccount->profit_before_tax != 0,
            'share_capital' => true,
            default => false,
        };
    }

    /**
     * Initialize cash flow statement for the annual account.
     */
    public function initializeCashFlowStatement(AnnualAccount $annualAccount, int $createdBy): CashFlowStatement
    {
        return CashFlowStatement::create([
            'annual_account_id' => $annualAccount->id,
            'profit_before_tax' => $annualAccount->profit_before_tax,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Update note content.
     */
    public function updateNote(AnnualAccountNote $note, array $data): AnnualAccountNote
    {
        $note->update($data);

        return $note->fresh();
    }

    /**
     * Reorder notes.
     */
    public function reorderNotes(AnnualAccount $annualAccount, array $noteOrder): void
    {
        foreach ($noteOrder as $index => $noteId) {
            AnnualAccountNote::where('id', $noteId)
                ->where('annual_account_id', $annualAccount->id)
                ->update([
                    'sort_order' => $index + 1,
                    'note_number' => $index + 1,
                ]);
        }
    }

    /**
     * Approve annual account.
     */
    public function approve(AnnualAccount $annualAccount, int $approvedBy): AnnualAccount
    {
        $annualAccount->approve($approvedBy);

        return $annualAccount->fresh();
    }

    /**
     * Validate annual account for submission.
     */
    public function validate(AnnualAccount $annualAccount): array
    {
        $errors = [];
        $warnings = [];

        // Check required notes
        $requiredNotes = $annualAccount->getRequiredNotes();
        $existingNotes = $annualAccount->notes()
            ->visible()
            ->pluck('note_type')
            ->toArray();

        foreach ($requiredNotes as $noteType) {
            if (! in_array($noteType, $existingNotes)) {
                $errors[] = 'Mangler påkrevd note: '.AnnualAccountNote::getDefaultTitle($noteType);
            }
        }

        // Check for empty required notes
        foreach ($annualAccount->notes()->visible()->get() as $note) {
            if ($note->is_required && empty(trim($note->content))) {
                $errors[] = "Note {$note->note_number} ({$note->title}) har ingen innhold.";
            }
        }

        // Check cash flow statement
        if ($annualAccount->requiresCashFlowStatement() && ! $annualAccount->cashFlowStatement) {
            $errors[] = 'Kontantstrømoppstilling er påkrevd for mellomstore og store foretak.';
        }

        // Check balance
        $balanceDiff = abs($annualAccount->total_assets - ($annualAccount->total_equity + $annualAccount->total_liabilities));
        if ($balanceDiff > 1) {
            $errors[] = 'Balansen stemmer ikke: Eiendeler != Egenkapital + Gjeld';
        }

        // Check approval
        if (! $annualAccount->board_approval_date) {
            $warnings[] = 'Styregodkjenningsdato er ikke registrert.';
        }

        // Check auditor (if required)
        if ($annualAccount->company_size !== AnnualAccount::SIZE_SMALL && empty($annualAccount->auditor_name)) {
            $warnings[] = 'Revisor er ikke registrert.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Get annual account summary for a year.
     */
    public function getSummary(int $year): ?array
    {
        $annualAccount = AnnualAccount::forYear($year)->first();

        if (! $annualAccount) {
            return null;
        }

        return [
            'annual_account' => $annualAccount,
            'notes_count' => $annualAccount->notes()->visible()->count(),
            'has_cash_flow' => $annualAccount->cashFlowStatement !== null,
            'validation' => $this->validate($annualAccount),
            'deadline' => $annualAccount->getDeadline(),
            'days_until_deadline' => $annualAccount->getDaysUntilDeadline(),
            'is_overdue' => $annualAccount->isOverdue(),
        ];
    }

    /**
     * Generate PDF for annual account.
     */
    public function generatePdf(AnnualAccount $annualAccount): string
    {
        // TODO: Implement PDF generation
        // This would use a PDF library like DomPDF or Snappy
        // to generate a properly formatted annual account document

        return '';
    }

    /**
     * Clone annual account from previous year as template.
     */
    public function cloneFromPreviousYear(int $targetYear, int $createdBy): ?AnnualAccount
    {
        $previousYear = AnnualAccount::forYear($targetYear - 1)->first();

        if (! $previousYear) {
            return null;
        }

        // Create new annual account
        $newAccount = $this->createAnnualAccount($targetYear, $createdBy);

        // Copy notes content from previous year
        foreach ($previousYear->notes as $oldNote) {
            $newNote = $newAccount->notes()->where('note_type', $oldNote->note_type)->first();
            if ($newNote) {
                $newNote->update([
                    'content' => $oldNote->content,
                    'structured_data' => $oldNote->structured_data,
                    'is_visible' => $oldNote->is_visible,
                ]);
            }
        }

        return $newAccount->fresh(['notes', 'cashFlowStatement']);
    }

    /**
     * Get comparison data between two years.
     */
    public function getYearComparison(int $currentYear, int $previousYear): array
    {
        $current = AnnualAccount::forYear($currentYear)->first();
        $previous = AnnualAccount::forYear($previousYear)->first();

        if (! $current || ! $previous) {
            return [];
        }

        $metrics = ['revenue', 'operating_profit', 'profit_before_tax', 'net_profit', 'total_assets', 'total_equity'];

        $comparison = [];
        foreach ($metrics as $metric) {
            $currentValue = $current->$metric ?? 0;
            $previousValue = $previous->$metric ?? 0;
            $change = $currentValue - $previousValue;
            $changePercent = $previousValue != 0 ? ($change / $previousValue) * 100 : 0;

            $comparison[$metric] = [
                'current' => $currentValue,
                'previous' => $previousValue,
                'change' => $change,
                'change_percent' => round($changePercent, 1),
            ];
        }

        return $comparison;
    }
}
