<?php

namespace App\Services\Payroll;

use App\Models\EmployeePayrollSettings;

class OtpService
{
    /**
     * Grunnbelopet (G) per mai 2025.
     */
    public const G_BELOP = 130160;

    /**
     * Maximum number of G for OTP calculation.
     */
    public const OTP_MAX_G = 12;

    /**
     * Minimum OTP percentage (legal requirement).
     */
    public const MIN_OTP_PROSENT = 2.0;

    /**
     * Maximum OTP percentage.
     */
    public const MAX_OTP_PROSENT = 7.0;

    /**
     * Calculate OTP (obligatorisk tjenestepensjon) contribution.
     */
    public function calculateOtp(float $aarslonn, EmployeePayrollSettings $settings): float
    {
        if (! $settings->otp_enabled) {
            return 0;
        }

        // Get the OTP basis (capped at 12G)
        $basis = $this->getOtpBasis($aarslonn);

        // Calculate the OTP amount
        $prosent = $settings->otp_prosent ?? self::MIN_OTP_PROSENT;

        return round($basis * ($prosent / 100), 2);
    }

    /**
     * Calculate monthly OTP contribution.
     */
    public function calculateMonthlyOtp(float $maanedslonn, EmployeePayrollSettings $settings): float
    {
        if (! $settings->otp_enabled) {
            return 0;
        }

        // Estimate yearly salary for the cap calculation
        $estimatedAarslonn = $maanedslonn * 12;

        // Get the monthly OTP basis
        $monthlyBasis = $this->getOtpBasis($estimatedAarslonn) / 12;

        $prosent = $settings->otp_prosent ?? self::MIN_OTP_PROSENT;

        return round($monthlyBasis * ($prosent / 100), 2);
    }

    /**
     * Get the OTP basis (salary up to 12G).
     */
    public function getOtpBasis(float $aarslonn): float
    {
        $maxBasis = self::G_BELOP * self::OTP_MAX_G;

        return min($aarslonn, $maxBasis);
    }

    /**
     * Check if salary exceeds OTP cap.
     */
    public function exceedsOtpCap(float $aarslonn): bool
    {
        return $aarslonn > (self::G_BELOP * self::OTP_MAX_G);
    }

    /**
     * Get the current G-belop.
     */
    public function getGBelop(): int
    {
        return self::G_BELOP;
    }

    /**
     * Get the maximum OTP basis.
     */
    public function getMaxOtpBasis(): float
    {
        return self::G_BELOP * self::OTP_MAX_G;
    }

    /**
     * Calculate employer's total OTP cost for a period.
     *
     * @param  array  $employees  Array of ['aarslonn' => float, 'otp_prosent' => float]
     */
    public function calculateTotalOtpCost(array $employees): float
    {
        $total = 0;

        foreach ($employees as $employee) {
            $basis = $this->getOtpBasis($employee['aarslonn']);
            $prosent = $employee['otp_prosent'] ?? self::MIN_OTP_PROSENT;
            $total += $basis * ($prosent / 100);
        }

        return round($total, 2);
    }
}
