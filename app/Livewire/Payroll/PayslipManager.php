<?php

namespace App\Livewire\Payroll;

use App\Models\PayrollEntry;
use Livewire\Component;
use Livewire\WithPagination;

class PayslipManager extends Component
{
    use WithPagination;

    public ?int $filterYear = null;

    public ?int $filterUserId = null;

    public function mount(): void
    {
        $this->filterYear = now()->year;
    }

    public function render()
    {
        $company = app('current.company');

        $entries = PayrollEntry::where('company_id', $company->id)
            ->whereHas('payrollRun', function ($query) {
                $query->whereIn('status', ['paid', 'reported']);

                if ($this->filterYear) {
                    $query->forYear($this->filterYear);
                }
            })
            ->when($this->filterUserId, function ($query) {
                $query->where('user_id', $this->filterUserId);
            })
            ->with(['user', 'payrollRun'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.payroll.payslip-manager', [
            'entries' => $entries,
        ]);
    }
}
