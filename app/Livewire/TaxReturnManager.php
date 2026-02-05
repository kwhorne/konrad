<?php

namespace App\Livewire;

use App\Models\TaxReturn;
use App\Services\TaxCalculationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TaxReturnManager extends Component
{
    use AuthorizesRequests;

    public $showCreateModal = false;

    public $showDetailModal = false;

    public $viewingReturnId = null;

    public $createYear;

    protected function rules(): array
    {
        $companyId = auth()->user()->current_company_id;

        return [
            'createYear' => [
                'required', 'integer', 'min:2000', 'max:2100',
                Rule::unique('tax_returns', 'fiscal_year')->where('company_id', $companyId),
            ],
        ];
    }

    protected $messages = [
        'createYear.required' => 'År er påkrevd.',
        'createYear.unique' => 'Det finnes allerede en skattemelding for dette året.',
    ];

    public function mount(): void
    {
        $this->createYear = now()->subYear()->year;
    }

    public function openCreateModal(): void
    {
        $this->createYear = now()->subYear()->year;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function createReturn(): void
    {
        $this->authorize('create', TaxReturn::class);

        $this->validate();

        $taxService = app(TaxCalculationService::class);
        $taxReturn = $taxService->createTaxReturn($this->createYear, auth()->id());

        session()->flash('success', "Skattemelding for {$this->createYear} ble opprettet.");
        $this->closeCreateModal();

        $this->viewReturn($taxReturn->id);
    }

    public function viewReturn($id): void
    {
        $this->viewingReturnId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->viewingReturnId = null;
    }

    public function calculateTax($id): void
    {
        $taxReturn = TaxReturn::findOrFail($id);
        $this->authorize('calculate', $taxReturn);

        if (! $taxReturn->canBeEdited()) {
            session()->flash('error', 'Kan ikke beregne på nytt for innsendt skattemelding.');

            return;
        }

        $taxService = app(TaxCalculationService::class);
        $taxService->calculateTaxReturn($taxReturn);

        session()->flash('success', 'Skatteberegning ble utført.');
    }

    public function validateReturn($id): void
    {
        $taxReturn = TaxReturn::findOrFail($id);
        $this->authorize('update', $taxReturn);

        $taxService = app(TaxCalculationService::class);

        $result = $taxService->validateTaxReturn($taxReturn);

        if ($result['valid']) {
            session()->flash('success', 'Skattemeldingen er komplett og korrekt.');
        } else {
            $errors = implode(' ', $result['errors']);
            session()->flash('error', 'Valideringsfeil: '.$errors);
        }

        if (! empty($result['warnings'])) {
            $warnings = implode(' ', $result['warnings']);
            session()->flash('info', 'Advarsler: '.$warnings);
        }
    }

    public function markAsReady($id): void
    {
        $taxReturn = TaxReturn::findOrFail($id);
        $this->authorize('markAsReady', $taxReturn);

        if (! $taxReturn->isDraft()) {
            session()->flash('error', 'Kun utkast kan markeres som klar.');

            return;
        }

        $taxReturn->markAsReady();
        session()->flash('success', 'Skattemeldingen er nå klar for innsending.');
    }

    public function markAsDraft($id): void
    {
        $taxReturn = TaxReturn::findOrFail($id);
        $this->authorize('markAsDraft', $taxReturn);

        if ($taxReturn->isSubmitted()) {
            session()->flash('error', 'Kan ikke endre innsendt skattemelding.');

            return;
        }

        $taxReturn->markAsDraft();
        session()->flash('success', 'Skattemeldingen ble satt tilbake til utkast.');
    }

    public function submitToAltinn($id): void
    {
        $taxReturn = TaxReturn::findOrFail($id);
        $this->authorize('submitToAltinn', $taxReturn);

        if (! $taxReturn->canBeSubmitted()) {
            session()->flash('error', 'Skattemeldingen er ikke klar for innsending.');

            return;
        }

        // TODO: Implement Altinn submission
        session()->flash('info', 'Altinn-innsending er ikke implementert ennå.');
    }

    public function delete($id): void
    {
        $taxReturn = TaxReturn::findOrFail($id);
        $this->authorize('delete', $taxReturn);

        if ($taxReturn->isSubmitted()) {
            session()->flash('error', 'Kan ikke slette innsendt skattemelding.');

            return;
        }

        $taxReturn->delete();
        session()->flash('success', 'Skattemeldingen ble slettet.');
    }

    public function render()
    {
        $taxReturns = TaxReturn::with(['creator', 'altinnSubmission'])
            ->ordered()
            ->get();

        $viewingReturn = null;
        if ($this->viewingReturnId) {
            $viewingReturn = TaxReturn::with(['creator', 'altinnSubmission'])
                ->findOrFail($this->viewingReturnId);
        }

        $existingYears = TaxReturn::pluck('fiscal_year')->toArray();
        $availableYears = collect(range(now()->year, now()->year - 5))
            ->filter(fn ($year) => ! in_array($year, $existingYears))
            ->values();

        return view('livewire.tax-return-manager', [
            'taxReturns' => $taxReturns,
            'viewingReturn' => $viewingReturn,
            'availableYears' => $availableYears,
        ]);
    }
}
