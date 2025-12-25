<?php

namespace App\Livewire;

use App\Jobs\ParseVoucherJob;
use App\Models\Account;
use App\Models\Contact;
use App\Models\IncomingVoucher;
use App\Services\AccountSuggestionService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class IncomingVoucherManager extends Component
{
    use WithFileUploads, WithPagination;

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

    public function uploadVouchers(): void
    {
        $this->validate([
            'uploadFiles' => 'required|array|min:1',
            'uploadFiles.*' => 'file|max:'.config('voucher.max_file_size', 10240).'|mimes:pdf,jpg,jpeg,png,gif,webp',
        ], [
            'uploadFiles.required' => 'Velg minst en fil',
            'uploadFiles.*.max' => 'Filen er for stor (maks '.config('voucher.max_file_size', 10240).' KB)',
            'uploadFiles.*.mimes' => 'Ugyldig filtype. Tillatte typer: PDF, JPG, PNG, GIF, WebP',
        ]);

        $disk = config('voucher.storage.disk', 'local');
        $path = config('voucher.storage.path', 'incoming-vouchers');

        foreach ($this->uploadFiles as $file) {
            $storedPath = $file->store($path, $disk);

            $voucher = IncomingVoucher::create([
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'source' => IncomingVoucher::SOURCE_UPLOAD,
                'status' => IncomingVoucher::STATUS_PENDING,
                'created_by' => auth()->id(),
            ]);

            // Start AI-tolkning asynkront
            dispatch(new ParseVoucherJob($voucher));
        }

        $this->closeUploadModal();
        session()->flash('success', count($this->uploadFiles).' bilag lastet opp og sendt til tolkning.');
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

    protected function updateSuggestions(): void
    {
        if (! $this->selectedVoucher) {
            return;
        }

        $this->selectedVoucher->update([
            'suggested_supplier_id' => $this->editSupplierId,
            'suggested_invoice_number' => $this->editInvoiceNumber ?: null,
            'suggested_invoice_date' => $this->editInvoiceDate ?: null,
            'suggested_due_date' => $this->editDueDate ?: null,
            'suggested_total' => $this->editTotal ? (float) $this->editTotal : null,
            'suggested_vat_total' => $this->editVatTotal ? (float) $this->editVatTotal : null,
            'suggested_account_id' => $this->editAccountId,
        ]);
    }

    public function attest(): void
    {
        if (! $this->selectedVoucher) {
            return;
        }

        $this->validate([
            'editSupplierId' => 'required|exists:contacts,id',
            'editInvoiceNumber' => 'required|string|max:100',
            'editInvoiceDate' => 'required|date',
            'editTotal' => 'required|numeric|min:0.01',
            'editAccountId' => 'required|exists:accounts,id',
        ], [
            'editSupplierId.required' => 'Velg en leverandør',
            'editInvoiceNumber.required' => 'Fakturanummer er påkrevd',
            'editInvoiceDate.required' => 'Fakturadato er påkrevd',
            'editTotal.required' => 'Totalbeløp er påkrevd',
            'editAccountId.required' => 'Velg en konto',
        ]);

        $this->updateSuggestions();

        if ($this->selectedVoucher->attest(auth()->user())) {
            session()->flash('success', 'Bilag attestert.');
            $this->closeDetail();
        } else {
            session()->flash('error', 'Kunne ikke attestere bilaget.');
        }
    }

    public function approve(): void
    {
        if (! $this->selectedVoucher) {
            return;
        }

        $this->updateSuggestions();

        if ($this->selectedVoucher->approve(auth()->user())) {
            // Opprett leverandørfaktura og bokfør
            $supplierInvoice = $this->selectedVoucher->createSupplierInvoice();

            if ($supplierInvoice) {
                // Registrer kontobruk for læring
                $supplier = $supplierInvoice->contact;
                $account = Account::find($this->editAccountId);

                if ($supplier && $account) {
                    app(AccountSuggestionService::class)->recordUsage(
                        $supplier,
                        $this->selectedVoucher->parsed_data['description'] ?? '',
                        $account
                    );
                }

                session()->flash('success', 'Bilag godkjent og bokført.');
            } else {
                session()->flash('error', 'Bilaget ble godkjent, men kunne ikke opprette leverandørfaktura.');
            }

            $this->closeDetail();
        } else {
            session()->flash('error', 'Kunne ikke godkjenne bilaget.');
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

    public function reject(): void
    {
        if (! $this->selectedVoucher) {
            return;
        }

        $this->validate([
            'rejectReason' => 'required|string|min:5|max:500',
        ], [
            'rejectReason.required' => 'Oppgi en grunn for avvisning',
            'rejectReason.min' => 'Grunnen må være minst 5 tegn',
        ]);

        if ($this->selectedVoucher->reject(auth()->user(), $this->rejectReason)) {
            session()->flash('success', 'Bilag avvist.');
            $this->closeRejectModal();
            $this->closeDetail();
        } else {
            session()->flash('error', 'Kunne ikke avvise bilaget.');
        }
    }

    public function reParse(int $id): void
    {
        $voucher = IncomingVoucher::findOrFail($id);

        if ($voucher->status === IncomingVoucher::STATUS_PARSING) {
            session()->flash('error', 'Bilaget tolkes allerede.');

            return;
        }

        $voucher->update(['status' => IncomingVoucher::STATUS_PENDING]);
        dispatch(new ParseVoucherJob($voucher));

        session()->flash('success', 'Bilaget blir tolket på nytt.');
    }

    public function delete(int $id): void
    {
        $voucher = IncomingVoucher::findOrFail($id);

        if (in_array($voucher->status, [IncomingVoucher::STATUS_APPROVED, IncomingVoucher::STATUS_POSTED])) {
            session()->flash('error', 'Kan ikke slette godkjente eller bokførte bilag.');

            return;
        }

        $voucher->delete();
        session()->flash('success', 'Bilaget ble slettet.');

        if ($this->showDetailModal) {
            $this->closeDetail();
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

    public function render()
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

        // Status-tellinger
        $statusCounts = [
            'pending' => IncomingVoucher::where('status', IncomingVoucher::STATUS_PENDING)->count(),
            'parsing' => IncomingVoucher::where('status', IncomingVoucher::STATUS_PARSING)->count(),
            'parsed' => IncomingVoucher::where('status', IncomingVoucher::STATUS_PARSED)->count(),
            'attested' => IncomingVoucher::where('status', IncomingVoucher::STATUS_ATTESTED)->count(),
            'approved' => IncomingVoucher::where('status', IncomingVoucher::STATUS_APPROVED)->count(),
            'posted' => IncomingVoucher::where('status', IncomingVoucher::STATUS_POSTED)->count(),
            'rejected' => IncomingVoucher::where('status', IncomingVoucher::STATUS_REJECTED)->count(),
        ];

        return view('livewire.incoming-voucher-manager', [
            'vouchers' => $vouchers,
            'statusCounts' => $statusCounts,
        ]);
    }
}
