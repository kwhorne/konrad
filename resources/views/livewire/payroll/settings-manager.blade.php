<div>
    <div class="max-w-2xl">
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-6">Generelle innstillinger</flux:heading>

                <form wire:submit="save" class="space-y-6">
                    <flux:field>
                        <flux:label>Arbeidsgiveravgift-sone</flux:label>
                        <flux:select wire:model="agaZone">
                            @foreach($zones as $zone)
                                <flux:select.option value="{{ $zone->code }}">
                                    {{ $zone->name }} ({{ $zone->rate }}%)
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:description>
                            AGA-sonen bestemmes av hvor bedriften er registrert.
                        </flux:description>
                    </flux:field>

                    <flux:separator />

                    <flux:heading size="sm">Standard verdier</flux:heading>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Standard feriepengeprosent</flux:label>
                            <flux:input type="number" value="10.2" step="0.1" disabled />
                            <flux:description>10.2% (lovbestemt minimum)</flux:description>
                        </flux:field>
                        <flux:field>
                            <flux:label>Standard OTP-prosent</flux:label>
                            <flux:input type="number" value="2.0" step="0.1" disabled />
                            <flux:description>2% (lovbestemt minimum)</flux:description>
                        </flux:field>
                    </div>

                    <flux:separator />

                    <flux:heading size="sm">Grunnbelop (G)</flux:heading>

                    <flux:callout icon="information-circle" variant="info">
                        Grunnbelopet (G) oppdateres arlig 1. mai. Gjeldende G-belop er <strong>130 160 kr</strong> (fra mai 2025).
                    </flux:callout>

                    <div class="flex justify-end pt-4">
                        <flux:button type="submit" variant="primary">Lagre innstillinger</flux:button>
                    </div>
                </form>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 mt-6">
            <div class="p-6">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-4">Integrasjoner</flux:heading>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-white">Skatteetaten API</div>
                            <div class="text-sm text-zinc-500">Hent skattekort og send A-melding</div>
                        </div>
                        <flux:badge color="zinc">Ikke konfigurert</flux:badge>
                    </div>

                    <div class="flex items-center justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-white">Altinn</div>
                            <div class="text-sm text-zinc-500">Motta meldinger fra NAV og Skatteetaten</div>
                        </div>
                        <flux:badge color="zinc">Ikke konfigurert</flux:badge>
                    </div>

                    <div class="flex items-center justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-white">Bankintegrasjon</div>
                            <div class="text-sm text-zinc-500">Generer betalingsfiler (pain.001)</div>
                        </div>
                        <flux:badge color="zinc">Ikke konfigurert</flux:badge>
                    </div>
                </div>
            </div>
        </flux:card>
    </div>
</div>
