<?php

namespace App\Services\Altinn;

use App\Models\AltinnSubmission;
use App\Models\CompanySetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AltinnApiClient
{
    private string $environment;

    private string $baseUrl;

    private MaskinportenService $maskinporten;

    public function __construct(MaskinportenService $maskinporten)
    {
        $this->maskinporten = $maskinporten;
        $this->environment = config('altinn.environment', 'test');
        $this->baseUrl = config("altinn.endpoints.{$this->environment}.altinn");
    }

    /**
     * Create an authenticated HTTP client for a specific submission type
     */
    private function client(string $submissionType): PendingRequest
    {
        $scope = $this->maskinporten->getScopeForType($submissionType);
        $token = $this->maskinporten->getAccessToken($scope);

        return Http::baseUrl($this->baseUrl)
            ->withToken($token)
            ->acceptJson()
            ->withHeaders([
                'Content-Type' => 'application/json',
            ]);
    }

    /**
     * Get the organization number for the current company
     */
    private function getOrganizationNumber(): string
    {
        $company = CompanySetting::current();

        if (! $company || ! $company->organization_number) {
            throw new \RuntimeException(
                'Organisasjonsnummer er ikke konfigurert. Oppdater firmainnstillinger.'
            );
        }

        // Remove spaces and dashes from org number
        return preg_replace('/[\s-]/', '', $company->organization_number);
    }

    /**
     * Create a new Altinn instance for a submission
     */
    public function createInstance(string $submissionType, array $prefill = []): array
    {
        $appId = config("altinn.services.{$submissionType}.app_id");

        if (! $appId) {
            throw new \RuntimeException("App ID ikke konfigurert for {$submissionType}");
        }

        $orgNumber = $this->getOrganizationNumber();

        $response = $this->client($submissionType)
            ->post('/storage/api/v1/instances', [
                'appId' => $appId,
                'instanceOwner' => [
                    'organisationNumber' => $orgNumber,
                ],
                'prefill' => $prefill,
            ]);

        if (! $response->successful()) {
            Log::error('Failed to create Altinn instance', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException(
                'Kunne ikke opprette Altinn-innsending: '.$response->body()
            );
        }

        return $response->json();
    }

    /**
     * Upload data to an Altinn instance
     */
    public function uploadData(
        string $instanceId,
        string $submissionType,
        string $dataType,
        string $content,
        string $contentType = 'application/xml'
    ): array {
        $response = $this->client($submissionType)
            ->withBody($content, $contentType)
            ->post("/storage/api/v1/instances/{$instanceId}/data?dataType={$dataType}");

        if (! $response->successful()) {
            Log::error('Failed to upload data to Altinn', [
                'instance_id' => $instanceId,
                'data_type' => $dataType,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException(
                'Kunne ikke laste opp data til Altinn: '.$response->body()
            );
        }

        return $response->json();
    }

    /**
     * Submit an instance for processing
     */
    public function submitInstance(string $instanceId, string $submissionType): array
    {
        $response = $this->client($submissionType)
            ->put("/storage/api/v1/instances/{$instanceId}/process/next");

        if (! $response->successful()) {
            Log::error('Failed to submit Altinn instance', [
                'instance_id' => $instanceId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException(
                'Kunne ikke sende inn til Altinn: '.$response->body()
            );
        }

        return $response->json();
    }

    /**
     * Get the status of an instance
     */
    public function getInstanceStatus(string $instanceId, string $submissionType): array
    {
        $response = $this->client($submissionType)
            ->get("/storage/api/v1/instances/{$instanceId}");

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Kunne ikke hente status fra Altinn: '.$response->body()
            );
        }

        return $response->json();
    }

    /**
     * Get validation errors for an instance
     */
    public function getValidationErrors(string $instanceId, string $submissionType): array
    {
        $response = $this->client($submissionType)
            ->get("/storage/api/v1/instances/{$instanceId}/validate");

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Kunne ikke validere innsending: '.$response->body()
            );
        }

        return $response->json();
    }

    /**
     * Complete submission and return to model
     */
    public function processSubmission(AltinnSubmission $submission, string $xmlContent): void
    {
        try {
            $submission->markAsValidating();

            // Create instance
            $instance = $this->createInstance($submission->submission_type);
            $instanceId = $instance['id'];

            // Upload data
            $dataType = config("altinn.services.{$submission->submission_type}.data_type");
            $this->uploadData($instanceId, $submission->submission_type, $dataType, $xmlContent);

            // Validate
            $validation = $this->getValidationErrors($instanceId, $submission->submission_type);

            if (! empty($validation['errors'])) {
                $submission->markAsRejected(
                    'Valideringsfeil fra Altinn',
                    $validation['errors']
                );

                return;
            }

            // Submit
            $result = $this->submitInstance($instanceId, $submission->submission_type);

            // Update submission with instance ID
            $submission->markAsSubmitted(
                $instanceId,
                $result['process']['currentTask']['altinnTaskType'] ?? null
            );

            Log::info('Altinn submission completed', [
                'submission_id' => $submission->id,
                'instance_id' => $instanceId,
            ]);

        } catch (\Exception $e) {
            Log::error('Altinn submission failed', [
                'submission_id' => $submission->id,
                'error' => $e->getMessage(),
            ]);

            $submission->markAsError($e->getMessage());

            throw $e;
        }
    }

    /**
     * Poll for submission status and update model
     */
    public function pollStatus(AltinnSubmission $submission): void
    {
        if (! $submission->altinn_instance_id) {
            return;
        }

        try {
            $status = $this->getInstanceStatus(
                $submission->altinn_instance_id,
                $submission->submission_type
            );

            $processStatus = $status['status'] ?? [];

            // Check if completed
            if (($processStatus['isCompleted'] ?? false) === true) {
                $submission->markAsAccepted($status['data']['archiveReference'] ?? null);

                return;
            }

            // Check for feedback/rejection
            if (isset($processStatus['feedback'])) {
                $feedback = $processStatus['feedback'];

                if (($feedback['status'] ?? '') === 'rejected') {
                    $submission->markAsRejected(
                        $feedback['message'] ?? 'Avvist av Altinn',
                        $feedback['errors'] ?? null
                    );
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to poll Altinn status', [
                'submission_id' => $submission->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if the API client is ready to use
     */
    public function isReady(): bool
    {
        return $this->maskinporten->isConfigured();
    }

    /**
     * Get the current environment
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Check if running in test mode
     */
    public function isTestMode(): bool
    {
        return $this->environment === 'test';
    }
}
