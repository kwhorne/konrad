<?php

namespace App\Livewire\Payroll;

use App\Models\PayrollRun;
use App\Services\Payroll\PayrollService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class PayrollRunDetail extends Component
{
    use AuthorizesRequests;

    public PayrollRun $run;

    public function mount(int $payrollRunId): void
    {
        $this->run = PayrollRun::with(['entries.user', 'createdByUser', 'approvedByUser'])
            ->findOrFail($payrollRunId);

        $this->authorize('view', $this->run);
    }

    public function calculate(): void
    {
        $this->authorize('calculate', $this->run);

        if ($this->run->status !== PayrollRun::STATUS_DRAFT) {
            session()->flash('error', 'Kan kun beregne lønnskjøringer med status Utkast.');

            return;
        }

        $payrollService = app(PayrollService::class);
        $payrollService->calculatePayroll($this->run);
        $this->run->refresh();

        session()->flash('success', 'Lønnskjøring beregnet.');
    }

    public function approve(): void
    {
        $this->authorize('approve', $this->run);

        if ($this->run->status !== PayrollRun::STATUS_CALCULATED) {
            session()->flash('error', 'Kan kun godkjenne beregnede lønnskjøringer.');

            return;
        }

        $payrollService = app(PayrollService::class);
        $payrollService->approveRun($this->run, auth()->user());
        $this->run->refresh();

        session()->flash('success', 'Lønnskjøring godkjent.');
    }

    public function markAsPaid(): void
    {
        $this->authorize('markAsPaid', $this->run);

        if ($this->run->status !== PayrollRun::STATUS_APPROVED) {
            session()->flash('error', 'Kan kun markere godkjente lønnskjøringer som utbetalt.');

            return;
        }

        $payrollService = app(PayrollService::class);
        $payrollService->markAsPaid($this->run);
        $this->run->refresh();

        session()->flash('success', 'Lønnskjøring markert som utbetalt.');
    }

    public function render()
    {
        return view('livewire.payroll.payroll-run-detail');
    }
}
