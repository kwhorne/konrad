<?php

namespace App\Services;

use App\Exceptions\TimesheetValidationException;
use App\Models\Company;
use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TimesheetService
{
    /**
     * Maximum hours allowed per day across all entries.
     */
    public const MAX_HOURS_PER_DAY = 24;

    /**
     * Minimum hours per entry.
     */
    public const MIN_HOURS_PER_ENTRY = 0.5;

    /**
     * Maximum hours per entry.
     */
    public const MAX_HOURS_PER_ENTRY = 24;

    /**
     * Get or create a timesheet for a user for a specific week.
     */
    public function getOrCreateTimesheet(User $user, Carbon $date): Timesheet
    {
        $weekStart = $date->copy()->startOfWeek();

        return Timesheet::firstOrCreate(
            [
                'company_id' => $user->current_company_id,
                'user_id' => $user->id,
                'week_start' => $weekStart,
            ],
            [
                'week_end' => $weekStart->copy()->endOfWeek(),
                'status' => Timesheet::STATUS_DRAFT,
                'total_hours' => 0,
            ]
        );
    }

    /**
     * Save a timesheet entry with full validation.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws TimesheetValidationException
     */
    public function saveEntry(Timesheet $timesheet, array $data, ?int $entryId = null): TimesheetEntry
    {
        // Validate timesheet is editable
        if (! $timesheet->is_editable) {
            throw TimesheetValidationException::timesheetNotEditable();
        }

        $hours = (float) $data['hours'];
        $entryDate = $data['entry_date'];
        $projectId = $data['project_id'] ?? null;
        $workOrderId = $data['work_order_id'] ?? null;
        $description = $data['description'] ?? null;

        // Validate hours per entry
        $this->validateHours($hours);

        // Validate entry date is within timesheet week
        $this->validateEntryDate($timesheet, $entryDate);

        // Validate daily total won't exceed limit
        $this->validateDailyTotal($timesheet, $entryDate, $hours, $entryId);

        // Validate target (project, work order, or description)
        $this->validateTarget($projectId, $workOrderId, $description);

        $entryData = [
            'company_id' => $timesheet->company_id,
            'timesheet_id' => $timesheet->id,
            'entry_date' => $entryDate,
            'hours' => $hours,
            'project_id' => $projectId,
            'work_order_id' => $workOrderId,
            'description' => $description,
            'is_billable' => $data['is_billable'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ];

        if ($entryId) {
            $entry = TimesheetEntry::findOrFail($entryId);
            $entry->update($entryData);

            return $entry->fresh();
        }

        return TimesheetEntry::create($entryData);
    }

    /**
     * Validate hours are within acceptable range.
     *
     * @throws TimesheetValidationException
     */
    protected function validateHours(float $hours): void
    {
        if ($hours < self::MIN_HOURS_PER_ENTRY || $hours > self::MAX_HOURS_PER_ENTRY) {
            throw TimesheetValidationException::invalidHours($hours);
        }
    }

    /**
     * Validate entry date is within timesheet week.
     *
     * @throws TimesheetValidationException
     */
    protected function validateEntryDate(Timesheet $timesheet, string $entryDate): void
    {
        $date = Carbon::parse($entryDate);
        $weekStart = $timesheet->week_start;
        $weekEnd = $timesheet->week_end;

        if ($date->lt($weekStart) || $date->gt($weekEnd)) {
            throw TimesheetValidationException::dateOutsideWeek(
                $date->format('Y-m-d'),
                $weekStart->format('Y-m-d'),
                $weekEnd->format('Y-m-d')
            );
        }
    }

    /**
     * Validate daily total won't exceed maximum.
     *
     * @throws TimesheetValidationException
     */
    protected function validateDailyTotal(Timesheet $timesheet, string $entryDate, float $newHours, ?int $excludeEntryId = null): void
    {
        // Get current total for this day (excluding the entry being updated if applicable)
        // Use withoutCompanyScope to avoid issues with multi-tenant filtering during validation
        // Use Carbon for date comparison to ensure consistent behavior across SQLite and MySQL
        $query = TimesheetEntry::withoutCompanyScope()
            ->where('timesheet_id', $timesheet->id)
            ->where('entry_date', Carbon::parse($entryDate));

        if ($excludeEntryId) {
            $query->where('id', '!=', $excludeEntryId);
        }

        $currentTotal = (float) $query->sum('hours');

        if (($currentTotal + $newHours) > self::MAX_HOURS_PER_DAY) {
            throw TimesheetValidationException::dailyLimitExceeded(
                $entryDate,
                $currentTotal,
                $newHours,
                self::MAX_HOURS_PER_DAY
            );
        }
    }

    /**
     * Validate that entry has a valid target (project, work order, or description).
     *
     * @throws TimesheetValidationException
     */
    protected function validateTarget(?int $projectId, ?int $workOrderId, ?string $description): void
    {
        $hasTarget = $projectId !== null
            || $workOrderId !== null
            || ($description !== null && trim($description) !== '');

        if (! $hasTarget) {
            throw TimesheetValidationException::missingTarget();
        }
    }

    /**
     * Delete a timesheet entry.
     */
    public function deleteEntry(TimesheetEntry $entry): bool
    {
        if (! $entry->timesheet->is_editable) {
            return false;
        }

        $entry->delete();

        return true;
    }

    /**
     * Submit a timesheet for approval.
     */
    public function submit(Timesheet $timesheet, User $user): bool
    {
        return $timesheet->submit($user);
    }

    /**
     * Approve a timesheet.
     */
    public function approve(Timesheet $timesheet, User $approver): bool
    {
        return $timesheet->approve($approver);
    }

    /**
     * Reject a timesheet.
     */
    public function reject(Timesheet $timesheet, User $rejector, string $reason): bool
    {
        return $timesheet->reject($rejector, $reason);
    }

    /**
     * Reopen a rejected timesheet.
     */
    public function reopen(Timesheet $timesheet): bool
    {
        return $timesheet->reopen();
    }

    /**
     * Check if a user can approve timesheets for a company.
     */
    public function canApproveTimesheets(User $user, Company $company): bool
    {
        // Admin/owner of company can approve
        if ($user->isOwnerOf($company)) {
            return true;
        }

        // Users who can manage the company can approve
        if ($user->canManage($company)) {
            return true;
        }

        return false;
    }

    /**
     * Get timesheets awaiting approval for a company.
     *
     * @return Collection<int, Timesheet>
     */
    public function getAwaitingApproval(Company $company): Collection
    {
        return Timesheet::where('company_id', $company->id)
            ->submitted()
            ->with(['user', 'entries'])
            ->ordered()
            ->get();
    }

    /**
     * Get timesheet history for a user.
     *
     * @return Collection<int, Timesheet>
     */
    public function getHistory(User $user, ?int $limit = null): Collection
    {
        $query = Timesheet::forUser($user)
            ->with(['entries', 'approvedByUser', 'rejectedByUser'])
            ->ordered();

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get week dates helper.
     *
     * @return array{start: Carbon, end: Carbon}
     */
    public function getWeekDates(Carbon $date): array
    {
        return Timesheet::getWeekDates($date);
    }

    /**
     * Get summary of hours for a timesheet grouped by day.
     *
     * @return array<string, float>
     */
    public function getDailySummary(Timesheet $timesheet): array
    {
        $summary = [];
        $current = $timesheet->week_start->copy();

        while ($current <= $timesheet->week_end) {
            $dateKey = $current->format('Y-m-d');
            $summary[$dateKey] = $timesheet->entries
                ->where('entry_date', $current->format('Y-m-d'))
                ->sum('hours');
            $current->addDay();
        }

        return $summary;
    }

    /**
     * Get summary of hours for a timesheet grouped by project/work order.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getTargetSummary(Timesheet $timesheet): Collection
    {
        return $timesheet->entries
            ->groupBy(function ($entry) {
                if ($entry->work_order_id) {
                    return 'wo_'.$entry->work_order_id;
                }
                if ($entry->project_id) {
                    return 'proj_'.$entry->project_id;
                }

                return 'other';
            })
            ->map(function ($entries) {
                $first = $entries->first();

                return [
                    'type' => $first->target_type,
                    'project_id' => $first->project_id,
                    'work_order_id' => $first->work_order_id,
                    'label' => $first->target_label,
                    'total_hours' => $entries->sum('hours'),
                    'entries' => $entries,
                ];
            });
    }

    /**
     * Get timesheets for a specific period.
     *
     * @return Collection<int, Timesheet>
     */
    public function getTimesheetsForPeriod(User $user, Carbon $from, Carbon $to): Collection
    {
        return Timesheet::forUser($user)
            ->where('week_start', '>=', $from->startOfWeek())
            ->where('week_end', '<=', $to->endOfWeek())
            ->with(['entries'])
            ->ordered()
            ->get();
    }

    /**
     * Calculate billable vs non-billable hours for a timesheet.
     *
     * @return array{billable: float, non_billable: float, total: float}
     */
    public function getHoursSummary(Timesheet $timesheet): array
    {
        $billable = $timesheet->entries->where('is_billable', true)->sum('hours');
        $nonBillable = $timesheet->entries->where('is_billable', false)->sum('hours');

        return [
            'billable' => (float) $billable,
            'non_billable' => (float) $nonBillable,
            'total' => (float) ($billable + $nonBillable),
        ];
    }
}
