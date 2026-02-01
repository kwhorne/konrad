<?php

namespace App\Livewire;

use App\Models\AnnualAccount;
use App\Services\AnnualAccountService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AnnualAccountManager extends Component
{
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

    public function mount()
    {
        $this->createYear = now()->subYear()->year;
    }

    public function openCreateModal()
    {
        $this->createYear = now()->subYear()->year;
        $this->cloneFromPrevious = false;
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function createAccount()
    {
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

    public function viewAccount($id)
    {
        $this->viewingAccountId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->viewingAccountId = null;
    }

    public function refreshData($id)
    {
        $annualAccount = AnnualAccount::findOrFail($id);

        if (! $annualAccount->canBeEdited()) {
            session()->flash('error', 'Kan ikke oppdatere innsendt årsregnskap.');

            return;
        }

        $service = app(AnnualAccountService::class);
        $service->populateFromAccounting($annualAccount);

        session()->flash('success', 'Regnskapsdata ble oppdatert.');
    }

    public function validateAccount($id)
    {
        $annualAccount = AnnualAccount::findOrFail($id);
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

    public function approve($id)
    {
        $annualAccount = AnnualAccount::findOrFail($id);

        if (! $annualAccount->isDraft()) {
            session()->flash('error', 'Kun utkast kan godkjennes.');

            return;
        }

        $service = app(AnnualAccountService::class);
        $service->approve($annualAccount, auth()->id());

        session()->flash('success', 'Årsregnskapet er godkjent av styret.');
    }

    public function markAsDraft($id)
    {
        $annualAccount = AnnualAccount::findOrFail($id);

        if ($annualAccount->isSubmitted()) {
            session()->flash('error', 'Kan ikke endre innsendt årsregnskap.');

            return;
        }

        $annualAccount->markAsDraft();
        session()->flash('success', 'Årsregnskapet ble satt tilbake til utkast.');
    }

    public function submitToAltinn($id)
    {
        $annualAccount = AnnualAccount::findOrFail($id);

        if (! $annualAccount->canBeSubmitted()) {
            session()->flash('error', 'Årsregnskapet er ikke klar for innsending.');

            return;
        }

        // TODO: Implement Altinn submission
        session()->flash('info', 'Altinn-innsending er ikke implementert ennå.');
    }

    public function delete($id)
    {
        $annualAccount = AnnualAccount::findOrFail($id);

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
