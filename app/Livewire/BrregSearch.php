<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class BrregSearch extends Component
{
    public $query = '';

    public $results = [];

    public $isSearching = false;

    public $showResults = false;

    public $error = null;

    public function search()
    {
        $this->error = null;
        $this->results = [];

        if (strlen($this->query) < 2) {
            $this->showResults = false;

            return;
        }

        $this->isSearching = true;
        $this->showResults = true;

        try {
            // Check if query is an org number (only digits)
            $isOrgNumber = preg_match('/^\d{9}$/', preg_replace('/\s/', '', $this->query));

            if ($isOrgNumber) {
                // Search by org number - direct lookup
                $orgNumber = preg_replace('/\s/', '', $this->query);
                $response = Http::timeout(10)
                    ->get("https://data.brreg.no/enhetsregisteret/api/enheter/{$orgNumber}");

                if ($response->successful()) {
                    $this->results = [$this->mapEnhet($response->json())];
                } else {
                    $this->results = [];
                }
            } else {
                // Search by name
                $response = Http::timeout(10)
                    ->get('https://data.brreg.no/enhetsregisteret/api/enheter', [
                        'navn' => $this->query,
                        'size' => 10,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $enheter = $data['_embedded']['enheter'] ?? [];
                    $this->results = array_map(fn ($e) => $this->mapEnhet($e), $enheter);
                }
            }
        } catch (\Exception $e) {
            $this->error = 'Kunne ikke koble til Brønnøysundregistrene. Prøv igjen senere.';
            $this->results = [];
        }

        $this->isSearching = false;
    }

    public function selectCompany($index)
    {
        if (isset($this->results[$index])) {
            $company = $this->results[$index];
            $this->dispatch('company-selected', company: $company);
            $this->showResults = false;
            $this->query = '';
            $this->results = [];
        }
    }

    public function clearSearch()
    {
        $this->query = '';
        $this->results = [];
        $this->showResults = false;
        $this->error = null;
    }

    private function mapEnhet(array $enhet): array
    {
        $forretningsadresse = $enhet['forretningsadresse'] ?? [];
        $postadresse = $enhet['postadresse'] ?? [];

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
            'kommune' => $forretningsadresse['kommune'] ?? '',
            'postadresse' => implode(', ', $postadresse['adresse'] ?? []),
            'postadresse_postnummer' => $postadresse['postnummer'] ?? '',
            'postadresse_poststed' => $postadresse['poststed'] ?? '',
            'postadresse_land' => $postadresse['land'] ?? '',
            'stiftelsesdato' => $enhet['stiftelsesdato'] ?? '',
            'antallAnsatte' => $enhet['antallAnsatte'] ?? null,
        ];
    }

    public function render()
    {
        return view('livewire.brreg-search');
    }
}
