<div>
    <flux:tabs>
        <flux:tab name="info">Informasjon</flux:tab>
        <flux:tab name="users">Brukere</flux:tab>
        <flux:tab name="modules">Moduler & Fakturering</flux:tab>
        <flux:tab name="stripe">Stripe</flux:tab>

        {{-- Info tab --}}
        <flux:tab.panel name="info">
            <div class="mt-6 space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <flux:field>
                        <flux:label>Selskapsnavn</flux:label>
                        <flux:input wire:model="name" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Org.nr</flux:label>
                        <flux:input wire:model="organizationNumber" />
                        <flux:error name="organizationNumber" />
                    </flux:field>

                    <flux:field>
                        <flux:label>MVA-nr</flux:label>
                        <flux:input wire:model="vatNumber" />
                        <flux:error name="vatNumber" />
                    </flux:field>

                    <flux:field>
                        <flux:label>E-post</flux:label>
                        <flux:input wire:model="email" type="email" />
                        <flux:error name="email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Telefon</flux:label>
                        <flux:input wire:model="phone" />
                        <flux:error name="phone" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Nettside</flux:label>
                        <flux:input wire:model="website" />
                        <flux:error name="website" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Adresse</flux:label>
                        <flux:input wire:model="address" />
                        <flux:error name="address" />
                    </flux:field>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Postnr</flux:label>
                            <flux:input wire:model="postalCode" />
                            <flux:error name="postalCode" />
                        </flux:field>

                        <flux:field>
                            <flux:label>By</flux:label>
                            <flux:input wire:model="city" />
                            <flux:error name="city" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Land</flux:label>
                        <flux:input wire:model="country" />
                        <flux:error name="country" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Status</flux:label>
                        <div class="flex items-center gap-3">
                            <flux:switch wire:model="isActive" />
                            <flux:text>{{ $isActive ? 'Aktiv' : 'Inaktiv' }}</flux:text>
                        </div>
                    </flux:field>
                </div>

                <div class="flex justify-end">
                    <flux:button wire:click="save" variant="primary">Lagre endringer</flux:button>
                </div>
            </div>
        </flux:tab.panel>

        {{-- Users tab --}}
        <flux:tab.panel name="users">
            <div class="mt-6">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Navn</flux:table.column>
                        <flux:table.column>E-post</flux:table.column>
                        <flux:table.column>Rolle</flux:table.column>
                        <flux:table.column>Ble med</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse($company->users as $user)
                            <flux:table.row wire:key="user-{{ $user->id }}">
                                <flux:table.cell>
                                    <flux:text class="font-medium">{{ $user->name }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:text class="text-sm">{{ $user->email }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge color="{{ $user->pivot->role === 'owner' ? 'yellow' : 'zinc' }}">
                                        {{ ucfirst($user->pivot->role) }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:text class="text-sm text-zinc-500">
                                        {{ $user->pivot->joined_at ? \Carbon\Carbon::parse($user->pivot->joined_at)->format('d.m.Y') : '—' }}
                                    </flux:text>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="text-center py-8">
                                    <flux:text class="text-zinc-400">Ingen brukere</flux:text>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:tab.panel>

        {{-- Modules & Billing tab --}}
        <flux:tab.panel name="modules">
            <div class="mt-6 space-y-6">
                <div class="flex items-center justify-between">
                    <flux:card class="bg-white dark:bg-zinc-900 flex-1 mr-4">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-green-100 dark:bg-green-900/30">
                                <flux:icon.banknotes class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Månedlig kostnad</flux:text>
                                <flux:heading size="xl">{{ number_format($totalMonthlyOre / 100, 0, ',', ' ') }} kr/mnd</flux:heading>
                            </div>
                        </div>
                    </flux:card>

                    <flux:button wire:click="openModuleModal" variant="outline" icon="puzzle-piece">
                        Administrer moduler
                    </flux:button>
                </div>

                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Modul</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Aktivert av</flux:table.column>
                        <flux:table.column>Aktivert</flux:table.column>
                        <flux:table.column>Utløper</flux:table.column>
                        <flux:table.column>Stripe-status</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse($companyModules as $cm)
                            <flux:table.row wire:key="cm-{{ $cm->id }}">
                                <flux:table.cell>
                                    <flux:text class="font-medium">{{ $cm->module?->name ?? '—' }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($cm->isActive())
                                        <flux:badge color="green">Aktiv</flux:badge>
                                    @elseif($cm->isExpired())
                                        <flux:badge color="red">Utløpt</flux:badge>
                                    @else
                                        <flux:badge color="zinc">Inaktiv</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:text class="text-sm">{{ $cm->enabled_by ?? '—' }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:text class="text-sm text-zinc-500">
                                        {{ $cm->enabled_at?->format('d.m.Y') ?? '—' }}
                                    </flux:text>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:text class="text-sm text-zinc-500">
                                        {{ $cm->expires_at?->format('d.m.Y') ?? '—' }}
                                    </flux:text>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($cm->stripe_subscription_status)
                                        <flux:badge color="{{ $cm->stripe_subscription_status === 'active' ? 'green' : 'yellow' }}">
                                            {{ $cm->stripe_subscription_status }}
                                        </flux:badge>
                                    @else
                                        <flux:text class="text-sm text-zinc-400">—</flux:text>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="6" class="text-center py-8">
                                    <flux:text class="text-zinc-400">Ingen moduler aktivert</flux:text>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:tab.panel>

        {{-- Stripe tab --}}
        <flux:tab.panel name="stripe">
            <div class="mt-6 space-y-6">
                <flux:card class="bg-white dark:bg-zinc-900">
                    <flux:heading size="lg" level="3" class="mb-4">Betalingsinformasjon</flux:heading>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <flux:text class="text-sm text-zinc-500 mb-1">Stripe ID</flux:text>
                            <flux:text class="font-mono text-sm">{{ $company->stripe_id ?? '—' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 mb-1">Betalingsmetode</flux:text>
                            <flux:text class="text-sm">{{ $company->pm_type ? strtoupper($company->pm_type) : '—' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 mb-1">Kortnummer (siste 4)</flux:text>
                            <flux:text class="font-mono text-sm">{{ $company->pm_last_four ? '•••• '.$company->pm_last_four : '—' }}</flux:text>
                        </div>
                    </div>
                </flux:card>

                @if($subscriptions->isNotEmpty())
                    <flux:card class="bg-white dark:bg-zinc-900">
                        <flux:heading size="lg" level="3" class="mb-4">Abonnementer</flux:heading>
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Navn</flux:table.column>
                                <flux:table.column>Stripe ID</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column>Opprettet</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach($subscriptions as $subscription)
                                    <flux:table.row wire:key="sub-{{ $subscription->id }}">
                                        <flux:table.cell>
                                            <flux:text class="font-medium">{{ $subscription->name }}</flux:text>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:text class="font-mono text-sm">{{ $subscription->stripe_id }}</flux:text>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge color="{{ $subscription->stripe_status === 'active' ? 'green' : 'yellow' }}">
                                                {{ $subscription->stripe_status }}
                                            </flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:text class="text-sm text-zinc-500">
                                                {{ $subscription->created_at->format('d.m.Y') }}
                                            </flux:text>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </flux:card>
                @else
                    <flux:card class="bg-white dark:bg-zinc-900">
                        <flux:text class="text-zinc-400">Ingen Stripe-abonnementer</flux:text>
                    </flux:card>
                @endif
            </div>
        </flux:tab.panel>
    </flux:tabs>

    {{-- Module Management Modal --}}
    <flux:modal wire:model="showModuleModal" name="module-detail-modal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Administrer moduler</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">
                    Aktiver eller deaktiver moduler og sett utløpsdato
                </flux:text>
            </div>

            <div class="space-y-4">
                <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Premium moduler</flux:text>

                @foreach($premiumModules as $module)
                    <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <flux:text class="font-medium">{{ $module->name }}</flux:text>
                                <flux:text class="text-sm text-zinc-500">{{ $module->description }}</flux:text>
                                <flux:text class="text-sm text-violet-600 dark:text-violet-400 mt-1">{{ $module->price_formatted }}</flux:text>
                            </div>
                            <flux:switch wire:model="moduleStates.{{ $module->id }}" />
                        </div>
                        @if(!empty($moduleStates[$module->id]))
                            <div class="mt-3">
                                <flux:field>
                                    <flux:label class="text-sm">Utløpsdato (valgfritt)</flux:label>
                                    <flux:input
                                        type="date"
                                        wire:model="moduleExpiries.{{ $module->id }}"
                                        placeholder="Ingen utløp"
                                    />
                                </flux:field>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeModuleModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="saveModules" variant="primary">Lagre endringer</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
