<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Component;

class TwoFactorManager extends Component
{
    public bool $showEnableModal = false;

    public bool $showDisableModal = false;

    public bool $showRecoveryCodes = false;

    public string $confirmationCode = '';

    public string $password = '';

    public array $recoveryCodes = [];

    public function startEnabling(): void
    {
        $user = auth()->user();

        // Enable 2FA (generates secret but not confirmed yet)
        app(EnableTwoFactorAuthentication::class)($user);

        $this->showEnableModal = true;
    }

    public function confirmEnabling(): void
    {
        $this->validate([
            'confirmationCode' => ['required', 'string'],
        ], [
            'confirmationCode.required' => 'Bekreftelseskoden er påkrevd.',
        ]);

        $user = auth()->user();

        try {
            app(ConfirmTwoFactorAuthentication::class)($user, $this->confirmationCode);
        } catch (ValidationException $e) {
            $this->addError('confirmationCode', 'Koden er ugyldig. Vennligst prøv igjen.');

            return;
        }

        // Get the recovery codes
        $this->recoveryCodes = $user->fresh()->recoveryCodes();

        $this->showEnableModal = false;
        $this->showRecoveryCodes = true;
        $this->confirmationCode = '';

        $this->dispatch('toast', message: 'Tofaktorautentisering er nå aktivert', variant: 'success');
    }

    public function cancelEnabling(): void
    {
        $user = auth()->user();

        // Reset the 2FA setup if not confirmed
        if (! $user->two_factor_confirmed_at) {
            app(DisableTwoFactorAuthentication::class)($user);
        }

        $this->showEnableModal = false;
        $this->confirmationCode = '';
    }

    public function closeRecoveryCodes(): void
    {
        $this->showRecoveryCodes = false;
        $this->recoveryCodes = [];
    }

    public function showRecoveryCodesModal(): void
    {
        $user = auth()->user();

        if ($user->two_factor_recovery_codes) {
            $this->recoveryCodes = $user->recoveryCodes();
            $this->showRecoveryCodes = true;
        }
    }

    public function regenerateRecoveryCodes(): void
    {
        $user = auth()->user();

        app(GenerateNewRecoveryCodes::class)($user);

        $this->recoveryCodes = $user->fresh()->recoveryCodes();

        $this->dispatch('toast', message: 'Gjenopprettingskoder er regenerert', variant: 'success');
    }

    public function startDisabling(): void
    {
        $this->showDisableModal = true;
        $this->password = '';
    }

    public function confirmDisabling(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Passord er påkrevd.',
        ]);

        $user = auth()->user();

        if (! Hash::check($this->password, $user->password)) {
            $this->addError('password', 'Feil passord.');

            return;
        }

        app(DisableTwoFactorAuthentication::class)($user);

        $this->showDisableModal = false;
        $this->password = '';

        $this->dispatch('toast', message: 'Tofaktorautentisering er deaktivert', variant: 'success');
    }

    public function cancelDisabling(): void
    {
        $this->showDisableModal = false;
        $this->password = '';
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.two-factor-manager', [
            'enabled' => $user->hasEnabledTwoFactorAuthentication(),
            'qrCodeSvg' => $this->showEnableModal && $user->two_factor_secret ? $user->twoFactorQrCodeSvg() : null,
            'secretKey' => $this->showEnableModal && $user->two_factor_secret ? decrypt($user->two_factor_secret) : null,
            'gracePeriodEndsAt' => $user->two_factor_grace_period_ends_at,
            'daysRemaining' => $user->two_factor_grace_days_remaining,
        ]);
    }
}
