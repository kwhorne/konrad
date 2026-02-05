<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\BankReconciliationDraft;
use App\Models\BankStatement;
use App\Models\BankTransaction;
use App\Models\CsvFormatMapping;
use App\Models\Invoice;
use App\Models\SupplierInvoice;
use App\Services\BankReconciliationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithFileUploads;

class BankReconciliationManager extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public int $currentStep = 1;

    public $uploadFile;

    public ?string $selectedFormatId = null;

    public ?int $selectedBankAccountId = null;

    public ?BankStatement $statement = null;

    public ?int $statementId = null;

    public ?BankTransaction $selectedTransaction = null;

    public ?int $selectedTransactionId = null;

    public bool $showMatchModal = false;

    public bool $showDraftModal = false;

    public string $matchSearch = '';

    public string $draftDescription = '';

    public ?int $draftAccountId = null;

    public ?int $draftContactId = null;

    public string $draftAmount = '';

    public string $draftVoucherType = '';

    protected $listeners = ['refresh' => '$refresh'];

    public function mount(?int $statementId = null): void
    {
        if ($statementId) {
            $this->statementId = $statementId;
            $this->statement = BankStatement::with('transactions')->find($statementId);
            if ($this->statement) {
                $this->currentStep = $this->determineStep();
            }
        }
    }

    public function updatedUploadFile(): void
    {
        $this->validateOnly('uploadFile');
    }

    protected function rules(): array
    {
        return [
            'uploadFile' => 'required|file|max:10240|mimes:csv,txt',
            'selectedBankAccountId' => 'nullable|exists:accounts,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'uploadFile.required' => 'Velg en CSV-fil',
            'uploadFile.max' => 'Filen er for stor (maks 10 MB)',
            'uploadFile.mimes' => 'Filen må være en CSV-fil',
        ];
    }

    protected function determineStep(): int
    {
        if (! $this->statement) {
            return 1;
        }

        if ($this->statement->status === BankStatement::STATUS_FINALIZED) {
            return 4;
        }

        if ($this->statement->unmatched_count === 0) {
            return 4;
        }

        if ($this->statement->matched_count > 0 || $this->statement->status === BankStatement::STATUS_MATCHING) {
            return 3;
        }

        return 2;
    }

    public function uploadAndParse(BankReconciliationService $service): void
    {
        $this->authorize('create', BankStatement::class);

        $this->validate([
            'uploadFile' => 'required|file|max:10240|mimes:csv,txt',
        ]);

        try {
            $formatId = is_numeric($this->selectedFormatId) ? (int) $this->selectedFormatId : null;

            $this->statement = $service->importCsvFile(
                $this->uploadFile,
                $formatId,
                $this->selectedBankAccountId
            );
            $this->statementId = $this->statement->id;

            session()->flash('success', "Importerte {$this->statement->transaction_count} transaksjoner.");
            $this->currentStep = 2;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function runMatching(BankReconciliationService $service): void
    {
        if (! $this->statement) {
            return;
        }

        try {
            $result = $service->runAutoMatching($this->statement);
            $this->statement->refresh();

            session()->flash('success', "Matchet {$result['matched']} transaksjoner. {$result['unmatched']} gjenstår.");
            $this->currentStep = 3;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function confirmMatch(int $transactionId, BankReconciliationService $service): void
    {
        $transaction = BankTransaction::find($transactionId);
        if (! $transaction) {
            return;
        }

        if ($service->confirmMatch($transaction, auth()->user())) {
            $this->statement?->refresh();
            session()->flash('success', 'Match bekreftet.');
        }
    }

    public function unmatchTransaction(int $transactionId, BankReconciliationService $service): void
    {
        $transaction = BankTransaction::find($transactionId);
        if (! $transaction) {
            return;
        }

        $service->unmatchTransaction($transaction);
        $this->statement?->refresh();
        session()->flash('success', 'Match fjernet.');
    }

    public function ignoreTransaction(int $transactionId, BankReconciliationService $service): void
    {
        $transaction = BankTransaction::find($transactionId);
        if (! $transaction) {
            return;
        }

        $service->ignoreTransaction($transaction);
        $this->statement?->refresh();
        session()->flash('success', 'Transaksjon ignorert.');
    }

    public function unignoreTransaction(int $transactionId, BankReconciliationService $service): void
    {
        $transaction = BankTransaction::find($transactionId);
        if (! $transaction) {
            return;
        }

        $service->unignoreTransaction($transaction);
        $this->statement?->refresh();
    }

    public function openMatchModal(int $transactionId): void
    {
        $this->selectedTransactionId = $transactionId;
        $this->selectedTransaction = BankTransaction::find($transactionId);
        $this->matchSearch = '';
        $this->showMatchModal = true;
    }

    public function closeMatchModal(): void
    {
        $this->showMatchModal = false;
        $this->selectedTransaction = null;
        $this->selectedTransactionId = null;
        $this->matchSearch = '';
    }

    public function manualMatch(string $type, int $id, BankReconciliationService $service): void
    {
        if (! $this->selectedTransaction) {
            return;
        }

        $matchable = match ($type) {
            'invoice' => Invoice::find($id),
            'supplier_invoice' => SupplierInvoice::find($id),
            default => null,
        };

        if (! $matchable) {
            session()->flash('error', 'Kunne ikke finne objektet.');

            return;
        }

        $service->matchTransaction($this->selectedTransaction, $matchable, auth()->user());
        $this->statement?->refresh();
        $this->closeMatchModal();
        session()->flash('success', 'Transaksjon matchet.');
    }

    public function openDraftModal(int $transactionId): void
    {
        $this->selectedTransactionId = $transactionId;
        $this->selectedTransaction = BankTransaction::find($transactionId);

        if ($this->selectedTransaction) {
            $this->draftDescription = $this->selectedTransaction->description;
            $this->draftAmount = number_format(abs($this->selectedTransaction->amount), 2, '.', '');
            $this->draftVoucherType = $this->selectedTransaction->isCredit
                ? BankReconciliationDraft::VOUCHER_TYPE_PAYMENT
                : BankReconciliationDraft::VOUCHER_TYPE_SUPPLIER_PAYMENT;
            $this->draftAccountId = null;
            $this->draftContactId = null;

            $existingDraft = $this->selectedTransaction->draftVoucher;
            if ($existingDraft) {
                $this->draftDescription = $existingDraft->description;
                $this->draftAmount = number_format($existingDraft->amount, 2, '.', '');
                $this->draftVoucherType = $existingDraft->voucher_type;
                $this->draftAccountId = $existingDraft->account_id;
                $this->draftContactId = $existingDraft->contact_id;
            }
        }

        $this->showDraftModal = true;
    }

    public function closeDraftModal(): void
    {
        $this->showDraftModal = false;
        $this->selectedTransaction = null;
        $this->selectedTransactionId = null;
        $this->draftDescription = '';
        $this->draftAmount = '';
        $this->draftAccountId = null;
        $this->draftContactId = null;
    }

    public function saveDraft(BankReconciliationService $service): void
    {
        if (! $this->selectedTransaction) {
            return;
        }

        $this->validate([
            'draftDescription' => 'required|string|max:255',
            'draftAccountId' => 'required|exists:accounts,id',
            'draftAmount' => 'required|numeric|min:0.01',
        ], [
            'draftDescription.required' => 'Beskrivelse er påkrevd',
            'draftAccountId.required' => 'Velg en konto',
            'draftAmount.required' => 'Beløp er påkrevd',
        ]);

        $existingDraft = $this->selectedTransaction->draftVoucher;

        $data = [
            'description' => $this->draftDescription,
            'account_id' => $this->draftAccountId,
            'contact_id' => $this->draftContactId,
            'amount' => (float) $this->draftAmount,
            'voucher_type' => $this->draftVoucherType,
        ];

        if ($existingDraft) {
            $service->updateDraftVoucher($existingDraft, $data);
        } else {
            $service->createDraftVoucher($this->selectedTransaction, $data);
        }

        $this->closeDraftModal();
        session()->flash('success', 'Kladd-bilag lagret.');
    }

    public function deleteDraft(int $transactionId, BankReconciliationService $service): void
    {
        $transaction = BankTransaction::with('draftVoucher')->find($transactionId);
        if (! $transaction?->draftVoucher) {
            return;
        }

        $service->deleteDraftVoucher($transaction->draftVoucher);
        session()->flash('success', 'Kladd-bilag slettet.');
    }

    public function processAllDrafts(BankReconciliationService $service): void
    {
        if (! $this->statement) {
            return;
        }

        $result = $service->processAllDrafts($this->statement);
        $this->statement->refresh();

        if ($result['processed'] > 0) {
            session()->flash('success', "Opprettet {$result['processed']} bilag.");
        }

        if (! empty($result['errors'])) {
            session()->flash('error', implode('. ', $result['errors']));
        }
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= 4) {
            if ($step === 1 && $this->statement) {
                return;
            }

            $this->currentStep = $step;
        }
    }

    public function finalizeReconciliation(BankReconciliationService $service): void
    {
        $this->authorize('create', BankStatement::class);

        if (! $this->statement) {
            return;
        }

        if ($service->finalizeReconciliation($this->statement, auth()->user())) {
            $this->statement->refresh();
            session()->flash('success', 'Bankavstemming fullført.');
        } else {
            session()->flash('error', 'Kan ikke fullføre. Det finnes fortsatt umatchede transaksjoner.');
        }
    }

    public function getFormatsProperty(): array
    {
        $formats = ['' => 'Auto-detekter'];

        foreach (CsvFormatMapping::getSystemFormats() as $key => $format) {
            $formats[$key] = $format['name'];
        }

        $customFormats = CsvFormatMapping::custom()->active()->get();
        foreach ($customFormats as $format) {
            $formats[$format->id] = $format->name;
        }

        return $formats;
    }

    public function getBankAccountsProperty()
    {
        return Account::where('account_number', 'like', '19%')
            ->where('is_active', true)
            ->orderBy('account_number')
            ->get();
    }

    public function getExpenseAccountsProperty()
    {
        return Account::where('is_active', true)
            ->where(function ($q) {
                $q->where('account_number', 'like', '4%')
                    ->orWhere('account_number', 'like', '5%')
                    ->orWhere('account_number', 'like', '6%')
                    ->orWhere('account_number', 'like', '7%');
            })
            ->orderBy('account_number')
            ->get();
    }

    public function getIncomeAccountsProperty()
    {
        return Account::where('is_active', true)
            ->where('account_number', 'like', '3%')
            ->orderBy('account_number')
            ->get();
    }

    public function getAllAccountsProperty()
    {
        return Account::where('is_active', true)
            ->orderBy('account_number')
            ->get();
    }

    public function getMatchSuggestionsProperty()
    {
        if (! $this->selectedTransaction) {
            return collect();
        }

        if ($this->selectedTransaction->isCredit) {
            $query = Invoice::where('balance', '>', 0)->with('contact');

            if ($this->matchSearch) {
                $query->where(function ($q) {
                    $q->where('invoice_number', 'like', "%{$this->matchSearch}%")
                        ->orWhereHas('contact', function ($sq) {
                            $sq->where('company_name', 'like', "%{$this->matchSearch}%");
                        });
                });
            }

            return $query->orderBy('due_date')->limit(20)->get()->map(function ($invoice) {
                return [
                    'type' => 'invoice',
                    'id' => $invoice->id,
                    'label' => "Faktura {$invoice->invoice_number}",
                    'description' => $invoice->contact?->company_name ?? '',
                    'amount' => $invoice->balance,
                    'date' => $invoice->due_date,
                ];
            });
        }

        $query = SupplierInvoice::where('balance', '>', 0)->with('contact');

        if ($this->matchSearch) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', "%{$this->matchSearch}%")
                    ->orWhereHas('contact', function ($sq) {
                        $sq->where('company_name', 'like', "%{$this->matchSearch}%");
                    });
            });
        }

        return $query->orderBy('due_date')->limit(20)->get()->map(function ($invoice) {
            return [
                'type' => 'supplier_invoice',
                'id' => $invoice->id,
                'label' => "Leverandorfaktura {$invoice->invoice_number}",
                'description' => $invoice->contact?->company_name ?? '',
                'amount' => $invoice->balance,
                'date' => $invoice->due_date,
            ];
        });
    }

    public function getStatisticsProperty()
    {
        if (! $this->statement) {
            return null;
        }

        return app(BankReconciliationService::class)->getStatistics($this->statement);
    }

    public function render()
    {
        $transactions = $this->statement?->transactions()
            ->with(['matches.matchable', 'draftVoucher'])
            ->ordered()
            ->get() ?? collect();

        return view('livewire.bank-reconciliation-manager', [
            'transactions' => $transactions,
            'unmatchedTransactions' => $transactions->where('match_status', 'unmatched'),
            'matchedTransactions' => $transactions->whereIn('match_status', ['auto_matched', 'manual_matched']),
            'ignoredTransactions' => $transactions->where('match_status', 'ignored'),
        ]);
    }
}
