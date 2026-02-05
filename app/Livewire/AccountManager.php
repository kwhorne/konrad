<?php

namespace App\Livewire;

use App\Models\Account;
use Database\Seeders\AccountSeeder;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class AccountManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public $showModal = false;

    public $showConfirmNs4102Modal = false;

    public $editingId = null;

    public $search = '';

    public $filterClass = '';

    public $filterType = '';

    public $filterActive = '';

    // Form fields
    public $account_number = '';

    public $name = '';

    public $description = '';

    public $account_class = '';

    public $account_type = 'expense';

    public $vat_code = '';

    public $is_active = true;

    protected function rules(): array
    {
        return [
            'account_number' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'account_class' => 'required|string',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'vat_code' => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'account_number.required' => 'Kontonummer er pakrevd.',
        'name.required' => 'Kontonavn er pakrevd.',
        'account_class.required' => 'Kontoklasse er pakrevd.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'account_number', 'name', 'description', 'account_class', 'account_type', 'vat_code', 'is_active']);
        $this->is_active = true;
        $this->account_type = 'expense';
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $account = Account::findOrFail($id);
        $this->editingId = $account->id;
        $this->account_number = $account->account_number;
        $this->name = $account->name;
        $this->description = $account->description ?? '';
        $this->account_class = $account->account_class;
        $this->account_type = $account->account_type;
        $this->vat_code = $account->vat_code ?? '';
        $this->is_active = $account->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorize('create', Account::class);

        $this->validate();

        $data = [
            'account_number' => $this->account_number,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'account_class' => $this->account_class,
            'account_type' => $this->account_type,
            'vat_code' => $this->vat_code ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $account = Account::findOrFail($this->editingId);

            // Don't allow editing system accounts
            if ($account->is_system) {
                Flux::toast(text: 'Systemkontoer kan ikke redigeres', variant: 'danger');

                return;
            }

            $account->update($data);
            Flux::toast(text: 'Konto oppdatert', variant: 'success');
        } else {
            Account::create($data);
            Flux::toast(text: 'Konto opprettet', variant: 'success');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'account_number', 'name', 'description', 'account_class', 'account_type', 'vat_code']);
    }

    public function delete(int $id): void
    {
        $account = Account::findOrFail($id);
        $this->authorize('delete', $account);

        // Don't allow deleting system accounts
        if ($account->is_system) {
            Flux::toast(text: 'Systemkontoer kan ikke slettes', variant: 'danger');

            return;
        }

        // Check if account has voucher lines
        if ($account->voucherLines()->exists()) {
            Flux::toast(text: 'Kan ikke slette konto med posteringer', variant: 'danger');

            return;
        }

        $account->delete();
        Flux::toast(text: 'Konto slettet', variant: 'success');
    }

    public function confirmCreateNs4102(): void
    {
        $this->showConfirmNs4102Modal = true;
    }

    public function createNs4102ChartOfAccounts(): void
    {
        $this->authorize('create', Account::class);

        $this->showConfirmNs4102Modal = false;

        // Run the account seeder
        $seeder = new AccountSeeder;
        $seeder->run();

        Flux::toast(text: 'Norsk standard kontoplan (NS 4102) er opprettet', variant: 'success');
    }

    public function render()
    {
        $query = Account::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('account_number', 'like', "%{$this->search}%")
                        ->orWhere('name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterClass, fn ($q) => $q->where('account_class', $this->filterClass))
            ->when($this->filterType, fn ($q) => $q->where('account_type', $this->filterType))
            ->when($this->filterActive !== '', fn ($q) => $q->where('is_active', $this->filterActive === '1'))
            ->ordered();

        $accountClasses = config('accounting.account_classes', []);

        return view('livewire.account-manager', [
            'accounts' => $query->paginate(50),
            'accountClasses' => $accountClasses,
        ]);
    }
}
