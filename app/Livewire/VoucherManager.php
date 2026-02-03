<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Voucher;
use App\Rules\ExistsInCompany;
use App\Services\VoucherService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class VoucherManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $filterType = '';

    public string $filterStatus = '';

    public bool $showModal = false;

    public bool $showLineModal = false;

    public ?int $editingId = null;

    public ?int $editingLineId = null;

    // Voucher form fields
    public string $voucher_date = '';

    public string $description = '';

    // Line form fields
    public ?int $line_account_id = null;

    public string $line_description = '';

    public string $line_debit = '';

    public string $line_credit = '';

    public ?int $line_contact_id = null;

    public string $accountSearch = '';

    // Working lines for new voucher
    public array $workingLines = [];

    protected $listeners = ['refresh' => '$refresh'];

    public function mount(): void
    {
        $this->voucher_date = now()->format('Y-m-d');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function getAccountsProperty()
    {
        return Account::active()
            ->when($this->accountSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('account_number', 'like', "{$this->accountSearch}%")
                        ->orWhere('name', 'like', "%{$this->accountSearch}%");
                });
            })
            ->orderBy('account_number')
            ->limit(20)
            ->get();
    }

    public function getContactsProperty()
    {
        return Contact::orderBy('company_name')->get();
    }

    public function getTotalDebitProperty(VoucherService $service): float
    {
        return $service->calculateTotalDebit($this->workingLines);
    }

    public function getTotalCreditProperty(VoucherService $service): float
    {
        return $service->calculateTotalCredit($this->workingLines);
    }

    public function getDifferenceProperty(VoucherService $service): float
    {
        return $service->calculateDifference($this->workingLines);
    }

    public function getIsBalancedProperty(VoucherService $service): bool
    {
        return $service->isBalanced($this->workingLines);
    }

    public function openModal(?int $id = null, ?VoucherService $service = null): void
    {
        $service ??= app(VoucherService::class);

        if ($id) {
            $voucher = Voucher::with('lines.account', 'lines.contact')->findOrFail($id);
            $this->authorize('view', $voucher);
        } else {
            $this->authorize('create', Voucher::class);
        }

        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $voucher = Voucher::with('lines.account', 'lines.contact')->findOrFail($id);
            $this->voucher_date = $voucher->voucher_date->format('Y-m-d');
            $this->description = $voucher->description ?? '';
            $this->workingLines = $service->voucherLinesToWorkingLines($voucher);
        } else {
            $this->voucher_date = now()->format('Y-m-d');
            $this->description = '';
            $this->workingLines = [];
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingId = null;
        $this->workingLines = [];
        $this->resetValidation();
    }

    public function openLineModal(?int $index = null): void
    {
        $this->resetValidation();
        $this->editingLineId = $index;
        $this->accountSearch = '';

        if ($index !== null && isset($this->workingLines[$index])) {
            $line = $this->workingLines[$index];
            $this->line_account_id = $line['account_id'];
            $this->line_description = $line['description'];
            $this->line_debit = $line['debit'] > 0 ? (string) $line['debit'] : '';
            $this->line_credit = $line['credit'] > 0 ? (string) $line['credit'] : '';
            $this->line_contact_id = $line['contact_id'];
            $this->accountSearch = $line['account_number'].' - '.$line['account_name'];
        } else {
            $this->line_account_id = null;
            $this->line_description = '';
            $this->line_debit = '';
            $this->line_credit = '';
            $this->line_contact_id = null;
        }

        $this->showLineModal = true;
    }

    public function closeLineModal(): void
    {
        $this->showLineModal = false;
        $this->editingLineId = null;
        $this->accountSearch = '';
        $this->resetValidation();
    }

    public function selectAccount(int $accountId): void
    {
        $account = Account::find($accountId);
        if ($account) {
            $this->line_account_id = $account->id;
            $this->accountSearch = $account->account_number.' - '.$account->name;
        }
    }

    public function saveLine(VoucherService $service): void
    {
        $this->validate([
            'line_account_id' => ['required', new ExistsInCompany('accounts')],
            'line_description' => 'nullable|string|max:255',
            'line_debit' => 'nullable|numeric|min:0',
            'line_credit' => 'nullable|numeric|min:0',
            'line_contact_id' => ['nullable', new ExistsInCompany('contacts')],
        ], [
            'line_account_id.required' => 'Velg en konto',
        ]);

        $debit = (float) ($this->line_debit ?: 0);
        $credit = (float) ($this->line_credit ?: 0);

        $errors = $service->validateLineAmounts($debit, $credit);
        if ($errors) {
            foreach ($errors as $field => $message) {
                $this->addError('line_'.$field, $message);
            }

            return;
        }

        $existingId = $this->editingLineId !== null
            ? ($this->workingLines[$this->editingLineId]['id'] ?? null)
            : null;

        $lineData = $service->buildLineData(
            $this->line_account_id,
            $this->line_description,
            $debit,
            $credit,
            $this->line_contact_id ?: null,
            $existingId
        );

        if ($this->editingLineId !== null) {
            $this->workingLines[$this->editingLineId] = $lineData;
        } else {
            $this->workingLines[] = $lineData;
        }

        $this->closeLineModal();
    }

    public function removeLine(int $index): void
    {
        unset($this->workingLines[$index]);
        $this->workingLines = array_values($this->workingLines);
    }

    public function save(VoucherService $service): void
    {
        $this->validate([
            'voucher_date' => 'required|date',
            'description' => 'required|string|max:500',
        ], [
            'voucher_date.required' => 'Bilagsdato er påkrevd',
            'description.required' => 'Beskrivelse er påkrevd',
        ]);

        if ($this->editingId) {
            $voucher = Voucher::findOrFail($this->editingId);
            $this->authorize('update', $voucher);
        } else {
            $this->authorize('create', Voucher::class);
        }

        $errors = $service->validateVoucher($this->workingLines);
        if ($errors) {
            foreach ($errors as $field => $message) {
                $this->addError('workingLines', $message);
            }

            return;
        }

        $voucherData = [
            'voucher_date' => $this->voucher_date,
            'description' => $this->description,
            'voucher_type' => 'manual',
        ];

        try {
            if ($this->editingId) {
                $voucher = Voucher::findOrFail($this->editingId);
                $service->updateVoucher($voucher, $voucherData, $this->workingLines);
            } else {
                $service->createVoucher($voucherData, $this->workingLines);
            }
        } catch (\InvalidArgumentException $e) {
            $this->addError('workingLines', $e->getMessage());

            return;
        }

        $this->closeModal();
        session()->flash('success', $this->editingId ? 'Bilaget ble oppdatert' : 'Bilaget ble opprettet');
    }

    public function post(int $id, VoucherService $service): void
    {
        $voucher = Voucher::findOrFail($id);
        $this->authorize('post', $voucher);

        if ($service->postVoucher($voucher)) {
            session()->flash('success', 'Bilaget ble bokført');
        } else {
            session()->flash('error', 'Kunne ikke bokføre bilaget. Sjekk at debet og kredit er i balanse.');
        }
    }

    public function delete(int $id, VoucherService $service): void
    {
        $voucher = Voucher::findOrFail($id);
        $this->authorize('delete', $voucher);

        if ($service->deleteVoucher($voucher)) {
            session()->flash('success', 'Bilaget ble slettet');
        } else {
            session()->flash('error', 'Kan ikke slette et bokført bilag');
        }
    }

    public function render()
    {
        $query = Voucher::with(['lines.account', 'lines.contact', 'creator'])
            ->byType('manual')
            ->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('voucher_number', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterStatus === 'posted') {
            $query->posted();
        } elseif ($this->filterStatus === 'unposted') {
            $query->unposted();
        }

        $vouchers = $query->paginate(20);

        return view('livewire.voucher-manager', [
            'vouchers' => $vouchers,
        ]);
    }
}
