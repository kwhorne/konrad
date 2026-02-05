<?php

namespace App\Livewire;

use App\Models\AltinnSubmission;
use App\Models\AnnualAccount;
use App\Models\ShareholderReport;
use App\Models\TaxReturn;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class AltinnDashboard extends Component
{
    use AuthorizesRequests;

    public $selectedYear;

    public $showSubmissionModal = false;

    public $viewingSubmissionId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', AltinnSubmission::class);
        $this->selectedYear = now()->year;
    }

    public function updatedSelectedYear(): void
    {
        // Refresh data when year changes
    }

    public function viewSubmission($id): void
    {
        $submission = AltinnSubmission::findOrFail($id);
        $this->authorize('view', $submission);
        $this->viewingSubmissionId = $id;
        $this->showSubmissionModal = true;
    }

    public function closeSubmissionModal(): void
    {
        $this->showSubmissionModal = false;
        $this->viewingSubmissionId = null;
    }

    public function getDeadlines(): array
    {
        $year = $this->selectedYear;
        $deadlines = [];

        // Aksjonærregisteroppgaven (RF-1086) - 31. januar
        $shareholderReport = ShareholderReport::where('year', $year - 1)->first();
        $deadlines[] = [
            'type' => 'aksjonaerregister',
            'name' => 'Aksjonærregisteroppgaven',
            'code' => 'RF-1086',
            'fiscal_year' => $year - 1,
            'deadline' => Carbon::create($year, 1, 31),
            'recipient' => 'Skatteetaten',
            'status' => $this->getDeadlineStatus($shareholderReport),
            'entity' => $shareholderReport,
            'route' => 'shareholders.reports',
        ];

        // Skattemelding (RF-1028) - 31. mai
        $taxReturn = TaxReturn::where('fiscal_year', $year - 1)->first();
        $deadlines[] = [
            'type' => 'skattemelding',
            'name' => 'Skattemelding',
            'code' => 'RF-1028',
            'fiscal_year' => $year - 1,
            'deadline' => Carbon::create($year, 5, 31),
            'recipient' => 'Skatteetaten',
            'status' => $this->getDeadlineStatus($taxReturn),
            'entity' => $taxReturn,
            'route' => 'tax.returns',
        ];

        // Årsregnskap - 31. juli
        $annualAccount = AnnualAccount::where('fiscal_year', $year - 1)->first();
        $deadlines[] = [
            'type' => 'arsregnskap',
            'name' => 'Årsregnskap',
            'code' => 'XBRL',
            'fiscal_year' => $year - 1,
            'deadline' => Carbon::create($year, 7, 31),
            'recipient' => 'Regnskapsregisteret',
            'status' => $this->getDeadlineStatus($annualAccount),
            'entity' => $annualAccount,
            'route' => 'annual-accounts.index',
        ];

        return $deadlines;
    }

    private function getDeadlineStatus($entity): array
    {
        if (! $entity) {
            return [
                'code' => 'not_started',
                'label' => 'Ikke startet',
                'color' => 'outline',
            ];
        }

        $status = $entity->status ?? 'draft';

        return match ($status) {
            'draft' => [
                'code' => 'draft',
                'label' => 'Under arbeid',
                'color' => 'warning',
            ],
            'ready', 'approved' => [
                'code' => 'ready',
                'label' => 'Klar for innsending',
                'color' => 'info',
            ],
            'submitted' => [
                'code' => 'submitted',
                'label' => 'Sendt inn',
                'color' => 'primary',
            ],
            'accepted' => [
                'code' => 'accepted',
                'label' => 'Akseptert',
                'color' => 'success',
            ],
            'rejected' => [
                'code' => 'rejected',
                'label' => 'Avvist',
                'color' => 'danger',
            ],
            default => [
                'code' => $status,
                'label' => ucfirst($status),
                'color' => 'outline',
            ],
        };
    }

    public function getSubmissionHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return AltinnSubmission::with('submittable')
            ->where(function ($query) {
                $query->where('year', $this->selectedYear - 1)
                    ->orWhere('year', $this->selectedYear);
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    public function getStatistics(): array
    {
        $year = $this->selectedYear;

        $totalSubmissions = AltinnSubmission::whereIn('year', [$year - 1, $year])->count();
        $acceptedSubmissions = AltinnSubmission::whereIn('year', [$year - 1, $year])
            ->where('status', 'accepted')
            ->count();
        $pendingSubmissions = AltinnSubmission::whereIn('year', [$year - 1, $year])
            ->whereIn('status', ['draft', 'validating', 'submitted'])
            ->count();
        $rejectedSubmissions = AltinnSubmission::whereIn('year', [$year - 1, $year])
            ->where('status', 'rejected')
            ->count();

        return [
            'total' => $totalSubmissions,
            'accepted' => $acceptedSubmissions,
            'pending' => $pendingSubmissions,
            'rejected' => $rejectedSubmissions,
        ];
    }

    public function getUpcomingDeadlines(): array
    {
        $deadlines = $this->getDeadlines();
        $upcoming = [];

        foreach ($deadlines as $deadline) {
            if ($deadline['status']['code'] !== 'accepted' && $deadline['deadline']->isFuture()) {
                $daysUntil = (int) now()->diffInDays($deadline['deadline'], false);
                $deadline['days_until'] = $daysUntil;
                $deadline['urgency'] = $this->getUrgencyLevel($daysUntil);
                $upcoming[] = $deadline;
            }
        }

        // Sort by deadline
        usort($upcoming, fn ($a, $b) => $a['deadline']->timestamp - $b['deadline']->timestamp);

        return $upcoming;
    }

    public function getOverdueDeadlines(): array
    {
        $deadlines = $this->getDeadlines();
        $overdue = [];

        foreach ($deadlines as $deadline) {
            if ($deadline['status']['code'] !== 'accepted' && $deadline['deadline']->isPast()) {
                $daysPast = (int) now()->diffInDays($deadline['deadline']);
                $deadline['days_past'] = $daysPast;
                $overdue[] = $deadline;
            }
        }

        return $overdue;
    }

    private function getUrgencyLevel(int $daysUntil): string
    {
        if ($daysUntil <= 7) {
            return 'critical';
        }
        if ($daysUntil <= 14) {
            return 'high';
        }
        if ($daysUntil <= 30) {
            return 'medium';
        }

        return 'low';
    }

    public function render()
    {
        $deadlines = $this->getDeadlines();
        $upcomingDeadlines = $this->getUpcomingDeadlines();
        $overdueDeadlines = $this->getOverdueDeadlines();
        $submissions = $this->getSubmissionHistory();
        $statistics = $this->getStatistics();

        $viewingSubmission = null;
        if ($this->viewingSubmissionId) {
            $viewingSubmission = AltinnSubmission::with('submittable')->find($this->viewingSubmissionId);
        }

        $years = range(now()->year + 1, now()->year - 3);

        return view('livewire.altinn-dashboard', [
            'deadlines' => $deadlines,
            'upcomingDeadlines' => $upcomingDeadlines,
            'overdueDeadlines' => $overdueDeadlines,
            'submissions' => $submissions,
            'statistics' => $statistics,
            'viewingSubmission' => $viewingSubmission,
            'years' => $years,
        ]);
    }
}
