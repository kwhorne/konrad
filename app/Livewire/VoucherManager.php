<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Livewire\Component;
use Livewire\WithPagination;

class VoucherManager extends Component
{
    use WithPagination;

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

    public function getTotalDebitProperty(): float
    {
        return collect($this->workingLines)->sum('debit');
    }

    public function getTotalCreditProperty(): float
    {
        return collect($this->workingLines)->sum('credit');
    }

    public function getDifferenceProperty(): float
    {
        return abs($this->totalDebit - $this->totalCredit);
    }

    public function getIsBalancedProperty(): bool
    {
        return $this->difference < 0.01 && count($this->workingLines) > 0;
    }

    public function openModal(?int $id = null): void
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $voucher = Voucher::with('lines.account', 'lines.contact')->findOrFail($id);
            $this->voucher_date = $voucher->voucher_date->format('Y-m-d');
            $this->description = $voucher->description ?? '';

            $this->workingLines = $voucher->lines->map(function ($line) {
                return [
                    'id' => $line->id,
                    'account_id' => $line->account_id,
                    'account_number' => $line->account->account_number,
                    'account_name' => $line->account->name,
                    'description' => $line->description ?? '',
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                    'contact_id' => $line->contact_id,
                    'contact_name' => $line->contact?->company_name,
                ];
            })->toArray();
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

    public function saveLine(): void
    {
        $this->validate([
            'line_account_id' => 'required|exists:accounts,id',
            'line_description' => 'nullable|string|max:255',
            'line_debit' => 'nullable|numeric|min:0',
            'line_credit' => 'nullable|numeric|min:0',
            'line_contact_id' => 'nullable|exists:contacts,id',
        ], [
            'line_account_id.required' => 'Velg en konto',
        ]);

        $debit = (float) ($this->line_debit ?: 0);
        $credit = (float) ($this->line_credit ?: 0);

        if ($debit == 0 && $credit == 0) {
            $this->addError('line_debit', 'Du må fylle inn debet eller kredit');

            return;
        }

        if ($debit > 0 && $credit > 0) {
            $this->addError('line_debit', 'Du kan ikke fylle inn både debet og kredit på samme linje');

            return;
        }

        $account = Account::find($this->line_account_id);
        $contact = $this->line_contact_id ? Contact::find($this->line_contact_id) : null;

        $lineData = [
            'account_id' => $this->line_account_id,
            'account_number' => $account->account_number,
            'account_name' => $account->name,
            'description' => $this->line_description,
            'debit' => $debit,
            'credit' => $credit,
            'contact_id' => $this->line_contact_id,
            'contact_name' => $contact?->company_name,
        ];

        if ($this->editingLineId !== null) {
            $lineData['id'] = $this->workingLines[$this->editingLineId]['id'] ?? null;
            $this->workingLines[$this->editingLineId] = $lineData;
        } else {
            $lineData['id'] = null;
            $this->workingLines[] = $lineData;
        }

        $this->closeLineModal();
    }

    public function removeLine(int $index): void
    {
        unset($this->workingLines[$index]);
        $this->workingLines = array_values($this->workingLines);
    }

    public function save(): void
    {
        $this->validate([
            'voucher_date' => 'required|date',
            'description' => 'required|string|max:500',
        ], [
            'voucher_date.required' => 'Bilagsdato er påkrevd',
            'description.required' => 'Beskrivelse er påkrevd',
        ]);

        if (count($this->workingLines) < 2) {
            $this->addError('workingLines', 'Et bilag må ha minst 2 linjer');

            return;
        }

        if (! $this->isBalanced) {
            $this->addError('workingLines', 'Debet og kredit må være i balanse');

            return;
        }

        if ($this->editingId) {
            $voucher = Voucher::findOrFail($this->editingId);

            if ($voucher->is_posted) {
                $this->addError('workingLines', 'Kan ikke redigere et bokført bilag');

                return;
            }

            $voucher->update([
                'voucher_date' => $this->voucher_date,
                'description' => $this->description,
            ]);

            // Get existing line IDs
            $existingIds = collect($this->workingLines)->pluck('id')->filter()->toArray();

            // Delete removed lines
            $voucher->lines()->whereNotIn('id', $existingIds)->delete();

            // Update or create lines
            foreach ($this->workingLines as $index => $line) {
                if ($line['id']) {
                    VoucherLine::find($line['id'])->update([
                        'account_id' => $line['account_id'],
                        'description' => $line['description'],
                        'debit' => $line['debit'],
                        'credit' => $line['credit'],
                        'contact_id' => $line['contact_id'],
                        'sort_order' => $index,
                    ]);
                } else {
                    $voucher->lines()->create([
                        'account_id' => $line['account_id'],
                        'description' => $line['description'],
                        'debit' => $line['debit'],
                        'credit' => $line['credit'],
                        'contact_id' => $line['contact_id'],
                        'sort_order' => $index,
                    ]);
                }
            }

            $voucher->recalculateTotals();
        } else {
            $voucher = Voucher::create([
                'voucher_date' => $this->voucher_date,
                'description' => $this->description,
                'voucher_type' => 'manual',
                'created_by' => auth()->id(),
            ]);

            foreach ($this->workingLines as $index => $line) {
                $voucher->lines()->create([
                    'account_id' => $line['account_id'],
                    'description' => $line['description'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'contact_id' => $line['contact_id'],
                    'sort_order' => $index,
                ]);
            }

            $voucher->recalculateTotals();
        }

        $this->closeModal();
        session()->flash('success', $this->editingId ? 'Bilaget ble oppdatert' : 'Bilaget ble opprettet');
    }

    public function post(int $id): void
    {
        $voucher = Voucher::findOrFail($id);

        if ($voucher->post()) {
            session()->flash('success', 'Bilaget ble bokført');
        } else {
            session()->flash('error', 'Kunne ikke bokføre bilaget. Sjekk at debet og kredit er i balanse.');
        }
    }

    public function delete(int $id): void
    {
        $voucher = Voucher::findOrFail($id);

        if ($voucher->is_posted) {
            session()->flash('error', 'Kan ikke slette et bokført bilag');

            return;
        }

        $voucher->delete();
        session()->flash('success', 'Bilaget ble slettet');
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
