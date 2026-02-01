<?php

namespace App\Livewire;

use App\Models\Timesheet;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class TimesheetHistory extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $filterStatus = '';

    public string $filterYear = '';

    public ?Timesheet $selectedTimesheet = null;

    public bool $showDetailModal = false;

    protected $queryString = [
        'filterStatus' => ['except' => ''],
        'filterYear' => ['except' => ''],
    ];

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterYear(): void
    {
        $this->resetPage();
    }

    public function viewDetails(int $id): void
    {
        $this->selectedTimesheet = Timesheet::with(['entries.project', 'entries.workOrder', 'approvedByUser', 'rejectedByUser'])
            ->findOrFail($id);

        $this->authorize('view', $this->selectedTimesheet);

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedTimesheet = null;
    }

    public function getAvailableYearsProperty(): array
    {
        return Timesheet::forUser(auth()->user())
            ->pluck('week_start')
            ->map(fn ($date) => $date->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();
    }

    public function render()
    {
        $query = Timesheet::forUser(auth()->user())
            ->with(['entries'])
            ->ordered();

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterYear) {
            $query->whereYear('week_start', $this->filterYear);
        }

        return view('livewire.timesheet-history', [
            'timesheets' => $query->paginate(15),
        ]);
    }
}
