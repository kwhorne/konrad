<?php

namespace App\Livewire;

use App\Models\Dividend;
use App\Models\ShareClass;
use App\Rules\ExistsInCompany;
use App\Services\ShareholderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class DividendManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public $showModal = false;

    public $showDistributionModal = false;

    public $editingId = null;

    public $viewingDividendId = null;

    public $filterYear = '';

    public $filterStatus = '';

    // Form fields
    public $fiscal_year = '';

    public $declaration_date = '';

    public $record_date = '';

    public $payment_date = '';

    public $share_class_id = '';

    public $amount_per_share = '';

    public $dividend_type = 'ordinary';

    public $description = '';

    public $resolution_reference = '';

    protected function rules(): array
    {
        return [
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'declaration_date' => 'required|date',
            'record_date' => 'required|date|after_or_equal:declaration_date',
            'payment_date' => 'required|date|after_or_equal:record_date',
            'share_class_id' => ['required', new ExistsInCompany('share_classes')],
            'amount_per_share' => 'required|numeric|min:0.0001',
            'dividend_type' => 'required|in:ordinary,extraordinary',
            'description' => 'nullable|string',
            'resolution_reference' => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'fiscal_year.required' => 'Regnskapsår er påkrevd.',
        'declaration_date.required' => 'Vedtaksdato er påkrevd.',
        'record_date.required' => 'Registreringsdato er påkrevd.',
        'record_date.after_or_equal' => 'Registreringsdato må være etter vedtaksdato.',
        'payment_date.required' => 'Utbetalingsdato er påkrevd.',
        'payment_date.after_or_equal' => 'Utbetalingsdato må være etter registreringsdato.',
        'share_class_id.required' => 'Aksjeklasse er påkrevd.',
        'amount_per_share.required' => 'Beløp per aksje er påkrevd.',
        'amount_per_share.min' => 'Beløp per aksje må være større enn 0.',
    ];

    public function mount(): void
    {
        $this->filterYear = now()->year;
        $this->fiscal_year = now()->year;
        $this->declaration_date = now()->format('Y-m-d');
        $this->record_date = now()->format('Y-m-d');
        $this->payment_date = now()->addDays(14)->format('Y-m-d');
    }

    public function updatedFilterYear(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function openModal($id = null): void
    {
        $this->resetForm();

        if ($id) {
            $this->editingId = $id;
            $dividend = Dividend::findOrFail($id);

            $this->fiscal_year = $dividend->fiscal_year;
            $this->declaration_date = $dividend->declaration_date->format('Y-m-d');
            $this->record_date = $dividend->record_date->format('Y-m-d');
            $this->payment_date = $dividend->payment_date->format('Y-m-d');
            $this->share_class_id = $dividend->share_class_id;
            $this->amount_per_share = $dividend->amount_per_share;
            $this->dividend_type = $dividend->dividend_type;
            $this->description = $dividend->description;
            $this->resolution_reference = $dividend->resolution_reference;
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->authorize('create', Dividend::class);

        $this->validate();

        $shareholderService = app(ShareholderService::class);

        if ($this->editingId) {
            $dividend = Dividend::findOrFail($this->editingId);
            $dividend->update([
                'fiscal_year' => $this->fiscal_year,
                'declaration_date' => $this->declaration_date,
                'record_date' => $this->record_date,
                'payment_date' => $this->payment_date,
                'share_class_id' => $this->share_class_id,
                'amount_per_share' => $this->amount_per_share,
                'dividend_type' => $this->dividend_type,
                'description' => $this->description ?: null,
                'resolution_reference' => $this->resolution_reference ?: null,
            ]);
            $dividend->recalculateTotalAmount();
            session()->flash('success', 'Utbyttevedtaket ble oppdatert.');
        } else {
            $shareholderService->declareDividend([
                'fiscal_year' => $this->fiscal_year,
                'declaration_date' => $this->declaration_date,
                'record_date' => $this->record_date,
                'payment_date' => $this->payment_date,
                'share_class_id' => $this->share_class_id,
                'amount_per_share' => $this->amount_per_share,
                'dividend_type' => $this->dividend_type,
                'description' => $this->description ?: null,
                'resolution_reference' => $this->resolution_reference ?: null,
                'created_by' => auth()->id(),
            ]);
            session()->flash('success', 'Utbyttevedtaket ble registrert.');
        }

        $this->closeModal();
    }

    public function approve($id): void
    {
        $dividend = Dividend::findOrFail($id);
        $this->authorize('approve', $dividend);

        $dividend->markAsApproved();
        session()->flash('success', 'Utbyttet ble godkjent.');
    }

    public function markAsPaid($id): void
    {
        $dividend = Dividend::findOrFail($id);
        $this->authorize('markAsPaid', $dividend);

        $dividend->markAsPaid();
        session()->flash('success', 'Utbyttet ble markert som utbetalt.');
    }

    public function cancel($id): void
    {
        $dividend = Dividend::findOrFail($id);
        $this->authorize('cancel', $dividend);

        if (! $dividend->canBeCancelled()) {
            session()->flash('error', 'Kan ikke kansellere dette utbyttet.');

            return;
        }

        $dividend->cancel();
        session()->flash('success', 'Utbyttet ble kansellert.');
    }

    public function showDistribution($id): void
    {
        $this->viewingDividendId = $id;
        $this->showDistributionModal = true;
    }

    public function closeDistributionModal(): void
    {
        $this->showDistributionModal = false;
        $this->viewingDividendId = null;
    }

    public function delete($id): void
    {
        $dividend = Dividend::findOrFail($id);
        $this->authorize('delete', $dividend);

        if ($dividend->isPaid()) {
            session()->flash('error', 'Kan ikke slette utbetalt utbytte.');

            return;
        }

        $dividend->delete();
        session()->flash('success', 'Utbyttevedtaket ble slettet.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->fiscal_year = now()->year;
        $this->declaration_date = now()->format('Y-m-d');
        $this->record_date = now()->format('Y-m-d');
        $this->payment_date = now()->addDays(14)->format('Y-m-d');
        $this->share_class_id = '';
        $this->amount_per_share = '';
        $this->dividend_type = 'ordinary';
        $this->description = '';
        $this->resolution_reference = '';
        $this->resetValidation();
    }

    public function render()
    {
        $query = Dividend::with(['shareClass', 'creator'])
            ->ordered();

        if ($this->filterYear) {
            $query->where('fiscal_year', $this->filterYear);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $years = Dividend::selectRaw('DISTINCT fiscal_year')
            ->orderBy('fiscal_year', 'desc')
            ->pluck('fiscal_year');

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        $distribution = null;
        if ($this->viewingDividendId) {
            $dividend = Dividend::findOrFail($this->viewingDividendId);
            $shareholderService = app(ShareholderService::class);
            $distribution = $shareholderService->calculateDividendDistribution($dividend);
        }

        return view('livewire.dividend-manager', [
            'dividends' => $query->paginate(15),
            'shareClasses' => ShareClass::active()->withDividendRights()->ordered()->get(),
            'years' => $years,
            'distribution' => $distribution,
            'viewingDividend' => $this->viewingDividendId ? Dividend::find($this->viewingDividendId) : null,
        ]);
    }
}
