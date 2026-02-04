<?php

namespace App\Services\Payroll;

use App\Models\AgaZone;
use App\Models\Company;

class AgaService
{
    /**
     * Calculate arbeidsgiveravgift (employer's national insurance contribution).
     */
    public function calculateArbeidsgiveravgift(float $lonnOgFeriepenger, string $zoneCode): float
    {
        $rate = $this->getZoneRate($zoneCode);

        return round($lonnOgFeriepenger * ($rate / 100), 2);
    }

    /**
     * Get the AGA rate for a zone.
     */
    public function getZoneRate(string $zoneCode): float
    {
        $zone = AgaZone::findByCode($zoneCode);

        return $zone ? $zone->rate : 14.1; // Default to zone 1
    }

    /**
     * Get the zone for a company based on its registered address.
     */
    public function getZoneForCompany(Company $company): AgaZone
    {
        // Default to zone 1 if no zone is configured
        // In a full implementation, this would look up the zone based on
        // the company's kommune (municipality) registration
        return AgaZone::findByCode('1') ?? AgaZone::first();
    }

    /**
     * Get all active zones.
     */
    public function getActiveZones(): \Illuminate\Database\Eloquent\Collection
    {
        return AgaZone::active()->orderBy('code')->get();
    }

    /**
     * Calculate AGA with fribeloep consideration for zone 1a.
     */
    public function calculateAgaWithFribeloep(
        float $lonnOgFeriepenger,
        string $zoneCode,
        float $previouslyUsedFribeloep = 0
    ): array {
        $zone = AgaZone::findByCode($zoneCode);

        if (! $zone) {
            return [
                'aga' => $this->calculateArbeidsgiveravgift($lonnOgFeriepenger, '1'),
                'fribeloep_used' => 0,
            ];
        }

        // For zone 1a, there's a fribeloep (tax-free allowance)
        if ($zone->fribeloep && $zone->fribeloep > 0) {
            $remainingFribeloep = max(0, $zone->fribeloep - $previouslyUsedFribeloep);

            if ($remainingFribeloep > 0) {
                $taxFreeAmount = min($lonnOgFeriepenger, $remainingFribeloep);
                $taxableAmount = $lonnOgFeriepenger - $taxFreeAmount;

                // Difference between zone 1 rate and zone 1a rate for amount within fribeloep
                $zone1Rate = 14.1;
                $savedAmount = $taxFreeAmount * (($zone1Rate - $zone->rate) / 100);

                return [
                    'aga' => round($lonnOgFeriepenger * ($zone->rate / 100), 2),
                    'fribeloep_used' => $taxFreeAmount,
                    'saved_amount' => round($savedAmount, 2),
                ];
            }
        }

        return [
            'aga' => round($lonnOgFeriepenger * ($zone->rate / 100), 2),
            'fribeloep_used' => 0,
        ];
    }
}
