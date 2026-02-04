<?php

namespace App\Services\Payroll;

use App\Models\EmployeePayrollSettings;
use App\Services\Altinn\MaskinportenService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SkattekortService
{
    /**
     * Maskinporten scope for Skattekort API.
     *
     * @see https://skatteetaten.github.io/api-dokumentasjon/api/skattekort
     */
    private const SCOPE = 'skatteetaten:skattekort';

    /**
     * Skattekort API path (Skattekortoppslag for arbeidsgiver).
     */
    private const API_PATH = '/api/innkreving/skattekortoppslag/v1/arbeidsgiver/hent';

    public function __construct(
        private MaskinportenService $maskinporten,
        private TaxCalculationService $taxCalculation
    ) {}

    /**
     * Fetch tax card from Skatteetaten API for an employee.
     *
     * API: Skattekortoppslag for arbeidsgiver
     *
     * @see https://skatteetaten.github.io/api-dokumentasjon/api/skattekort
     *
     * @throws \RuntimeException
     */
    public function fetchTaxCard(EmployeePayrollSettings $settings): array
    {
        if (! $settings->personnummer) {
            throw new \RuntimeException('Personnummer mangler for den ansatte.');
        }

        if (! $this->validatePersonnummer($settings->personnummer)) {
            throw new \RuntimeException('Ugyldig personnummer format.');
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

        // Clean org number (remove spaces and dashes)
        $orgnr = preg_replace('/[^0-9]/', '', $orgnr);

        $requestBody = [
            'inntektsaar' => (int) now()->year,
            'personidentifikator' => $settings->personnummer,
            'arbeidsgiveridentifikator' => [
                'organisasjonsnummer' => $orgnr,
            ],
        ];

        Log::info('Skattekort API request', [
            'endpoint' => $endpoint.self::API_PATH,
            'employee_id' => $settings->id,
            'inntektsaar' => $requestBody['inntektsaar'],
        ]);

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Korrelasjonsid' => $this->generateCorrelationId(),
            ])
            ->timeout(30)
            ->retry(3, 1000, function ($exception, $request) {
                // Retry on connection errors or 5xx responses
                return $exception instanceof \Illuminate\Http\Client\ConnectionException
                    || ($exception instanceof \Illuminate\Http\Client\RequestException
                        && $exception->response->serverError());
            })
            ->post($endpoint.self::API_PATH, $requestBody);

        if (! $response->successful()) {
            $this->logApiError($response, $settings);
            throw new \RuntimeException(
                'Kunne ikke hente skattekort: '.$this->parseErrorMessage($response)
            );
        }

        $data = $response->json();

        Log::info('Skattekort API response', [
            'employee_id' => $settings->id,
            'has_skattekort' => isset($data['skattekort']),
        ]);

        return $this->processTaxCardResponse($data, $settings);
    }

    /**
     * Process the tax card response and update employee settings.
     *
     * Response structure from Skatteetaten:
     * {
     *   "arbeidsgiveridentifikator": { "organisasjonsnummer": "..." },
     *   "arbeidstaker": { "personidentifikator": "..." },
     *   "skattekort": {
     *     "utstedtDato": "2024-01-01",
     *     "skattekortidentifikator": 12345,
     *     "resultatPaaForespoersel": "skattekortOpplysningerOK",
     *     "forskuddstrekk": [
     *       {
     *         "trekkode": "tabelltrekk|prosenttrekk|frikort|trekkpliktig",
     *         "tabellnummer": "7100",
     *         "prosentsats": 25.0,
     *         "frikortbeloep": 65000,
     *         "trekkprosent": 50,
     *         "gyldigFraOgMed": "2024-01-01",
     *         "gyldigTilOgMed": "2024-12-31"
     *       }
     *     ]
     *   }
     * }
     */
    protected function processTaxCardResponse(array $data, EmployeePayrollSettings $settings): array
    {
        // Check for error response
        if (isset($data['feilmelding']) || isset($data['error'])) {
            throw new \RuntimeException(
                $data['feilmelding'] ?? $data['error'] ?? 'Ukjent feil fra Skatteetaten'
            );
        }

        $skattekort = $data['skattekort'] ?? null;

        // Check result status
        $resultat = $skattekort['resultatPaaForespoersel'] ?? null;
        if ($resultat && $resultat !== 'skattekortOpplysningerOK') {
            throw new \RuntimeException(
                $this->translateResultCode($resultat)
            );
        }

        // Initialize result
        $result = [
            'raw_response' => $data,
            'skattekort_id' => $skattekort['skattekortidentifikator'] ?? null,
            'utstedt_dato' => $skattekort['utstedtDato'] ?? null,
            'trekktype' => null,
            'tabellnummer' => null,
            'trekkprosent' => null,
            'frikortbeloep' => null,
            'gyldig_fra' => null,
            'gyldig_til' => null,
        ];

        // Process forskuddstrekk (tax deduction rules)
        $forskuddstrekk = $skattekort['forskuddstrekk'] ?? [];

        // Use the first (primary) forskuddstrekk entry
        $trekk = $forskuddstrekk[0] ?? null;

        if ($trekk) {
            $trekkode = $trekk['trekkode'] ?? null;

            switch ($trekkode) {
                case 'tabelltrekk':
                    $result['trekktype'] = 'tabelltrekk';
                    $result['tabellnummer'] = $trekk['tabellnummer'] ?? null;
                    // Some table-based deductions also have a percentage for 6-digit tables
                    if (isset($trekk['prosentsats'])) {
                        $result['trekkprosent'] = (float) $trekk['prosentsats'];
                    }
                    break;

                case 'prosenttrekk':
                    $result['trekktype'] = 'prosenttrekk';
                    $result['trekkprosent'] = (float) ($trekk['prosentsats'] ?? 0);
                    break;

                case 'frikort':
                    $result['trekktype'] = 'frikort';
                    $result['frikortbeloep'] = (float) ($trekk['frikortbeloep'] ?? 0);
                    break;

                case 'trekkpliktig':
                    // "trekkpliktig" means source tax (kildeskatt) - typically 50%
                    $result['trekktype'] = 'kildeskatt';
                    $result['trekkprosent'] = (float) ($trekk['trekkprosent'] ?? $trekk['prosentsats'] ?? 50);
                    break;

                default:
                    Log::warning('Unknown trekkode from Skatteetaten', [
                        'trekkode' => $trekkode,
                        'employee_id' => $settings->id,
                    ]);
                    // Default to percentage deduction if we have a rate
                    if (isset($trekk['prosentsats'])) {
                        $result['trekktype'] = 'prosenttrekk';
                        $result['trekkprosent'] = (float) $trekk['prosentsats'];
                    }
            }

            // Extract validity dates
            $result['gyldig_fra'] = $trekk['gyldigFraOgMed'] ?? null;
            $result['gyldig_til'] = $trekk['gyldigTilOgMed'] ?? null;
        }

        // Update the employee settings with the new tax card data
        $this->updateEmployeeFromTaxCard($settings, $result);

        return $result;
    }

    /**
     * Translate result code from Skatteetaten to Norwegian message.
     */
    protected function translateResultCode(string $code): string
    {
        return match ($code) {
            'skattekortOpplysningerOK' => 'OK',
            'ugyldigPersonidentifikator' => 'Ugyldig personnummer.',
            'personenFinnesIkke' => 'Personen finnes ikke i Folkeregisteret.',
            'skattekortFinnesIkke' => 'Skattekort finnes ikke for inntektsåret.',
            'arbeidsgiverHarIkkeTilgang' => 'Arbeidsgiver har ikke tilgang til skattekortet.',
            'feilIRequest' => 'Feil i forespørselen.',
            'tekniskFeil' => 'Teknisk feil hos Skatteetaten. Prøv igjen senere.',
            default => "Ukjent svar fra Skatteetaten: {$code}",
        };
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
        switch ($taxCardData['trekktype'] ?? null) {
            case 'tabelltrekk':
                $updateData['skatt_type'] = EmployeePayrollSettings::SKATT_TYPE_TABELLTREKK;
                if (! empty($taxCardData['tabellnummer'])) {
                    $updateData['skattetabell'] = $taxCardData['tabellnummer'];
                }
                // Some 6-digit tables also have percentage
                if (! empty($taxCardData['trekkprosent'])) {
                    $updateData['skatteprosent'] = $taxCardData['trekkprosent'];
                }
                break;

            case 'prosenttrekk':
                $updateData['skatt_type'] = EmployeePayrollSettings::SKATT_TYPE_PROSENTTREKK;
                if (! empty($taxCardData['trekkprosent'])) {
                    $updateData['skatteprosent'] = $taxCardData['trekkprosent'];
                }
                break;

            case 'frikort':
                $updateData['skatt_type'] = EmployeePayrollSettings::SKATT_TYPE_FRIKORT;
                if (! empty($taxCardData['frikortbeloep'])) {
                    $updateData['frikort_belop'] = $taxCardData['frikortbeloep'];
                    // Reset used amount when new frikort is received
                    $updateData['frikort_brukt'] = 0;
                }
                break;

            case 'kildeskatt':
                $updateData['skatt_type'] = EmployeePayrollSettings::SKATT_TYPE_KILDESKATT;
                if (! empty($taxCardData['trekkprosent'])) {
                    $updateData['skatteprosent'] = $taxCardData['trekkprosent'];
                }
                break;

            default:
                Log::warning('No trekktype in tax card data', [
                    'employee_id' => $settings->id,
                    'data' => $taxCardData,
                ]);
        }

        // Set validity dates
        if (! empty($taxCardData['gyldig_fra'])) {
            $updateData['skattekort_gyldig_fra'] = $taxCardData['gyldig_fra'];
        }
        if (! empty($taxCardData['gyldig_til'])) {
            $updateData['skattekort_gyldig_til'] = $taxCardData['gyldig_til'];
        }

        $settings->update($updateData);

        Log::info('Employee tax settings updated', [
            'employee_id' => $settings->id,
            'skatt_type' => $updateData['skatt_type'] ?? 'unknown',
        ]);
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

        // Skatteetaten specific error fields
        if (isset($body['feilmelding'])) {
            return $body['feilmelding'];
        }

        if (isset($body['skattekort']['resultatPaaForespoersel'])) {
            return $this->translateResultCode($body['skattekort']['resultatPaaForespoersel']);
        }

        return $body['melding']
            ?? $body['message']
            ?? $body['error']
            ?? $body['kode']
            ?? "HTTP {$response->status()}";
    }

    /**
     * Generate a correlation ID for API request tracking.
     */
    protected function generateCorrelationId(): string
    {
        return sprintf(
            '%s-%s-%s',
            config('app.name', 'konrad'),
            now()->format('YmdHis'),
            bin2hex(random_bytes(4))
        );
    }

    /**
     * Log API error details.
     */
    protected function logApiError($response, EmployeePayrollSettings $settings): void
    {
        $body = $response->json() ?? [];

        Log::error('Skattekort API error', [
            'status' => $response->status(),
            'body' => $body,
            'employee_id' => $settings->id,
            'user_id' => $settings->user_id,
            'endpoint' => $this->getEndpoint().self::API_PATH,
        ]);
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
