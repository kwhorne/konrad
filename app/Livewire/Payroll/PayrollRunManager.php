<?php

namespace App\Livewire\Payroll;

use App\Models\PayrollRun;
use App\Services\Payroll\PayrollService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class PayrollRunManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public ?int $filterYear = null;

    public bool $showCreateModal = false;

    public int $newYear;

    public int $newMonth;

    public string $newPaymentDate;

    public function mount(): void
    {
        $this->filterYear = now()->year;
        $this->newYear = now()->year;
        $this->newMonth = now()->month;
        $this->newPaymentDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedNewMonth(): void
    {
        $this->newPaymentDate = Carbon::create($this->newYear, $this->newMonth, 1)
            ->endOfMonth()
            ->format('Y-m-d');
    }

    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function createRun(): mixed
    {
        $this->authorize('create', PayrollRun::class);

        $this->validate([
            'newYear' => 'required|integer|min:2020|max:2100',
            'newMonth' => 'required|integer|min:1|max:12',
            'newPaymentDate' => 'required|date',
        ]);

        $company = app('current.company');

        // Check if run already exists
        $existing = PayrollRun::where('company_id', $company->id)
            ->where('year', $this->newYear)
            ->where('month', $this->newMonth)
            ->exists();

        if ($existing) {
            session()->flash('error', 'Det finnes allerede en lønnskjøring for denne perioden.');

            return null;
        }

        $payrollService = app(PayrollService::class);
        $run = $payrollService->createPayrollRun(
            $company,
            $this->newYear,
            $this->newMonth,
            Carbon::parse($this->newPaymentDate)
        );

        session()->flash('success', 'Lønnskjøring opprettet.');
        $this->closeCreateModal();

        return redirect()->route('payroll.runs.show', $run);
    }

    public function calculateRun(int $id): void
    {
        $run = PayrollRun::findOrFail($id);
        $this->authorize('calculate', $run);

        if ($run->status !== PayrollRun::STATUS_DRAFT) {
            session()->flash('error', 'Kan kun beregne lønnskjøringer med status Utkast.');

            return;
        }

        $payrollService = app(PayrollService::class);
        $payrollService->calculatePayroll($run);

        session()->flash('success', 'Lønnskjøring beregnet.');
    }

    public function deleteRun(int $id): void
    {
        $run = PayrollRun::findOrFail($id);
        $this->authorize('delete', $run);

        if (! $run->is_editable) {
            session()->flash('error', 'Kan ikke slette en godkjent eller utbetalt lønnskjøring.');

            return;
        }

        $run->delete();
        session()->flash('success', 'Lønnskjøring slettet.');
    }

    public function render()
    {
        $company = app('current.company');

        $runs = PayrollRun::where('company_id', $company->id)
            ->when($this->filterYear, function ($query) {
                $query->forYear($this->filterYear);
            })
            ->ordered()
            ->paginate(12);

        $years = PayrollRun::where('company_id', $company->id)
            ->selectRaw('DISTINCT year')
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($years)) {
            $years = [now()->year];
        }

        $months = [
            1 => 'Januar',
            2 => 'Februar',
            3 => 'Mars',
            4 => 'April',
            5 => 'Mai',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'August',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return view('livewire.payroll.payroll-run-manager', [
            'runs' => $runs,
            'years' => $years,
            'months' => $months,
        ]);
    }
}
