<?php

namespace App\Livewire;

use App\Models\Timesheet;
use App\Models\User;
use App\Services\TimesheetService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class TimesheetApprovalManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $filterStatus = 'submitted';

    public string $filterUser = '';

    public ?Timesheet $selectedTimesheet = null;

    public bool $showDetailModal = false;

    public bool $showRejectModal = false;

    public string $rejectionReason = '';

    protected $queryString = [
        'filterStatus' => ['except' => 'submitted'],
        'filterUser' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->authorize('viewAwaitingApproval', Timesheet::class);
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterUser(): void
    {
        $this->resetPage();
    }

    public function viewDetails(int $id): void
    {
        $this->selectedTimesheet = Timesheet::with(['user', 'entries.project', 'entries.workOrder', 'approvedByUser', 'rejectedByUser'])
            ->findOrFail($id);

        $this->authorize('view', $this->selectedTimesheet);

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedTimesheet = null;
    }

    public function approve(int $id): void
    {
        $timesheet = Timesheet::findOrFail($id);
        $this->authorize('approve', $timesheet);

        $service = app(TimesheetService::class);
        if ($service->approve($timesheet, auth()->user())) {
            session()->flash('success', 'Timeseddelen ble godkjent.');
        } else {
            session()->flash('error', 'Kunne ikke godkjenne timeseddelen.');
        }

        $this->closeDetailModal();
    }

    public function openRejectModal(int $id): void
    {
        $this->selectedTimesheet = Timesheet::findOrFail($id);
        $this->authorize('reject', $this->selectedTimesheet);
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->rejectionReason = '';
    }

    public function reject(): void
    {
        if (! $this->selectedTimesheet) {
            return;
        }

        $this->authorize('reject', $this->selectedTimesheet);

        $this->validate([
            'rejectionReason' => 'required|string|min:5',
        ], [
            'rejectionReason.required' => 'Du ma oppgi en grunn for avvisningen.',
            'rejectionReason.min' => 'Grunnen ma vaere minst 5 tegn.',
        ]);

        $service = app(TimesheetService::class);
        if ($service->reject($this->selectedTimesheet, auth()->user(), $this->rejectionReason)) {
            session()->flash('success', 'Timeseddelen ble avvist.');
        } else {
            session()->flash('error', 'Kunne ikke avvise timeseddelen.');
        }

        $this->closeRejectModal();
        $this->closeDetailModal();
    }

    public function render()
    {
        $company = auth()->user()->currentCompany;

        $query = Timesheet::where('company_id', $company->id)
            ->with(['user', 'entries'])
            ->ordered();

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterUser) {
            $query->where('user_id', $this->filterUser);
        }

        $users = User::whereHas('companies', function ($q) use ($company) {
            $q->where('company_id', $company->id);
        })->orderBy('name')->get();

        return view('livewire.timesheet-approval-manager', [
            'timesheets' => $query->paginate(15),
            'users' => $users,
        ]);
    }
}
