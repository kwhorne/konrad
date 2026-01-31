<?php

namespace App\Livewire;

use App\Services\CompanyService;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class OnboardingWizard extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public int $totalSteps = 4;

    // Step 1: Organization number search
    public string $searchQuery = '';

    public array $searchResults = [];

    public bool $isSearching = false;

    public ?string $searchError = null;

    // Step 2: Company information
    #[Validate('required|string|size:9|unique:companies,organization_number')]
    public string $organizationNumber = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:255')]
    public ?string $address = '';

    #[Validate('nullable|string|max:10')]
    public ?string $postalCode = '';

    #[Validate('nullable|string|max:100')]
    public ?string $city = '';

    #[Validate('nullable|string|max:100')]
    public string $country = 'Norge';

    #[Validate('nullable|string|max:50')]
    public ?string $phone = '';

    #[Validate('nullable|email|max:255')]
    public ?string $email = '';

    #[Validate('nullable|url|max:255')]
    public ?string $website = '';

    // Step 3: Banking and optional info
    #[Validate('nullable|string|max:255')]
    public ?string $bankName = '';

    #[Validate('nullable|string|max:20')]
    public ?string $bankAccount = '';

    #[Validate('nullable|string|max:34')]
    public ?string $iban = '';

    #[Validate('nullable|string|max:11')]
    public ?string $swift = '';

    #[Validate('nullable|image|max:2048')]
    public $logo;

    public ?string $existingLogoPath = null;

    // Additional company settings
    #[Validate('nullable|integer|min:1|max:365')]
    public int $defaultPaymentDays = 14;

    #[Validate('nullable|integer|min:1|max:365')]
    public int $defaultQuoteValidityDays = 30;

    public function mount(): void
    {
        // Check if user already has a company
        if (auth()->user()->hasCompany()) {
            $this->redirect(route('dashboard'));
        }
    }

    public function search(): void
    {
        $this->searchError = null;
        $this->searchResults = [];

        if (strlen($this->searchQuery) < 2) {
            return;
        }

        $this->isSearching = true;

        try {
            $isOrgNumber = preg_match('/^\d{9}$/', preg_replace('/\s/', '', $this->searchQuery));

            if ($isOrgNumber) {
                $orgNumber = preg_replace('/\s/', '', $this->searchQuery);
                $response = Http::timeout(10)
                    ->get("https://data.brreg.no/enhetsregisteret/api/enheter/{$orgNumber}");

                if ($response->successful()) {
                    $this->searchResults = [$this->mapEnhet($response->json())];
                }
            } else {
                $response = Http::timeout(10)
                    ->get('https://data.brreg.no/enhetsregisteret/api/enheter', [
                        'navn' => $this->searchQuery,
                        'size' => 10,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $enheter = $data['_embedded']['enheter'] ?? [];
                    $this->searchResults = array_map(fn ($e) => $this->mapEnhet($e), $enheter);
                }
            }
        } catch (\Exception $e) {
            $this->searchError = 'Kunne ikke koble til Brønnøysundregistrene. Prøv igjen senere.';
        }

        $this->isSearching = false;
    }

    public function selectCompany(int $index): void
    {
        if (! isset($this->searchResults[$index])) {
            return;
        }

        $company = $this->searchResults[$index];

        $this->organizationNumber = $company['organisasjonsnummer'];
        $this->name = $company['navn'];
        $this->address = $company['adresse'];
        $this->postalCode = $company['postnummer'];
        $this->city = $company['poststed'];
        $this->country = $company['land'] ?: 'Norge';
        $this->website = $company['hjemmeside'];

        $this->searchResults = [];
        $this->searchQuery = '';
        $this->step = 2;
    }

    public function manualEntry(): void
    {
        $this->searchResults = [];
        $this->searchQuery = '';
        $this->step = 2;
    }

    public function nextStep(): void
    {
        if ($this->step === 2) {
            $this->validate([
                'organizationNumber' => 'required|string|size:9|unique:companies,organization_number',
                'name' => 'required|string|max:255',
            ]);
        }

        if ($this->step < $this->totalSteps) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function createCompany(): void
    {
        $this->validate([
            'organizationNumber' => 'required|string|size:9|unique:companies,organization_number',
            'name' => 'required|string|max:255',
        ]);

        $companyService = app(CompanyService::class);

        $logoPath = null;
        if ($this->logo) {
            $logoPath = $this->logo->store('company-logos', 'public');
        }

        $companyService->createCompany([
            'organization_number' => $this->organizationNumber,
            'name' => $this->name,
            'address' => $this->address,
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'bank_name' => $this->bankName,
            'bank_account' => $this->bankAccount,
            'iban' => $this->iban,
            'swift' => $this->swift,
            'logo_path' => $logoPath,
            'default_payment_days' => $this->defaultPaymentDays,
            'default_quote_validity_days' => $this->defaultQuoteValidityDays,
        ], auth()->user());

        $this->redirect(route('dashboard'), navigate: true);
    }

    private function mapEnhet(array $enhet): array
    {
        $forretningsadresse = $enhet['forretningsadresse'] ?? [];

        return [
            'organisasjonsnummer' => $enhet['organisasjonsnummer'] ?? '',
            'navn' => $enhet['navn'] ?? '',
            'organisasjonsform' => $enhet['organisasjonsform']['beskrivelse'] ?? '',
            'naeringskode' => $enhet['naeringskode1']['beskrivelse'] ?? '',
            'hjemmeside' => $enhet['hjemmeside'] ?? '',
            'adresse' => implode(', ', $forretningsadresse['adresse'] ?? []),
            'postnummer' => $forretningsadresse['postnummer'] ?? '',
            'poststed' => $forretningsadresse['poststed'] ?? '',
            'land' => $forretningsadresse['land'] ?? 'Norge',
        ];
    }

    public function render()
    {
        return view('livewire.onboarding-wizard');
    }
}
