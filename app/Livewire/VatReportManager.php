<?php

namespace App\Livewire;

use App\Models\VatReport;
use App\Models\VatReportAttachment;
use App\Models\VatReportLine;
use App\Services\VatReportService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class VatReportManager extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $filterYear = '';

    public string $filterStatus = '';

    // Create modal
    public bool $showCreateModal = false;

    public int $createYear;

    public int $createPeriod = 0;

    // Edit modal
    public bool $showEditModal = false;

    public ?VatReport $editingReport = null;

    // Line edit modal
    public bool $showLineModal = false;

    public ?VatReportLine $editingLine = null;

    public string $lineBaseAmount = '';

    public string $lineVatAmount = '';

    public ?string $lineNote = null;

    // Note modal
    public bool $showNoteModal = false;

    public ?string $reportNote = null;

    // Attachment
    public $attachment;

    // Submit modal
    public bool $showSubmitModal = false;

    public ?string $altinnReference = null;

    public ?int $initialReportId = null;

    public function mount(?int $initialReportId = null): void
    {
        $this->createYear = now()->year;
        $this->filterYear = (string) now()->year;
        $this->initialReportId = $initialReportId;

        if ($initialReportId) {
            $report = VatReport::find($initialReportId);
            if ($report) {
                $this->openEditModal($report);
            }
        }
    }

    #[Computed]
    public function reports()
    {
        $query = VatReport::query()
            ->with(['creator'])
            ->withCount('lines');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('year', 'like', "%{$this->search}%")
                    ->orWhere('altinn_reference', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterYear) {
            $query->where('year', $this->filterYear);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return $query->ordered()->paginate(10);
    }

    #[Computed]
    public function availableYears(): array
    {
        $years = [];
        $currentYear = now()->year;
        for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
            $years[] = $i;
        }

        return $years;
    }

    #[Computed]
    public function availablePeriods(): Collection
    {
        $service = app(VatReportService::class);

        return $service->getAvailablePeriods($this->createYear);
    }

    public function openCreateModal(): void
    {
        $this->createYear = now()->year;
        $this->createPeriod = 0;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->validate([
            'createYear' => 'required|integer|min:2020|max:2099',
            'createPeriod' => 'required|integer|min:1|max:6',
        ]);

        $service = app(VatReportService::class);

        if ($service->reportExists($this->createYear, $this->createPeriod)) {
            $this->addError('createPeriod', 'Det finnes allerede en MVA-melding for denne perioden.');

            return;
        }

        $report = $service->createReport($this->createYear, $this->createPeriod);

        $this->closeCreateModal();
        session()->flash('success', 'MVA-melding opprettet.');

        $this->redirectRoute('vat-reports.show', $report);
    }

    public function openEditModal(VatReport $report): void
    {
        $this->editingReport = $report->load(['lines.vatCode', 'attachments']);
        $this->reportNote = $report->note;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingReport = null;
        $this->resetValidation();
    }

    public function calculate(): void
    {
        if (! $this->editingReport) {
            return;
        }

        $service = app(VatReportService::class);
        $this->editingReport = $service->calculateReport($this->editingReport);

        session()->flash('success', 'MVA-melding beregnet.');
    }

    public function openLineModal(VatReportLine $line): void
    {
        $this->editingLine = $line;
        $this->lineBaseAmount = (string) $line->base_amount;
        $this->lineVatAmount = (string) $line->vat_amount;
        $this->lineNote = $line->note;
        $this->showLineModal = true;
    }

    public function closeLineModal(): void
    {
        $this->showLineModal = false;
        $this->editingLine = null;
        $this->lineBaseAmount = '';
        $this->lineVatAmount = '';
        $this->lineNote = null;
        $this->resetValidation();
    }

    public function saveLine(): void
    {
        $this->validate([
            'lineBaseAmount' => 'required|numeric',
            'lineVatAmount' => 'required|numeric',
        ]);

        if (! $this->editingLine) {
            return;
        }

        $service = app(VatReportService::class);
        $service->updateLine(
            $this->editingLine,
            (float) $this->lineBaseAmount,
            (float) $this->lineVatAmount,
            $this->lineNote
        );

        // Refresh the editing report
        if ($this->editingReport) {
            $this->editingReport = $this->editingReport->fresh(['lines.vatCode', 'attachments']);
        }

        $this->closeLineModal();
        session()->flash('success', 'Linje oppdatert.');
    }

    public function openNoteModal(): void
    {
        if ($this->editingReport) {
            $this->reportNote = $this->editingReport->note;
        }
        $this->showNoteModal = true;
    }

    public function closeNoteModal(): void
    {
        $this->showNoteModal = false;
    }

    public function saveNote(): void
    {
        if (! $this->editingReport) {
            return;
        }

        $service = app(VatReportService::class);
        $this->editingReport = $service->updateNote($this->editingReport, $this->reportNote);
        $this->editingReport->load(['lines.vatCode', 'attachments']);

        $this->closeNoteModal();
        session()->flash('success', 'Merknad lagret.');
    }

    public function uploadAttachment(): void
    {
        $this->validate([
            'attachment' => 'required|file|max:10240', // Max 10MB
        ]);

        if (! $this->editingReport) {
            return;
        }

        $service = app(VatReportService::class);
        $service->addAttachment($this->editingReport, $this->attachment);

        $this->editingReport = $this->editingReport->fresh(['lines.vatCode', 'attachments']);
        $this->attachment = null;

        session()->flash('success', 'Vedlegg lastet opp.');
    }

    public function removeAttachment(VatReportAttachment $attachment): void
    {
        $service = app(VatReportService::class);
        $service->removeAttachment($attachment);

        if ($this->editingReport) {
            $this->editingReport = $this->editingReport->fresh(['lines.vatCode', 'attachments']);
        }

        session()->flash('success', 'Vedlegg slettet.');
    }

    public function openSubmitModal(): void
    {
        $this->altinnReference = null;
        $this->showSubmitModal = true;
    }

    public function closeSubmitModal(): void
    {
        $this->showSubmitModal = false;
        $this->altinnReference = null;
    }

    public function submit(): void
    {
        if (! $this->editingReport) {
            return;
        }

        $service = app(VatReportService::class);
        $this->editingReport = $service->submitReport($this->editingReport, $this->altinnReference);
        $this->editingReport->load(['lines.vatCode', 'attachments']);

        $this->closeSubmitModal();
        session()->flash('success', 'MVA-melding merket som sendt.');
    }

    public function markAccepted(): void
    {
        if (! $this->editingReport) {
            return;
        }

        $service = app(VatReportService::class);
        $this->editingReport = $service->acceptReport($this->editingReport);
        $this->editingReport->load(['lines.vatCode', 'attachments']);

        session()->flash('success', 'MVA-melding merket som godkjent.');
    }

    public function markRejected(): void
    {
        if (! $this->editingReport) {
            return;
        }

        $service = app(VatReportService::class);
        $this->editingReport = $service->rejectReport($this->editingReport);
        $this->editingReport->load(['lines.vatCode', 'attachments']);

        session()->flash('success', 'MVA-melding merket som avvist.');
    }

    public function delete(VatReport $report): void
    {
        if ($report->status !== 'draft') {
            session()->flash('error', 'Kan ikke slette MVA-meldinger som er sendt eller godkjent.');

            return;
        }

        $report->delete();
        session()->flash('success', 'MVA-melding slettet.');
    }

    public function updatedFilterYear(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.vat-report-manager');
    }
}
