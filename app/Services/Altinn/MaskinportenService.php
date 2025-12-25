<?php

namespace App\Services\Altinn;

use App\Models\AltinnCertificate;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MaskinportenService
{
    private string $environment;

    private array $config;

    public function __construct()
    {
        $this->environment = config('altinn.environment', 'test');
        $this->config = config('altinn.maskinporten', []);
    }

    /**
     * Get an access token from Maskinporten for the given scope
     */
    public function getAccessToken(string $scope): string
    {
        $cacheKey = "maskinporten_token_{$scope}";

        return Cache::remember($cacheKey, 90, function () use ($scope) {
            return $this->requestAccessToken($scope);
        });
    }

    /**
     * Request a new access token from Maskinporten
     */
    private function requestAccessToken(string $scope): string
    {
        $jwt = $this->createJwtAssertion($scope);

        $tokenEndpoint = $this->getTokenEndpoint();

        $response = Http::asForm()->post($tokenEndpoint, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (! $response->successful()) {
            Log::error('Maskinporten token request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException(
                'Kunne ikke hente token fra Maskinporten: '.$response->body()
            );
        }

        $data = $response->json();

        return $data['access_token'];
    }

    /**
     * Create a JWT assertion for Maskinporten authentication
     */
    private function createJwtAssertion(string $scope): string
    {
        $certificate = $this->getCertificate();

        if (! $certificate) {
            throw new \RuntimeException(
                'Ingen gyldig virksomhetssertifikat funnet. Last opp et sertifikat i administrasjonen.'
            );
        }

        $privateKey = $certificate->private_key;
        $passphrase = $certificate->passphrase;

        if (! $privateKey) {
            // Try loading from file path
            if ($certificate->file_path && file_exists($certificate->file_path)) {
                $privateKey = file_get_contents($certificate->file_path);
            } else {
                throw new \RuntimeException('Privat nøkkel ikke funnet for sertifikatet.');
            }
        }

        // Parse the private key
        $key = openssl_pkey_get_private($privateKey, $passphrase ?? '');

        if (! $key) {
            throw new \RuntimeException('Kunne ikke lese privat nøkkel. Sjekk passord.');
        }

        $now = time();

        $payload = [
            'aud' => $this->getIssuer(),
            'iss' => $this->getClientId(),
            'scope' => $scope,
            'iat' => $now,
            'exp' => $now + 120, // 2 minutes
            'jti' => Str::uuid()->toString(),
        ];

        // Get certificate thumbprint for kid header
        $certContent = $certificate->certificate;
        $certData = openssl_x509_parse($certContent);
        $kid = $certData['serialNumberHex'] ?? hash('sha256', $certContent);

        return JWT::encode($payload, $key, 'RS256', $kid);
    }

    /**
     * Get the active certificate
     */
    private function getCertificate(): ?AltinnCertificate
    {
        return AltinnCertificate::getActiveCertificate();
    }

    /**
     * Get the Maskinporten issuer URL
     */
    private function getIssuer(): string
    {
        return $this->config['issuer']
            ?? config("altinn.endpoints.{$this->environment}.maskinporten");
    }

    /**
     * Get the Maskinporten token endpoint
     */
    private function getTokenEndpoint(): string
    {
        return $this->config['token_endpoint']
            ?? config("altinn.endpoints.{$this->environment}.maskinporten").'/token';
    }

    /**
     * Get the client ID
     */
    private function getClientId(): string
    {
        $clientId = $this->config['client_id'] ?? null;

        if (! $clientId) {
            throw new \RuntimeException(
                'Maskinporten client_id er ikke konfigurert. Sett MASKINPORTEN_CLIENT_ID i .env'
            );
        }

        return $clientId;
    }

    /**
     * Get the scope for a specific submission type
     */
    public function getScopeForType(string $submissionType): string
    {
        $scopes = $this->config['scopes'] ?? [];

        return $scopes[$submissionType]
            ?? throw new \RuntimeException("Ukjent innsendingstype: {$submissionType}");
    }

    /**
     * Check if Maskinporten is properly configured
     */
    public function isConfigured(): bool
    {
        return ! empty($this->config['client_id'])
            && AltinnCertificate::hasCertificate();
    }

    /**
     * Get configuration status for display
     */
    public function getConfigurationStatus(): array
    {
        $certificate = AltinnCertificate::getActiveCertificate();

        return [
            'environment' => $this->environment,
            'client_id_configured' => ! empty($this->config['client_id']),
            'certificate_active' => $certificate !== null,
            'certificate_valid' => $certificate?->isValid() ?? false,
            'certificate_expiring_soon' => $certificate?->isExpiringSoon() ?? false,
            'certificate_expires_at' => $certificate?->valid_to,
            'is_ready' => $this->isConfigured(),
        ];
    }

    /**
     * Clear cached tokens
     */
    public function clearTokenCache(): void
    {
        $scopes = $this->config['scopes'] ?? [];

        foreach ($scopes as $scope) {
            Cache::forget("maskinporten_token_{$scope}");
        }
    }
}
