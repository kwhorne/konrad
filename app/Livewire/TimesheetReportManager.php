<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\TimesheetReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class TimesheetReportManager extends Component
{
    use AuthorizesRequests;

    public string $reportType = 'project';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public ?int $selectedProjectId = null;

    protected $queryString = [
        'reportType' => ['except' => 'project'],
        'fromDate' => ['except' => ''],
        'toDate' => ['except' => ''],
        'selectedProjectId' => ['except' => ''],
    ];

    public function mount(): void
    {
        // Default to current month
        $this->fromDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->toDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function setReportType(string $type): void
    {
        $this->reportType = $type;
        $this->selectedProjectId = null;
    }

    public function setQuickPeriod(string $period): void
    {
        match ($period) {
            'this_week' => $this->setPeriod(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()),
            'last_week' => $this->setPeriod(Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()),
            'this_month' => $this->setPeriod(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()),
            'last_month' => $this->setPeriod(Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()),
            'this_quarter' => $this->setPeriod(Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()),
            'this_year' => $this->setPeriod(Carbon::now()->startOfYear(), Carbon::now()->endOfYear()),
            'all_time' => $this->setPeriod(null, null),
            default => null,
        };
    }

    protected function setPeriod(?Carbon $from, ?Carbon $to): void
    {
        $this->fromDate = $from?->format('Y-m-d');
        $this->toDate = $to?->format('Y-m-d');
    }

    public function viewProjectDetails(int $projectId): void
    {
        $this->selectedProjectId = $projectId;
        $this->reportType = 'project_detail';
    }

    public function backToProjectList(): void
    {
        $this->selectedProjectId = null;
        $this->reportType = 'project';
    }

    public function getReportDataProperty(): array
    {
        $service = app(TimesheetReportService::class);
        $company = auth()->user()->currentCompany;

        $fromDate = $this->fromDate ? Carbon::parse($this->fromDate) : null;
        $toDate = $this->toDate ? Carbon::parse($this->toDate) : null;

        return match ($this->reportType) {
            'project' => [
                'projects' => $service->getHoursByProject($company, $fromDate, $toDate),
                'summary' => $service->getSummary($company, $fromDate, $toDate),
            ],
            'project_detail' => [
                'project' => $this->selectedProjectId ? Project::find($this->selectedProjectId) : null,
                'employees' => $this->selectedProjectId
                    ? $service->getProjectHoursByEmployee($company, $this->selectedProjectId, $fromDate, $toDate)
                    : collect(),
                'entries' => $this->selectedProjectId
                    ? $service->getEntriesForProject($company, $this->selectedProjectId, $fromDate, $toDate)
                    : collect(),
            ],
            'work_order' => [
                'workOrders' => $service->getHoursByWorkOrder($company, $fromDate, $toDate),
                'summary' => $service->getSummary($company, $fromDate, $toDate),
            ],
            'employee' => [
                'employees' => $service->getHoursByEmployee($company, $fromDate, $toDate),
                'summary' => $service->getSummary($company, $fromDate, $toDate),
            ],
            'weekly' => [
                'weeks' => $service->getHoursByWeek($company, $fromDate, $toDate),
                'summary' => $service->getSummary($company, $fromDate, $toDate),
            ],
            default => [
                'summary' => $service->getSummary($company, $fromDate, $toDate),
            ],
        };
    }

    public function getPeriodLabelProperty(): string
    {
        if (! $this->fromDate && ! $this->toDate) {
            return 'Hele perioden';
        }

        $from = $this->fromDate ? Carbon::parse($this->fromDate)->format('d.m.Y') : 'Start';
        $to = $this->toDate ? Carbon::parse($this->toDate)->format('d.m.Y') : 'Nå';

        return "{$from} - {$to}";
    }

    public function render()
    {
        return view('livewire.timesheet-report-manager');
    }
}
