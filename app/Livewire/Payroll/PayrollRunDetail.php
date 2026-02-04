<?php

namespace App\Livewire\Payroll;

use App\Models\PayrollRun;
use App\Services\Payroll\PayrollService;
use Livewire\Component;

class PayrollRunDetail extends Component
{
    public PayrollRun $run;

    public function mount(int $payrollRunId): void
    {
        $this->run = PayrollRun::with(['entries.user', 'createdByUser', 'approvedByUser'])
            ->findOrFail($payrollRunId);
    }

    public function calculate(): void
    {
        if ($this->run->status !== PayrollRun::STATUS_DRAFT) {
            session()->flash('error', 'Kan kun beregne lonnskjoringer med status Utkast.');

            return;
        }

        $payrollService = app(PayrollService::class);
        $payrollService->calculatePayroll($this->run);
        $this->run->refresh();

        session()->flash('success', 'Lonnskjoring beregnet.');
    }

    public function approve(): void
    {
        if ($this->run->status !== PayrollRun::STATUS_CALCULATED) {
            session()->flash('error', 'Kan kun godkjenne beregnede lonnskjoringer.');

            return;
        }

        $payrollService = app(PayrollService::class);
        $payrollService->approveRun($this->run, auth()->user());
        $this->run->refresh();

        session()->flash('success', 'Lonnskjoring godkjent.');
    }

    public function markAsPaid(): void
    {
        if ($this->run->status !== PayrollRun::STATUS_APPROVED) {
            session()->flash('error', 'Kan kun markere godkjente lonnskjoringer som utbetalt.');

            return;
        }

        $payrollService = app(PayrollService::class);
        $payrollService->markAsPaid($this->run);
        $this->run->refresh();

        session()->flash('success', 'Lonnskjoring markert som utbetalt.');
    }

    public function render()
    {
        return view('livewire.payroll.payroll-run-detail');
    }
}
