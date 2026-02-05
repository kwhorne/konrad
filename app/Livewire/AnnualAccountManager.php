<?php

namespace App\Livewire;

use App\Models\AnnualAccount;
use App\Services\AnnualAccountService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AnnualAccountManager extends Component
{
    use AuthorizesRequests;

    public $showCreateModal = false;

    public $showDetailModal = false;

    public $viewingAccountId = null;

    public $createYear;

    public $cloneFromPrevious = false;

    protected function rules(): array
    {
        $companyId = auth()->user()->current_company_id;

        return [
            'createYear' => [
                'required', 'integer', 'min:2000', 'max:2100',
                Rule::unique('annual_accounts', 'fiscal_year')->where('company_id', $companyId),
            ],
        ];
    }

    protected $messages = [
        'createYear.required' => 'År er påkrevd.',
        'createYear.unique' => 'Det finnes allerede et årsregnskap for dette året.',
    ];

    public function mount(): void
    {
        $this->createYear = now()->subYear()->year;
    }

    public function openCreateModal(): void
    {
        $this->createYear = now()->subYear()->year;
        $this->cloneFromPrevious = false;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function createAccount(): void
    {
        $this->authorize('create', AnnualAccount::class);

        $this->validate();

        $service = app(AnnualAccountService::class);

        if ($this->cloneFromPrevious) {
            $annualAccount = $service->cloneFromPreviousYear($this->createYear, auth()->id());
            if (! $annualAccount) {
                // No previous year to clone from, create new
                $annualAccount = $service->createAnnualAccount($this->createYear, auth()->id());
            }
        } else {
            $annualAccount = $service->createAnnualAccount($this->createYear, auth()->id());
        }

        session()->flash('success', "Årsregnskap for {$this->createYear} ble opprettet.");
        $this->closeCreateModal();

        $this->viewAccount($annualAccount->id);
    }

    public function viewAccount($id): void
    {
        $this->viewingAccountId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->viewingAccountId = null;
    }

    public function refreshData($id): void
    {
        $annualAccount = AnnualAccount::findOrFail($id);
        $this->authorize('update', $annualAccount);

        if (! $annualAccount->canBeEdited()) {
            session()->flash('error', 'Kan ikke oppdatere innsendt årsregnskap.');

            return;
        }

        $service = app(AnnualAccountService::class);
        $service->populateFromAccounting($annualAccount);

        session()->flash('success', 'Regnskapsdata ble oppdatert.');
    }

    public function validateAccount($id): void
    {
        $annualAccount = AnnualAccount::findOrFail($id);
        $this->authorize('update', $annualAccount);

        $service = app(AnnualAccountService::class);

        $result = $service->validate($annualAccount);

        if ($result['valid']) {
            session()->flash('success', 'Årsregnskapet er komplett og korrekt.');
        } else {
            $errors = implode(' ', $result['errors']);
            session()->flash('error', 'Valideringsfeil: '.$errors);
        }

        if (! empty($result['warnings'])) {
            $warnings = implode(' ', $result['warnings']);
            session()->flash('info', 'Advarsler: '.$warnings);
        }
    }

    public function approve($id): void
    {
        $annualAccount = AnnualAccount::findOrFail($id);
        $this->authorize('approve', $annualAccount);

        if (! $annualAccount->isDraft()) {
            session()->flash('error', 'Kun utkast kan godkjennes.');

            return;
        }

        $service = app(AnnualAccountService::class);
        $service->approve($annualAccount, auth()->id());

        session()->flash('success', 'Årsregnskapet er godkjent av styret.');
    }

    public function markAsDraft($id): void
    {
        $annualAccount = AnnualAccount::findOrFail($id);
        $this->authorize('markAsDraft', $annualAccount);

        if ($annualAccount->isSubmitted()) {
            session()->flash('error', 'Kan ikke endre innsendt årsregnskap.');

            return;
        }

        $annualAccount->markAsDraft();
        session()->flash('success', 'Årsregnskapet ble satt tilbake til utkast.');
    }

    public function submitToAltinn($id): void
    {
        $annualAccount = AnnualAccount::findOrFail($id);
        $this->authorize('submitToAltinn', $annualAccount);

        if (! $annualAccount->canBeSubmitted()) {
            session()->flash('error', 'Årsregnskapet er ikke klar for innsending.');

            return;
        }

        // TODO: Implement Altinn submission
        session()->flash('info', 'Altinn-innsending er ikke implementert ennå.');
    }

    public function delete($id): void
    {
        $annualAccount = AnnualAccount::findOrFail($id);
        $this->authorize('delete', $annualAccount);

        if ($annualAccount->isSubmitted()) {
            session()->flash('error', 'Kan ikke slette innsendt årsregnskap.');

            return;
        }

        $annualAccount->delete();
        session()->flash('success', 'Årsregnskapet ble slettet.');
        $this->closeDetailModal();
    }

    public function render()
    {
        $annualAccounts = AnnualAccount::with(['creator', 'altinnSubmission'])
            ->ordered()
            ->get();

        $viewingAccount = null;
        if ($this->viewingAccountId) {
            $viewingAccount = AnnualAccount::with(['creator', 'accountNotes', 'cashFlowStatement'])
                ->findOrFail($this->viewingAccountId);
        }

        $existingYears = AnnualAccount::pluck('fiscal_year')->toArray();
        $availableYears = collect(range(now()->year, now()->year - 5))
            ->filter(fn ($year) => ! in_array($year, $existingYears))
            ->values();

        $hasPreviousYear = AnnualAccount::where('fiscal_year', $this->createYear - 1)->exists();

        return view('livewire.annual-account-manager', [
            'annualAccounts' => $annualAccounts,
            'viewingAccount' => $viewingAccount,
            'availableYears' => $availableYears,
            'hasPreviousYear' => $hasPreviousYear,
        ]);
    }
}
