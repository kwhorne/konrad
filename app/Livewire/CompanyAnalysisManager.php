<?php

namespace App\Livewire;

use App\Services\CompanyAnalysisService;
use Livewire\Component;

class CompanyAnalysisManager extends Component
{
    public bool $isAnalyzing = false;

    public bool $hasAnalysis = false;

    public ?array $analysis = null;

    public ?array $financialData = null;

    public ?string $error = null;

    public ?string $generatedAt = null;

    public function runAnalysis(): void
    {
        $this->isAnalyzing = true;
        $this->error = null;

        $company = auth()->user()->currentCompany;

        if (! $company) {
            $this->error = 'Ingen bedrift valgt';
            $this->isAnalyzing = false;

            return;
        }

        $service = app(CompanyAnalysisService::class);
        $result = $service->generateAnalysis($company);

        if ($result['success']) {
            $this->analysis = $result['analysis'];
            $this->financialData = $result['financial_data'];
            $this->generatedAt = $result['generated_at'];
            $this->hasAnalysis = true;
        } else {
            $this->error = $result['error'] ?? 'En ukjent feil oppstod';
        }

        $this->isAnalyzing = false;
    }

    public function resetAnalysis(): void
    {
        $this->hasAnalysis = false;
        $this->analysis = null;
        $this->financialData = null;
        $this->error = null;
        $this->generatedAt = null;
    }

    public function getHealthColorProperty(): string
    {
        if (! $this->analysis) {
            return 'zinc';
        }

        $score = $this->analysis['health_score'] ?? 0;

        return match (true) {
            $score >= 80 => 'green',
            $score >= 60 => 'lime',
            $score >= 40 => 'yellow',
            $score >= 20 => 'orange',
            default => 'red',
        };
    }

    public function getStatusColorProperty(): string
    {
        return match ($this->analysis['key_metrics'] ?? []) {
            default => 'zinc',
        };
    }

    public function render()
    {
        return view('livewire.company-analysis-manager');
    }
}
