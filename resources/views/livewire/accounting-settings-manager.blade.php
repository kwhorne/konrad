<div>
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            <flux:heading size="lg" class="mb-2">Regnskapsinnstillinger</flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400 mb-6">
                Konfigurer avdelinger og andre regnskapsrelaterte innstillinger.
            </flux:text>

            <form wire:submit="save" class="space-y-6">
                <div class="space-y-4">
                    <flux:heading size="base">Avdelinger</flux:heading>

                    <flux:field>
                        <flux:switch wire:model.live="departments_enabled" label="Aktiver avdelinger" description="Bruk avdelinger som konteringsdimensjon i hovedbok og på dokumenter." />
                    </flux:field>

                    @if($departments_enabled)
                        <flux:field>
                            <flux:switch wire:model="require_department_on_vouchers" label="Krev avdeling på bilag" description="Krev at avdeling velges på alle bilagslinjer." />
                        </flux:field>

                        <flux:field>
                            <flux:label>Standard avdeling</flux:label>
                            <flux:select wire:model="default_department_id">
                                <flux:select.option value="">Ingen standard</flux:select.option>
                                @foreach($departments as $department)
                                    <flux:select.option value="{{ $department->id }}">{{ $department->code }} - {{ $department->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:description>Standard avdeling for nye bilag og dokumenter.</flux:description>
                        </flux:field>

                        @if($departments->isEmpty())
                            <flux:callout variant="info">
                                <flux:callout.text>
                                    Du ma opprette avdelinger for denne innstillingen skal ha effekt.
                                    <a href="{{ route('settings') }}?tab=departments" class="underline font-medium">Administrer avdelinger</a>
                                </flux:callout.text>
                            </flux:callout>
                        @endif
                    @endif
                </div>

                @if($canManage)
                    <div class="flex justify-end pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button type="submit" variant="primary">
                            Lagre innstillinger
                        </flux:button>
                    </div>
                @endif
            </form>
        </div>
    </flux:card>
</div>
