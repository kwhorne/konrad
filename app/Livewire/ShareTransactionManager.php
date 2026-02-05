<?php

namespace App\Livewire;

use App\Models\ShareClass;
use App\Models\Shareholder;
use App\Models\ShareTransaction;
use App\Rules\ExistsInCompany;
use App\Services\ShareholderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class ShareTransactionManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public $showModal = false;

    public $search = '';

    public $filterType = '';

    public $filterYear = '';

    public $filterShareClass = '';

    // Form fields
    public $transaction_type = 'issue';

    public $transaction_date = '';

    public $share_class_id = '';

    public $from_shareholder_id = '';

    public $to_shareholder_id = '';

    public $number_of_shares = '';

    public $price_per_share = '';

    public $total_amount = '';

    public $description = '';

    public $document_reference = '';

    protected function rules(): array
    {
        $rules = [
            'transaction_type' => 'required|in:issue,transfer,redemption,split,merger,bonus',
            'transaction_date' => 'required|date',
            'share_class_id' => ['required', new ExistsInCompany('share_classes')],
            'number_of_shares' => 'required|integer|min:1',
            'price_per_share' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'document_reference' => 'nullable|string|max:255',
        ];

        if (in_array($this->transaction_type, ['transfer', 'redemption'])) {
            $rules['from_shareholder_id'] = ['required', new ExistsInCompany('shareholders')];
        }

        if (in_array($this->transaction_type, ['issue', 'transfer', 'bonus'])) {
            $rules['to_shareholder_id'] = ['required', new ExistsInCompany('shareholders')];
        }

        return $rules;
    }

    protected $messages = [
        'transaction_date.required' => 'Transaksjonsdato er påkrevd.',
        'share_class_id.required' => 'Aksjeklasse er påkrevd.',
        'number_of_shares.required' => 'Antall aksjer er påkrevd.',
        'number_of_shares.min' => 'Antall aksjer må være minst 1.',
        'from_shareholder_id.required' => 'Selgende aksjonær er påkrevd.',
        'to_shareholder_id.required' => 'Kjøpende aksjonær er påkrevd.',
    ];

    public function mount(): void
    {
        $this->transaction_date = now()->format('Y-m-d');
        $this->filterYear = now()->year;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterYear(): void
    {
        $this->resetPage();
    }

    public function updatedFilterShareClass(): void
    {
        $this->resetPage();
    }

    public function updatedTransactionType(): void
    {
        // Reset shareholder fields when transaction type changes
        $this->from_shareholder_id = '';
        $this->to_shareholder_id = '';
    }

    public function updatedNumberOfShares(): void
    {
        $this->calculateTotal();
    }

    public function updatedPricePerShare(): void
    {
        $this->calculateTotal();
    }

    private function calculateTotal(): void
    {
        if ($this->number_of_shares && $this->price_per_share) {
            $this->total_amount = $this->number_of_shares * $this->price_per_share;
        }
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->authorize('create', ShareTransaction::class);

        $this->validate();

        $shareholderService = app(ShareholderService::class);

        try {
            $data = [
                'transaction_date' => $this->transaction_date,
                'share_class_id' => $this->share_class_id,
                'number_of_shares' => $this->number_of_shares,
                'price_per_share' => $this->price_per_share ?: null,
                'total_amount' => $this->total_amount ?: null,
                'description' => $this->description ?: null,
                'document_reference' => $this->document_reference ?: null,
                'created_by' => auth()->id(),
            ];

            switch ($this->transaction_type) {
                case 'issue':
                case 'bonus':
                    $data['shareholder_id'] = $this->to_shareholder_id;
                    $data['acquisition_type'] = $this->transaction_type === 'issue' ? 'purchase' : 'bonus';
                    $shareholderService->issueShares($data);
                    break;

                case 'transfer':
                    $data['from_shareholder_id'] = $this->from_shareholder_id;
                    $data['to_shareholder_id'] = $this->to_shareholder_id;
                    $shareholderService->transferShares($data);
                    break;

                case 'redemption':
                    $data['shareholder_id'] = $this->from_shareholder_id;
                    $shareholderService->redeemShares($data);
                    break;

                default:
                    // For split and merger, create transaction directly
                    ShareTransaction::create([
                        'transaction_date' => $this->transaction_date,
                        'transaction_type' => $this->transaction_type,
                        'share_class_id' => $this->share_class_id,
                        'from_shareholder_id' => $this->from_shareholder_id ?: null,
                        'to_shareholder_id' => $this->to_shareholder_id ?: null,
                        'number_of_shares' => $this->number_of_shares,
                        'price_per_share' => $this->price_per_share ?: null,
                        'total_amount' => $this->total_amount ?: null,
                        'description' => $this->description ?: null,
                        'document_reference' => $this->document_reference ?: null,
                        'created_by' => auth()->id(),
                    ]);
            }

            session()->flash('success', 'Transaksjonen ble registrert.');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Feil ved registrering: '.$e->getMessage());
        }
    }

    private function resetForm(): void
    {
        $this->transaction_type = 'issue';
        $this->transaction_date = now()->format('Y-m-d');
        $this->share_class_id = '';
        $this->from_shareholder_id = '';
        $this->to_shareholder_id = '';
        $this->number_of_shares = '';
        $this->price_per_share = '';
        $this->total_amount = '';
        $this->description = '';
        $this->document_reference = '';
        $this->resetValidation();
    }

    public function render()
    {
        $query = ShareTransaction::with(['shareClass', 'fromShareholder', 'toShareholder', 'creator'])
            ->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('transaction_number', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhereHas('fromShareholder', fn ($sq) => $sq->where('name', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('toShareholder', fn ($sq) => $sq->where('name', 'like', '%'.$this->search.'%'));
            });
        }

        if ($this->filterType) {
            $query->where('transaction_type', $this->filterType);
        }

        if ($this->filterYear) {
            $query->whereYear('transaction_date', $this->filterYear);
        }

        if ($this->filterShareClass) {
            $query->where('share_class_id', $this->filterShareClass);
        }

        $years = ShareTransaction::selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('livewire.share-transaction-manager', [
            'transactions' => $query->paginate(15),
            'shareholders' => Shareholder::active()->ordered()->get(),
            'shareClasses' => ShareClass::active()->ordered()->get(),
            'years' => $years,
        ]);
    }
}
