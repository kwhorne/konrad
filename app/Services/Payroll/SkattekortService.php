<?php

namespace App\Services\Payroll;

use App\Models\EmployeePayrollSettings;
use App\Services\Altinn\MaskinportenService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SkattekortService
{
    private const SCOPE = 'skatteetaten:skattekort';

    public function __construct(
        private MaskinportenService $maskinporten,
        private TaxCalculationService $taxCalculation
    ) {}

    /**
     * Fetch tax card from Skatteetaten API for an employee.
     *
     * @throws \RuntimeException
     */
    public function fetchTaxCard(EmployeePayrollSettings $settings): array
    {
        if (! $settings->personnummer) {
            throw new \RuntimeException('Personnummer mangler for den ansatte.');
        }

        if (! $this->maskinporten->isConfigured()) {
            throw new \RuntimeException(
                'Maskinporten er ikke konfigurert. Kontakt administrator.'
            );
        }

        $accessToken = $this->maskinporten->getAccessToken(self::SCOPE);
        $endpoint = $this->getEndpoint();

        // The employer's organization number
        $company = app('current.company');
        $orgnr = $company->org_number;

        if (! $orgnr) {
            throw new \RuntimeException('Bedriftens organisasjonsnummer mangler.');
        }

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post("{$endpoint}/api/skatteoppgjoer/v1/hentskattekort", [
                'inntektsaar' => now()->year,
                'personidentifikator' => $settings->personnummer,
                'arbeidsgiveridentifikator' => [
                    'organisasjonsnummer' => $orgnr,
                ],
            ]);

        if (! $response->successful()) {
            Log::error('Skattekort API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'employee_id' => $settings->id,
            ]);

            throw new \RuntimeException(
                'Kunne ikke hente skattekort: '.$this->parseErrorMessage($response)
            );
        }

        $data = $response->json();

        return $this->processTaxCardResponse($data, $settings);
    }

    /**
     * Process the tax card response and update employee settings.
     */
    protected function processTaxCardResponse(array $data, EmployeePayrollSettings $settings): array
    {
        $taxCard = $data['skattekort'] ?? $data;

        // Extract relevant information from the response
        $result = [
            'raw_response' => $data,
            'trekktype' => null,
            'tabellnummer' => null,
            'trekkprosent' => null,
            'frikortbeloep' => null,
            'gyldig_fra' => null,
            'gyldig_til' => null,
        ];

        // Parse based on trekk type
        $trekk = $taxCard['forskuddstrekk'] ?? $taxCard['trekkode'] ?? null;

        if (isset($trekk['tabelltrekk'])) {
            $result['trekktype'] = 'tabelltrekk';
            $result['tabellnummer'] = $trekk['tabelltrekk']['tabellnummer'] ?? null;
        } elseif (isset($trekk['prosenttrekk'])) {
            $result['trekktype'] = 'prosenttrekk';
            $result['trekkprosent'] = $trekk['prosenttrekk']['prosentsats'] ?? null;
        } elseif (isset($trekk['frikort'])) {
            $result['trekktype'] = 'frikort';
            $result['frikortbeloep'] = $trekk['frikort']['frikortbeloep'] ?? null;
        }

        // Extract validity dates
        $result['gyldig_fra'] = $taxCard['gyldigFraOgMed'] ?? null;
        $result['gyldig_til'] = $taxCard['gyldigTilOgMed'] ?? null;

        // Update the employee settings with the new tax card data
        $this->updateEmployeeFromTaxCard($settings, $result);

        return $result;
    }

    /**
     * Update employee payroll settings from tax card data.
     */
    public function updateEmployeeFromTaxCard(EmployeePayrollSettings $settings, array $taxCardData): void
    {
        $updateData = [
            'skattekort_hentet_at' => now(),
            'skattekort_data' => $taxCardData,
        ];

        // Set tax type and related fields
        switch ($taxCardData['trekktype']) {
            case 'tabelltrekk':
                $updateData['skatt_type'] = EmployeePayrollSettings::SKATT_TYPE_TABELLTREKK;
                if ($taxCardData['tabellnummer']) {
                    $updateData['skattetabell'] = $taxCardData['tabellnummer'];
                }
                break;

            case 'prosenttrekk':
                $updateData['skatt_type'] = EmployeePayrollSettings::SKATT_TYPE_PROSENTTREKK;
                if ($taxCardData['trekkprosent']) {
                    $updateData['skatteprosent'] = $taxCardData['trekkprosent'];
                }
                break;

            case 'frikort':
                $updateData['skatt_type'] = EmployeePayrollSettings::SKATT_TYPE_FRIKORT;
                if ($taxCardData['frikortbeloep']) {
                    $updateData['frikort_belop'] = $taxCardData['frikortbeloep'];
                    // Reset used amount at start of year
                    $updateData['frikort_brukt'] = 0;
                }
                break;
        }

        // Set validity dates
        if ($taxCardData['gyldig_fra']) {
            $updateData['skattekort_gyldig_fra'] = $taxCardData['gyldig_fra'];
        }
        if ($taxCardData['gyldig_til']) {
            $updateData['skattekort_gyldig_til'] = $taxCardData['gyldig_til'];
        }

        $settings->update($updateData);
    }

    /**
     * Fetch tax cards for all employees in a company.
     *
     * @return array{success: int, failed: int, errors: array}
     */
    public function syncAllEmployees(): array
    {
        $company = app('current.company');
        $employees = EmployeePayrollSettings::where('company_id', $company->id)
            ->whereNotNull('personnummer')
            ->active()
            ->get();

        $result = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($employees as $employee) {
            try {
                $this->fetchTaxCard($employee);
                $result['success']++;
            } catch (\Exception $e) {
                $result['failed']++;
                $result['errors'][$employee->id] = $e->getMessage();
                Log::warning('Failed to fetch tax card', [
                    'employee_id' => $employee->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $result;
    }

    /**
     * Check if the Skattekort API is configured and available.
     */
    public function isAvailable(): bool
    {
        return $this->maskinporten->isConfigured();
    }

    /**
     * Get the API endpoint based on environment.
     */
    protected function getEndpoint(): string
    {
        $environment = config('altinn.environment', 'test');

        return config("altinn.endpoints.{$environment}.skatteetaten");
    }

    /**
     * Parse error message from API response.
     */
    protected function parseErrorMessage($response): string
    {
        $body = $response->json() ?? [];

        return $body['melding']
            ?? $body['message']
            ?? $body['error']
            ?? "HTTP {$response->status()}";
    }

    /**
     * Validate a Norwegian personnummer (basic validation).
     */
    public function validatePersonnummer(string $personnummer): bool
    {
        // Must be 11 digits
        if (! preg_match('/^\d{11}$/', $personnummer)) {
            return false;
        }

        // Extract parts
        $d1 = (int) $personnummer[0];
        $d2 = (int) $personnummer[1];
        $m1 = (int) $personnummer[2];
        $m2 = (int) $personnummer[3];

        // Basic date validation (days 01-31, months 01-12)
        $day = $d1 * 10 + $d2;
        $month = $m1 * 10 + $m2;

        // D-numbers have 4 added to first digit
        if ($d1 >= 4 && $d1 <= 7) {
            $day -= 40;
        }

        if ($day < 1 || $day > 31 || $month < 1 || $month > 12) {
            return false;
        }

        // Validate control digits using modulo 11
        $weights1 = [3, 7, 6, 1, 8, 9, 4, 5, 2];
        $weights2 = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];

        $sum1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum1 += (int) $personnummer[$i] * $weights1[$i];
        }
        $control1 = 11 - ($sum1 % 11);
        if ($control1 === 11) {
            $control1 = 0;
        }
        if ($control1 === 10 || $control1 !== (int) $personnummer[9]) {
            return false;
        }

        $sum2 = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum2 += (int) $personnummer[$i] * $weights2[$i];
        }
        $control2 = 11 - ($sum2 % 11);
        if ($control2 === 11) {
            $control2 = 0;
        }
        if ($control2 === 10 || $control2 !== (int) $personnummer[10]) {
            return false;
        }

        return true;
    }
}
