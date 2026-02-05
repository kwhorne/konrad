<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Contact;
use App\Models\IncomingVoucher;
use App\Rules\ExistsInCompany;
use App\Services\IncomingVoucherService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class IncomingVoucherManager extends Component
{
    use AuthorizesRequests, WithFileUploads, WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    public string $filterSource = '';

    // Opplastings-modal
    public bool $showUploadModal = false;

    public array $uploadFiles = [];

    // Detalj-modal
    public bool $showDetailModal = false;

    public ?IncomingVoucher $selectedVoucher = null;

    // Redigerbare felter
    public ?int $editSupplierId = null;

    public string $editInvoiceNumber = '';

    public string $editInvoiceDate = '';

    public string $editDueDate = '';

    public string $editTotal = '';

    public string $editVatTotal = '';

    public ?int $editAccountId = null;

    // Avvisnings-modal
    public bool $showRejectModal = false;

    public string $rejectReason = '';

    // Leverandørsøk
    public string $supplierSearch = '';

    protected $listeners = ['refresh' => '$refresh'];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterSource(): void
    {
        $this->resetPage();
    }

    public function openUploadModal(): void
    {
        $this->uploadFiles = [];
        $this->showUploadModal = true;
    }

    public function closeUploadModal(): void
    {
        $this->uploadFiles = [];
        $this->showUploadModal = false;
    }

    public function uploadVouchers(IncomingVoucherService $service): void
    {
        $this->authorize('create', IncomingVoucher::class);

        $this->validate([
            'uploadFiles' => 'required|array|min:1',
            'uploadFiles.*' => 'file|max:'.config('voucher.max_file_size', 10240).'|mimes:pdf,jpg,jpeg,png,gif,webp',
        ], [
            'uploadFiles.required' => 'Velg minst en fil',
            'uploadFiles.*.max' => 'Filen er for stor (maks '.config('voucher.max_file_size', 10240).' KB)',
            'uploadFiles.*.mimes' => 'Ugyldig filtype. Tillatte typer: PDF, JPG, PNG, GIF, WebP',
        ]);

        $uploadedCount = count($this->uploadFiles);
        $service->uploadFiles($this->uploadFiles);

        $this->closeUploadModal();
        session()->flash('success', $uploadedCount.' bilag lastet opp og sendt til tolkning.');
    }

    public function openDetail(int $id): void
    {
        $this->selectedVoucher = IncomingVoucher::with([
            'suggestedSupplier',
            'suggestedAccount',
            'attestedByUser',
            'approvedByUser',
            'rejectedByUser',
            'supplierInvoice',
        ])->findOrFail($id);

        // Populer redigerbare felter
        $this->editSupplierId = $this->selectedVoucher->suggested_supplier_id;
        $this->editInvoiceNumber = $this->selectedVoucher->suggested_invoice_number ?? '';
        $this->editInvoiceDate = $this->selectedVoucher->suggested_invoice_date?->format('Y-m-d') ?? '';
        $this->editDueDate = $this->selectedVoucher->suggested_due_date?->format('Y-m-d') ?? '';
        $this->editTotal = $this->selectedVoucher->suggested_total ? number_format($this->selectedVoucher->suggested_total, 2, '.', '') : '';
        $this->editVatTotal = $this->selectedVoucher->suggested_vat_total ? number_format($this->selectedVoucher->suggested_vat_total, 2, '.', '') : '';
        $this->editAccountId = $this->selectedVoucher->suggested_account_id;
        $this->supplierSearch = $this->selectedVoucher->suggestedSupplier?->company_name ?? '';

        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedVoucher = null;
        $this->resetValidation();
    }

    protected function getSuggestionData(): array
    {
        return [
            'supplier_id' => $this->editSupplierId,
            'invoice_number' => $this->editInvoiceNumber ?: null,
            'invoice_date' => $this->editInvoiceDate ?: null,
            'due_date' => $this->editDueDate ?: null,
            'total' => $this->editTotal ?: null,
            'vat_total' => $this->editVatTotal ?: null,
            'account_id' => $this->editAccountId,
        ];
    }

    public function attest(IncomingVoucherService $service): void
    {
        $this->authorize('attest', $this->selectedVoucher);

        if (! $this->selectedVoucher) {
            return;
        }

        $this->validate([
            'editSupplierId' => ['required', new ExistsInCompany('contacts')],
            'editInvoiceNumber' => 'required|string|max:100',
            'editInvoiceDate' => 'required|date',
            'editTotal' => 'required|numeric|min:0.01',
            'editAccountId' => ['required', new ExistsInCompany('accounts')],
        ], [
            'editSupplierId.required' => 'Velg en leverandør',
            'editInvoiceNumber.required' => 'Fakturanummer er påkrevd',
            'editInvoiceDate.required' => 'Fakturadato er påkrevd',
            'editTotal.required' => 'Totalbeløp er påkrevd',
            'editAccountId.required' => 'Velg en konto',
        ]);

        if ($service->attest($this->selectedVoucher, $this->getSuggestionData())) {
            session()->flash('success', 'Bilag attestert.');
            $this->closeDetail();
        } else {
            session()->flash('error', 'Kunne ikke attestere bilaget.');
        }
    }

    public function approve(IncomingVoucherService $service): void
    {
        $this->authorize('approve', $this->selectedVoucher);

        if (! $this->selectedVoucher) {
            return;
        }

        $result = $service->approve($this->selectedVoucher, $this->getSuggestionData());

        if ($result['success']) {
            if ($result['error']) {
                session()->flash('error', $result['error']);
            } else {
                session()->flash('success', 'Bilag godkjent og bokført.');
            }
            $this->closeDetail();
        } else {
            session()->flash('error', $result['error'] ?? 'Kunne ikke godkjenne bilaget.');
        }
    }

    public function openRejectModal(): void
    {
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->rejectReason = '';
        $this->showRejectModal = false;
    }

    public function reject(IncomingVoucherService $service): void
    {
        $this->authorize('reject', $this->selectedVoucher);

        if (! $this->selectedVoucher) {
            return;
        }

        $this->validate([
            'rejectReason' => 'required|string|min:5|max:500',
        ], [
            'rejectReason.required' => 'Oppgi en grunn for avvisning',
            'rejectReason.min' => 'Grunnen må være minst 5 tegn',
        ]);

        if ($service->reject($this->selectedVoucher, $this->rejectReason)) {
            session()->flash('success', 'Bilag avvist.');
            $this->closeRejectModal();
            $this->closeDetail();
        } else {
            session()->flash('error', 'Kunne ikke avvise bilaget.');
        }
    }

    public function reParse(int $id, IncomingVoucherService $service): void
    {
        $voucher = IncomingVoucher::findOrFail($id);
        $this->authorize('update', $voucher);

        if ($service->reParse($voucher)) {
            session()->flash('success', 'Bilaget blir tolket på nytt.');
        } else {
            session()->flash('error', 'Bilaget tolkes allerede.');
        }
    }

    public function delete(int $id, IncomingVoucherService $service): void
    {
        $voucher = IncomingVoucher::findOrFail($id);
        $this->authorize('delete', $voucher);

        if ($service->delete($voucher)) {
            session()->flash('success', 'Bilaget ble slettet.');

            if ($this->showDetailModal) {
                $this->closeDetail();
            }
        } else {
            session()->flash('error', 'Kan ikke slette godkjente eller bokførte bilag.');
        }
    }

    public function selectSupplier(int $supplierId): void
    {
        $supplier = Contact::find($supplierId);
        if ($supplier) {
            $this->editSupplierId = $supplier->id;
            $this->supplierSearch = $supplier->company_name;
        }
    }

    public function getSuppliersProperty()
    {
        return Contact::query()
            ->where('type', 'supplier')
            ->when($this->supplierSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('company_name', 'like', "%{$this->supplierSearch}%")
                        ->orWhere('organization_number', 'like', "%{$this->supplierSearch}%");
                });
            })
            ->orderBy('company_name')
            ->limit(20)
            ->get();
    }

    public function getAccountsProperty()
    {
        // Kostkontoer (klasse 4-7)
        return Account::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('account_number', 'like', '4%')
                    ->orWhere('account_number', 'like', '5%')
                    ->orWhere('account_number', 'like', '6%')
                    ->orWhere('account_number', 'like', '7%');
            })
            ->orderBy('account_number')
            ->get();
    }

    public function render(IncomingVoucherService $service)
    {
        $query = IncomingVoucher::with(['suggestedSupplier', 'suggestedAccount', 'creator'])
            ->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('reference_number', 'like', "%{$this->search}%")
                    ->orWhere('original_filename', 'like', "%{$this->search}%")
                    ->orWhere('suggested_invoice_number', 'like', "%{$this->search}%")
                    ->orWhereHas('suggestedSupplier', function ($sq) {
                        $sq->where('company_name', 'like', "%{$this->search}%");
                    });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterSource) {
            $query->where('source', $this->filterSource);
        }

        $vouchers = $query->paginate(20);

        return view('livewire.incoming-voucher-manager', [
            'vouchers' => $vouchers,
            'statusCounts' => $service->getStatusCounts(),
        ]);
    }
}
