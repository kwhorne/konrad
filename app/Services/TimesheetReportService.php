<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Project;
use App\Models\TimesheetEntry;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TimesheetReportService
{
    /**
     * Get hours grouped by project for a company within a date range.
     *
     * @return Collection<int, array{project_id: int|null, project_name: string, project_number: string|null, total_hours: float, billable_hours: float, non_billable_hours: float, entry_count: int}>
     */
    public function getHoursByProject(Company $company, ?Carbon $fromDate = null, ?Carbon $toDate = null): Collection
    {
        $query = TimesheetEntry::withoutCompanyScope()
            ->select(
                'project_id',
                DB::raw('SUM(hours) as total_hours'),
                DB::raw('SUM(CASE WHEN is_billable = 1 THEN hours ELSE 0 END) as billable_hours'),
                DB::raw('SUM(CASE WHEN is_billable = 0 THEN hours ELSE 0 END) as non_billable_hours'),
                DB::raw('COUNT(*) as entry_count')
            )
            ->where('company_id', $company->id)
            ->whereNotNull('project_id')
            ->groupBy('project_id');

        if ($fromDate) {
            $query->where('entry_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('entry_date', '<=', $toDate);
        }

        $results = $query->get();

        // Hydrate with project data
        $projectIds = $results->pluck('project_id')->filter()->unique();
        $projects = Project::withoutCompanyScope()
            ->whereIn('id', $projectIds)
            ->get()
            ->keyBy('id');

        return $results->map(function ($row) use ($projects) {
            $project = $projects->get($row->project_id);

            return [
                'project_id' => $row->project_id,
                'project_name' => $project?->name ?? 'Ukjent prosjekt',
                'project_number' => $project?->project_number,
                'total_hours' => (float) $row->total_hours,
                'billable_hours' => (float) $row->billable_hours,
                'non_billable_hours' => (float) $row->non_billable_hours,
                'entry_count' => (int) $row->entry_count,
            ];
        })->sortByDesc('total_hours')->values();
    }

    /**
     * Get hours grouped by work order for a company within a date range.
     *
     * @return Collection<int, array{work_order_id: int, work_order_number: string, work_order_title: string, project_name: string|null, total_hours: float, billable_hours: float, entry_count: int}>
     */
    public function getHoursByWorkOrder(Company $company, ?Carbon $fromDate = null, ?Carbon $toDate = null): Collection
    {
        $query = TimesheetEntry::withoutCompanyScope()
            ->select(
                'work_order_id',
                DB::raw('SUM(hours) as total_hours'),
                DB::raw('SUM(CASE WHEN is_billable = 1 THEN hours ELSE 0 END) as billable_hours'),
                DB::raw('COUNT(*) as entry_count')
            )
            ->where('company_id', $company->id)
            ->whereNotNull('work_order_id')
            ->groupBy('work_order_id');

        if ($fromDate) {
            $query->where('entry_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('entry_date', '<=', $toDate);
        }

        $results = $query->get();

        // Hydrate with work order data
        $workOrderIds = $results->pluck('work_order_id')->filter()->unique();
        $workOrders = WorkOrder::withoutCompanyScope()
            ->with('project')
            ->whereIn('id', $workOrderIds)
            ->get()
            ->keyBy('id');

        return $results->map(function ($row) use ($workOrders) {
            $workOrder = $workOrders->get($row->work_order_id);

            return [
                'work_order_id' => $row->work_order_id,
                'work_order_number' => $workOrder?->work_order_number ?? 'Ukjent',
                'work_order_title' => $workOrder?->title ?? 'Ukjent arbeidsordre',
                'project_name' => $workOrder?->project?->name,
                'total_hours' => (float) $row->total_hours,
                'billable_hours' => (float) $row->billable_hours,
                'entry_count' => (int) $row->entry_count,
            ];
        })->sortByDesc('total_hours')->values();
    }

    /**
     * Get hours grouped by employee for a company within a date range.
     *
     * @return Collection<int, array{user_id: int, user_name: string, user_email: string, total_hours: float, billable_hours: float, non_billable_hours: float, approved_hours: float, pending_hours: float}>
     */
    public function getHoursByEmployee(Company $company, ?Carbon $fromDate = null, ?Carbon $toDate = null): Collection
    {
        $query = DB::table('timesheet_entries')
            ->join('timesheets', 'timesheet_entries.timesheet_id', '=', 'timesheets.id')
            ->select(
                'timesheets.user_id',
                DB::raw('SUM(timesheet_entries.hours) as total_hours'),
                DB::raw('SUM(CASE WHEN timesheet_entries.is_billable = 1 THEN timesheet_entries.hours ELSE 0 END) as billable_hours'),
                DB::raw('SUM(CASE WHEN timesheet_entries.is_billable = 0 THEN timesheet_entries.hours ELSE 0 END) as non_billable_hours'),
                DB::raw("SUM(CASE WHEN timesheets.status = 'approved' THEN timesheet_entries.hours ELSE 0 END) as approved_hours"),
                DB::raw("SUM(CASE WHEN timesheets.status IN ('draft', 'submitted') THEN timesheet_entries.hours ELSE 0 END) as pending_hours")
            )
            ->where('timesheet_entries.company_id', $company->id)
            ->groupBy('timesheets.user_id');

        if ($fromDate) {
            $query->where('timesheet_entries.entry_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('timesheet_entries.entry_date', '<=', $toDate);
        }

        $results = collect($query->get());

        // Hydrate with user data
        $userIds = $results->pluck('user_id')->unique();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        return $results->map(function ($row) use ($users) {
            $user = $users->get($row->user_id);

            return [
                'user_id' => $row->user_id,
                'user_name' => $user?->name ?? 'Ukjent bruker',
                'user_email' => $user?->email ?? '',
                'total_hours' => (float) $row->total_hours,
                'billable_hours' => (float) $row->billable_hours,
                'non_billable_hours' => (float) $row->non_billable_hours,
                'approved_hours' => (float) $row->approved_hours,
                'pending_hours' => (float) $row->pending_hours,
            ];
        })->sortByDesc('total_hours')->values();
    }

    /**
     * Get hours grouped by week for a company within a date range.
     *
     * @return Collection<int, array{week_start: string, week_number: int, year: int, total_hours: float, billable_hours: float, employee_count: int}>
     */
    public function getHoursByWeek(Company $company, ?Carbon $fromDate = null, ?Carbon $toDate = null): Collection
    {
        $query = DB::table('timesheet_entries')
            ->join('timesheets', 'timesheet_entries.timesheet_id', '=', 'timesheets.id')
            ->select(
                'timesheets.week_start',
                DB::raw('SUM(timesheet_entries.hours) as total_hours'),
                DB::raw('SUM(CASE WHEN timesheet_entries.is_billable = 1 THEN timesheet_entries.hours ELSE 0 END) as billable_hours'),
                DB::raw('COUNT(DISTINCT timesheets.user_id) as employee_count')
            )
            ->where('timesheet_entries.company_id', $company->id)
            ->groupBy('timesheets.week_start')
            ->orderBy('timesheets.week_start', 'desc');

        if ($fromDate) {
            $query->where('timesheet_entries.entry_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('timesheet_entries.entry_date', '<=', $toDate);
        }

        return collect($query->get())->map(function ($row) {
            $weekStart = Carbon::parse($row->week_start);

            return [
                'week_start' => $weekStart->format('Y-m-d'),
                'week_label' => 'Uke '.$weekStart->isoWeek().', '.$weekStart->year,
                'week_number' => $weekStart->isoWeek(),
                'year' => $weekStart->year,
                'total_hours' => (float) $row->total_hours,
                'billable_hours' => (float) $row->billable_hours,
                'employee_count' => (int) $row->employee_count,
            ];
        });
    }

    /**
     * Get detailed entries for a specific project.
     *
     * @return Collection<int, TimesheetEntry>
     */
    public function getEntriesForProject(Company $company, int $projectId, ?Carbon $fromDate = null, ?Carbon $toDate = null): Collection
    {
        $query = TimesheetEntry::withoutCompanyScope()
            ->with(['timesheet.user'])
            ->where('company_id', $company->id)
            ->where('project_id', $projectId)
            ->orderBy('entry_date', 'desc');

        if ($fromDate) {
            $query->where('entry_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('entry_date', '<=', $toDate);
        }

        return $query->get();
    }

    /**
     * Get summary statistics for a company within a date range.
     *
     * @return array{total_hours: float, billable_hours: float, non_billable_hours: float, approved_hours: float, pending_hours: float, employee_count: int, project_count: int, work_order_count: int}
     */
    public function getSummary(Company $company, ?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $query = DB::table('timesheet_entries')
            ->join('timesheets', 'timesheet_entries.timesheet_id', '=', 'timesheets.id')
            ->where('timesheet_entries.company_id', $company->id);

        if ($fromDate) {
            $query->where('timesheet_entries.entry_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('timesheet_entries.entry_date', '<=', $toDate);
        }

        $stats = $query->selectRaw('
            SUM(timesheet_entries.hours) as total_hours,
            SUM(CASE WHEN timesheet_entries.is_billable = 1 THEN timesheet_entries.hours ELSE 0 END) as billable_hours,
            SUM(CASE WHEN timesheet_entries.is_billable = 0 THEN timesheet_entries.hours ELSE 0 END) as non_billable_hours,
            SUM(CASE WHEN timesheets.status = \'approved\' THEN timesheet_entries.hours ELSE 0 END) as approved_hours,
            SUM(CASE WHEN timesheets.status IN (\'draft\', \'submitted\') THEN timesheet_entries.hours ELSE 0 END) as pending_hours,
            COUNT(DISTINCT timesheets.user_id) as employee_count,
            COUNT(DISTINCT timesheet_entries.project_id) as project_count,
            COUNT(DISTINCT timesheet_entries.work_order_id) as work_order_count
        ')->first();

        return [
            'total_hours' => (float) ($stats->total_hours ?? 0),
            'billable_hours' => (float) ($stats->billable_hours ?? 0),
            'non_billable_hours' => (float) ($stats->non_billable_hours ?? 0),
            'approved_hours' => (float) ($stats->approved_hours ?? 0),
            'pending_hours' => (float) ($stats->pending_hours ?? 0),
            'employee_count' => (int) ($stats->employee_count ?? 0),
            'project_count' => (int) ($stats->project_count ?? 0),
            'work_order_count' => (int) ($stats->work_order_count ?? 0),
        ];
    }

    /**
     * Get hours by employee for a specific project.
     *
     * @return Collection<int, array{user_id: int, user_name: string, total_hours: float, billable_hours: float}>
     */
    public function getProjectHoursByEmployee(Company $company, int $projectId, ?Carbon $fromDate = null, ?Carbon $toDate = null): Collection
    {
        $query = DB::table('timesheet_entries')
            ->join('timesheets', 'timesheet_entries.timesheet_id', '=', 'timesheets.id')
            ->select(
                'timesheets.user_id',
                DB::raw('SUM(timesheet_entries.hours) as total_hours'),
                DB::raw('SUM(CASE WHEN timesheet_entries.is_billable = 1 THEN timesheet_entries.hours ELSE 0 END) as billable_hours')
            )
            ->where('timesheet_entries.company_id', $company->id)
            ->where('timesheet_entries.project_id', $projectId)
            ->groupBy('timesheets.user_id');

        if ($fromDate) {
            $query->where('timesheet_entries.entry_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('timesheet_entries.entry_date', '<=', $toDate);
        }

        $results = collect($query->get());

        // Hydrate with user data
        $userIds = $results->pluck('user_id')->unique();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        return $results->map(function ($row) use ($users) {
            $user = $users->get($row->user_id);

            return [
                'user_id' => $row->user_id,
                'user_name' => $user?->name ?? 'Ukjent bruker',
                'total_hours' => (float) $row->total_hours,
                'billable_hours' => (float) $row->billable_hours,
            ];
        })->sortByDesc('total_hours')->values();
    }
}
