<?php

namespace App\Livewire;

use Livewire\Component;

class TwoFactorReminderBanner extends Component
{
    public function render()
    {
        $user = auth()->user();

        // Don't show if user already has 2FA enabled or is not in grace period
        if (! $user || $user->hasEnabledTwoFactorAuthentication() || ! $user->isInTwoFactorGracePeriod()) {
            return view('livewire.two-factor-reminder-banner', [
                'show' => false,
                'daysRemaining' => null,
            ]);
        }

        return view('livewire.two-factor-reminder-banner', [
            'show' => true,
            'daysRemaining' => $user->two_factor_grace_days_remaining,
        ]);
    }
}
