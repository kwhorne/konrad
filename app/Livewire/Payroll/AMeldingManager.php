<?php

namespace App\Livewire\Payroll;

use App\Models\AMeldingReport;
use App\Models\PayrollRun;
use App\Services\Payroll\AMeldingService;
use Livewire\Component;
use Livewire\WithPagination;

class AMeldingManager extends Component
{
    use WithPagination;

    public ?int $filterYear = null;

    public bool $showGenerateModal = false;

    public ?int $selectedRunId = null;

    public function mount(): void
    {
        $this->filterYear = now()->year;
    }

    public function openGenerateModal(): void
    {
        $this->selectedRunId = null;
        $this->showGenerateModal = true;
    }

    public function closeGenerateModal(): void
    {
        $this->showGenerateModal = false;
    }

    public function generateReport(): void
    {
        if (! $this->selectedRunId) {
            session()->flash('error', 'Velg en lonnskjoring.');

            return;
        }

        $run = PayrollRun::findOrFail($this->selectedRunId);

        $service = app(AMeldingService::class);
        $service->generateReport($run);

        session()->flash('success', 'A-melding generert.');
        $this->closeGenerateModal();
    }

    public function downloadXml(int $id): void
    {
        $report = AMeldingReport::findOrFail($id);

        // Would trigger download - placeholder for now
        session()->flash('success', 'XML lastes ned...');
    }

    public function render()
    {
        $company = app('current.company');

        $reports = AMeldingReport::where('company_id', $company->id)
            ->when($this->filterYear, function ($query) {
                $query->forYear($this->filterYear);
            })
            ->ordered()
            ->paginate(12);

        $availableRuns = PayrollRun::where('company_id', $company->id)
            ->whereIn('status', [PayrollRun::STATUS_PAID, PayrollRun::STATUS_REPORTED])
            ->whereDoesntHave('aMeldingReport')
            ->ordered()
            ->get();

        $aMeldingService = app(AMeldingService::class);

        return view('livewire.payroll.a-melding-manager', [
            'reports' => $reports,
            'availableRuns' => $availableRuns,
            'aMeldingService' => $aMeldingService,
        ]);
    }
}
