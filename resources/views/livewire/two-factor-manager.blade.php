<div>
    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div>
                    <flux:heading size="base" class="text-zinc-900 dark:text-white mb-2">
                        Tofaktorautentisering
                    </flux:heading>
                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                        Legg til et ekstra sikkerhetslag ved å kreve en engangskode ved innlogging.
                    </flux:text>
                </div>

                @if($enabled)
                    <flux:badge color="green">Aktivert</flux:badge>
                @else
                    <flux:badge color="zinc">Ikke aktivert</flux:badge>
                @endif
            </div>

            @if(!$enabled && $gracePeriodEndsAt)
                <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                    <div class="flex items-start gap-3">
                        <flux:icon.exclamation-triangle class="h-5 w-5 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" />
                        <div>
                            <flux:text class="font-medium text-amber-800 dark:text-amber-200">
                                @if($daysRemaining !== null && $daysRemaining > 0)
                                    Du har {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'dag' : 'dager' }} igjen til å aktivere tofaktorautentisering.
                                @elseif($daysRemaining === 0)
                                    Siste dag for å aktivere tofaktorautentisering!
                                @else
                                    Tidsfristen for å aktivere tofaktorautentisering har utløpt.
                                @endif
                            </flux:text>
                            <flux:text class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                Aktiver tofaktorautentisering for å beskytte kontoen din.
                            </flux:text>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                @if($enabled)
                    <flux:button wire:click="showRecoveryCodesModal" variant="ghost" icon="key">
                        Vis gjenopprettingskoder
                    </flux:button>
                    <flux:button wire:click="startDisabling" variant="danger" icon="x-circle">
                        Deaktiver
                    </flux:button>
                @else
                    <flux:button wire:click="startEnabling" variant="primary" icon="shield-check">
                        Aktiver tofaktorautentisering
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:card>

    <!-- Enable 2FA Modal -->
    <flux:modal wire:model="showEnableModal" name="enable-2fa-modal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Aktiver tofaktorautentisering</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">
                    Skann QR-koden med en autentiseringsapp som Google Authenticator eller Authy.
                </flux:text>
            </div>

            @if($qrCodeSvg)
                <div class="flex justify-center p-4 bg-white rounded-xl">
                    {!! $qrCodeSvg !!}
                </div>

                <div class="text-center">
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">Eller skriv inn denne koden manuelt:</flux:text>
                    <flux:text class="font-mono text-sm bg-zinc-100 dark:bg-zinc-800 px-3 py-2 rounded-lg select-all">{{ $secretKey }}</flux:text>
                </div>
            @endif

            <div>
                <flux:field>
                    <flux:label>Bekreftelseskode</flux:label>
                    <flux:input
                        wire:model="confirmationCode"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        placeholder="123456"
                        autocomplete="one-time-code"
                    />
                    @error('confirmationCode')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="cancelEnabling" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="confirmEnabling" variant="primary">Bekreft og aktiver</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Recovery Codes Modal -->
    <flux:modal wire:model="showRecoveryCodes" name="recovery-codes-modal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Gjenopprettingskoder</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">
                    Lagre disse kodene et trygt sted. Du kan bruke dem for å logge inn hvis du mister tilgang til autentiseringsappen.
                </flux:text>
            </div>

            <div class="bg-zinc-50 dark:bg-zinc-800 rounded-xl p-4">
                <div class="grid grid-cols-2 gap-2">
                    @foreach($recoveryCodes as $code)
                        <code class="font-mono text-sm text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 px-3 py-2 rounded select-all">{{ $code }}</code>
                    @endforeach
                </div>
            </div>

            <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                <div class="flex items-start gap-3">
                    <flux:icon.exclamation-triangle class="h-5 w-5 text-amber-600 dark:text-amber-400 mt-0.5" />
                    <flux:text class="text-sm text-amber-700 dark:text-amber-300">
                        Hver kode kan bare brukes én gang. Etter at du har brukt en kode, vil den ikke fungere igjen.
                    </flux:text>
                </div>
            </div>

            <div class="flex justify-between">
                <flux:button wire:click="regenerateRecoveryCodes" variant="ghost" wire:confirm="Er du sikker på at du vil generere nye koder? De gamle kodene vil slutte å fungere.">
                    Generer nye koder
                </flux:button>
                <flux:button wire:click="closeRecoveryCodes" variant="primary">Ferdig</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Disable 2FA Modal -->
    <flux:modal wire:model="showDisableModal" name="disable-2fa-modal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Deaktiver tofaktorautentisering</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">
                    Bekreft passordet ditt for å deaktivere tofaktorautentisering.
                </flux:text>
            </div>

            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                <div class="flex items-start gap-3">
                    <flux:icon.exclamation-triangle class="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5" />
                    <flux:text class="text-sm text-red-700 dark:text-red-300">
                        Å deaktivere tofaktorautentisering gjør kontoen din mindre sikker.
                    </flux:text>
                </div>
            </div>

            <div>
                <flux:field>
                    <flux:label>Passord</flux:label>
                    <flux:input
                        wire:model="password"
                        type="password"
                        placeholder="Skriv inn passordet ditt"
                    />
                    @error('password')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="cancelDisabling" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="confirmDisabling" variant="danger">Deaktiver</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
