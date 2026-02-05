<?php

namespace App\Livewire;

use App\Models\ShareholderReport;
use App\Services\ShareholderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ShareholderReportManager extends Component
{
    use AuthorizesRequests;

    public $showCreateModal = false;

    public $showDetailModal = false;

    public $viewingReportId = null;

    public $createYear = '';

    protected function rules(): array
    {
        $companyId = auth()->user()->current_company_id;

        return [
            'createYear' => [
                'required', 'integer', 'min:2000', 'max:2100',
                Rule::unique('shareholder_reports', 'year')->where('company_id', $companyId),
            ],
        ];
    }

    protected $messages = [
        'createYear.required' => 'År er påkrevd.',
        'createYear.unique' => 'Det finnes allerede en rapport for dette året.',
    ];

    public function mount(): void
    {
        $this->createYear = now()->subYear()->year;
    }

    public function openCreateModal(): void
    {
        $this->createYear = now()->subYear()->year;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function createReport(): void
    {
        $this->authorize('create', ShareholderReport::class);

        $this->validate();

        $shareholderService = app(ShareholderService::class);
        $report = $shareholderService->createShareholderReport($this->createYear, auth()->id());

        session()->flash('success', "Aksjonæroppgave for {$this->createYear} ble opprettet.");
        $this->closeCreateModal();

        $this->viewReport($report->id);
    }

    public function viewReport($id): void
    {
        $this->viewingReportId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->viewingReportId = null;
    }

    public function regenerateSnapshot($id): void
    {
        $report = ShareholderReport::findOrFail($id);
        $this->authorize('update', $report);

        if (! $report->canBeEdited()) {
            session()->flash('error', 'Kan ikke regenerere snapshot for innsendt rapport.');

            return;
        }

        $report->generateAllSummaries();
        session()->flash('success', 'Snapshot ble regenerert.');
    }

    public function markAsReady($id): void
    {
        $report = ShareholderReport::findOrFail($id);
        $this->authorize('markAsReady', $report);

        if (! $report->isDraft()) {
            session()->flash('error', 'Kun utkast kan markeres som klar.');

            return;
        }

        $report->markAsReady();
        session()->flash('success', 'Rapporten er nå klar for innsending.');
    }

    public function markAsDraft($id): void
    {
        $report = ShareholderReport::findOrFail($id);
        $this->authorize('markAsDraft', $report);

        if ($report->isSubmitted()) {
            session()->flash('error', 'Kan ikke endre innsendt rapport.');

            return;
        }

        $report->markAsDraft();
        session()->flash('success', 'Rapporten ble satt tilbake til utkast.');
    }

    public function submitToAltinn($id): void
    {
        $report = ShareholderReport::findOrFail($id);
        $this->authorize('submitToAltinn', $report);

        if (! $report->canBeSubmitted()) {
            session()->flash('error', 'Rapporten er ikke klar for innsending.');

            return;
        }

        // TODO: Implement actual Altinn submission
        session()->flash('info', 'Altinn-innsending er ikke implementert ennå.');
    }

    public function delete($id): void
    {
        $report = ShareholderReport::findOrFail($id);
        $this->authorize('delete', $report);

        if ($report->isSubmitted()) {
            session()->flash('error', 'Kan ikke slette innsendt rapport.');

            return;
        }

        $report->delete();
        session()->flash('success', 'Rapporten ble slettet.');
    }

    public function render()
    {
        $reports = ShareholderReport::with(['creator', 'altinnSubmission'])
            ->ordered()
            ->get();

        $viewingReport = null;
        if ($this->viewingReportId) {
            $viewingReport = ShareholderReport::with(['creator', 'altinnSubmission'])
                ->findOrFail($this->viewingReportId);
        }

        // Find years that don't have a report yet
        $existingYears = ShareholderReport::pluck('year')->toArray();
        $availableYears = collect(range(now()->year, now()->year - 5))
            ->filter(fn ($year) => ! in_array($year, $existingYears))
            ->values();

        return view('livewire.shareholder-report-manager', [
            'reports' => $reports,
            'viewingReport' => $viewingReport,
            'availableYears' => $availableYears,
        ]);
    }
}
