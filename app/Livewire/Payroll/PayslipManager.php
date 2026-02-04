<?php

namespace App\Livewire\Payroll;

use App\Mail\PayslipMail;
use App\Models\PayrollEntry;
use App\Models\PayrollRun;
use App\Models\User;
use App\Services\Payroll\PayslipPdfService;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayslipManager extends Component
{
    use WithPagination;

    public ?int $filterYear = null;

    public ?int $filterMonth = null;

    public ?int $filterUserId = null;

    public bool $showSendModal = false;

    public ?int $selectedRunId = null;

    public array $selectedEntries = [];

    public bool $selectAll = false;

    public function mount(): void
    {
        $this->filterYear = now()->year;
    }

    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectedEntries = $this->getFilteredEntryIds();
        } else {
            $this->selectedEntries = [];
        }
    }

    /**
     * Download a single payslip as PDF.
     */
    public function downloadPayslip(int $entryId): StreamedResponse
    {
        $entry = PayrollEntry::findOrFail($entryId);
        $pdfService = app(PayslipPdfService::class);

        $password = $pdfService->getPasswordForEmployee($entry->user_id);
        $pdfContent = $pdfService->generatePayslip($entry, $password);
        $filename = $pdfService->getFilename($entry);

        return response()->streamDownload(
            fn () => print ($pdfContent),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Send a single payslip via email.
     */
    public function sendPayslip(int $entryId): void
    {
        $entry = PayrollEntry::with(['user', 'payrollRun'])->findOrFail($entryId);
        $pdfService = app(PayslipPdfService::class);

        $email = $pdfService->getEmailForEmployee($entry->user_id);
        $password = $pdfService->getPasswordForEmployee($entry->user_id);

        if (! $email) {
            session()->flash('error', "Ingen e-postadresse funnet for {$entry->user->name}.");

            return;
        }

        if (! $password) {
            session()->flash('error', "Personnummer mangler for {$entry->user->name}. Kan ikke sende passordbeskyttet PDF.");

            return;
        }

        Mail::to($email)->send(new PayslipMail($entry, $password));

        // Mark as sent
        $entry->update(['payslip_sent_at' => now()]);

        session()->flash('success', "Lønnsslipp sendt til {$entry->user->name} ({$email}).");
    }

    /**
     * Open modal to send multiple payslips.
     */
    public function openSendModal(?int $runId = null): void
    {
        $this->selectedRunId = $runId;
        $this->selectedEntries = [];
        $this->selectAll = false;

        if ($runId) {
            // Pre-select all entries for this run
            $this->selectedEntries = PayrollEntry::where('payroll_run_id', $runId)
                ->pluck('id')
                ->toArray();
            $this->selectAll = true;
        }

        $this->showSendModal = true;
    }

    /**
     * Close the send modal.
     */
    public function closeSendModal(): void
    {
        $this->showSendModal = false;
        $this->selectedRunId = null;
        $this->selectedEntries = [];
        $this->selectAll = false;
    }

    /**
     * Send selected payslips.
     */
    public function sendSelected(): void
    {
        if (empty($this->selectedEntries)) {
            session()->flash('error', 'Ingen lønnsslipper valgt.');

            return;
        }

        $entries = PayrollEntry::with(['user', 'payrollRun'])
            ->whereIn('id', $this->selectedEntries)
            ->get();

        $pdfService = app(PayslipPdfService::class);
        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($entries as $entry) {
            $email = $pdfService->getEmailForEmployee($entry->user_id);
            $password = $pdfService->getPasswordForEmployee($entry->user_id);

            if (! $email) {
                $failed++;
                $errors[] = "{$entry->user->name}: Ingen e-postadresse";

                continue;
            }

            if (! $password) {
                $failed++;
                $errors[] = "{$entry->user->name}: Personnummer mangler";

                continue;
            }

            try {
                Mail::to($email)->send(new PayslipMail($entry, $password));
                $entry->update(['payslip_sent_at' => now()]);
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "{$entry->user->name}: ".$e->getMessage();
            }
        }

        $this->closeSendModal();

        if ($sent > 0 && $failed === 0) {
            session()->flash('success', "Sendt {$sent} lønnsslipper.");
        } elseif ($sent > 0 && $failed > 0) {
            session()->flash('warning', "Sendt {$sent} lønnsslipper. {$failed} feilet.");
        } else {
            session()->flash('error', "Kunne ikke sende lønnsslipper. {$failed} feilet.");
        }
    }

    /**
     * Send all payslips for a payroll run.
     */
    public function sendAllForRun(int $runId): void
    {
        $this->selectedEntries = PayrollEntry::where('payroll_run_id', $runId)
            ->pluck('id')
            ->toArray();

        $this->sendSelected();
    }

    /**
     * Get filtered entry IDs for select all.
     */
    protected function getFilteredEntryIds(): array
    {
        $company = app('current.company');

        return PayrollEntry::where('company_id', $company->id)
            ->whereHas('payrollRun', function ($query) {
                $query->whereIn('status', ['paid', 'reported']);

                if ($this->filterYear) {
                    $query->forYear($this->filterYear);
                }

                if ($this->filterMonth) {
                    $query->where('month', $this->filterMonth);
                }
            })
            ->when($this->filterUserId, function ($query) {
                $query->where('user_id', $this->filterUserId);
            })
            ->pluck('id')
            ->toArray();
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

                if ($this->filterMonth) {
                    $query->where('month', $this->filterMonth);
                }
            })
            ->when($this->filterUserId, function ($query) {
                $query->where('user_id', $this->filterUserId);
            })
            ->with(['user', 'payrollRun'])
            ->orderByDesc('created_at')
            ->paginate(20);

        // Get available years and employees for filters
        $years = PayrollRun::where('company_id', $company->id)
            ->whereIn('status', ['paid', 'reported'])
            ->selectRaw('DISTINCT year')
            ->orderByDesc('year')
            ->pluck('year');

        $employees = User::whereHas('payrollEntries', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        })
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get payroll runs for bulk send
        $payrollRuns = PayrollRun::where('company_id', $company->id)
            ->whereIn('status', ['paid', 'reported'])
            ->when($this->filterYear, fn ($q) => $q->forYear($this->filterYear))
            ->ordered()
            ->get();

        return view('livewire.payroll.payslip-manager', [
            'entries' => $entries,
            'years' => $years,
            'employees' => $employees,
            'payrollRuns' => $payrollRuns,
        ]);
    }
}
