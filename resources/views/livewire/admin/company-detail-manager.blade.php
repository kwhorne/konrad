<div>
    <flux:tab.group>
        <flux:tabs>
            <flux:tab name="info">Informasjon</flux:tab>
            <flux:tab name="users">Brukere</flux:tab>
            <flux:tab name="modules">Moduler & Fakturering</flux:tab>
            <flux:tab name="billing">Faktura</flux:tab>
        </flux:tabs>

        {{-- Info tab --}}
        <flux:tab.panel name="info" class="pt-6">
            <div class="flex gap-8 items-start">
                {{-- Main form (2/3) --}}
                <div class="min-w-0 flex-[2] space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <flux:field class="col-span-2">
                            <flux:label>Selskapsnavn</flux:label>
                            <flux:input wire:model="name" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Org.nr</flux:label>
                            <flux:input wire:model="organizationNumber" placeholder="f.eks. 123 456 789" />
                            <flux:error name="organizationNumber" />
                        </flux:field>

                        <flux:field>
                            <flux:label>MVA-nr</flux:label>
                            <flux:input wire:model="vatNumber" placeholder="f.eks. NO123456789MVA" />
                            <flux:error name="vatNumber" />
                        </flux:field>

                        <flux:field>
                            <flux:label>E-post</flux:label>
                            <flux:input wire:model="email" type="email" />
                            <flux:error name="email" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Faktura-e-post</flux:label>
                            <flux:input wire:model="billingEmail" type="email" placeholder="Bruker vanlig e-post hvis tom" />
                            <flux:error name="billingEmail" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Telefon</flux:label>
                            <flux:input wire:model="phone" type="tel" />
                            <flux:error name="phone" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Nettside</flux:label>
                            <flux:input wire:model="website" type="url" placeholder="https://" />
                            <flux:error name="website" />
                        </flux:field>

                        <flux:field class="col-span-2">
                            <flux:label>Adresse</flux:label>
                            <flux:input wire:model="address" />
                            <flux:error name="address" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Postnummer</flux:label>
                            <flux:input wire:model="postalCode" />
                            <flux:error name="postalCode" />
                        </flux:field>

                        <flux:field>
                            <flux:label>By</flux:label>
                            <flux:input wire:model="city" />
                            <flux:error name="city" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Land</flux:label>
                            <flux:input wire:model="country" />
                            <flux:error name="country" />
                        </flux:field>
                    </div>

                    <div class="flex justify-end">
                        <flux:button wire:click="save" variant="primary">Lagre endringer</flux:button>
                    </div>
                </div>

                {{-- Side panel (1/3) --}}
                <div class="w-72 shrink-0 space-y-4">
                    {{-- Status --}}
                    <flux:card class="p-4">
                        <flux:text class="text-xs font-semibold tracking-wide text-zinc-500 dark:text-zinc-400 uppercase mb-3">Status</flux:text>
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text class="font-medium">{{ $isActive ? 'Aktiv' : 'Inaktiv' }}</flux:text>
                                <flux:text class="text-sm text-zinc-500">{{ $isActive ? 'Selskapet har tilgang til plattformen' : 'Tilgang er sperret' }}</flux:text>
                            </div>
                            <flux:switch wire:model="isActive" />
                        </div>
                    </flux:card>

                    {{-- Meta --}}
                    <flux:card class="p-4">
                        <flux:text class="text-xs font-semibold tracking-wide text-zinc-500 dark:text-zinc-400 uppercase mb-3">Detaljer</flux:text>
                        <dl class="space-y-2">
                            <div class="flex justify-between gap-2">
                                <dt class="text-sm text-zinc-500 shrink-0">Selskaps-ID</dt>
                                <dd class="text-sm font-mono font-medium text-zinc-700 dark:text-zinc-300">#{{ $company->id }}</dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-sm text-zinc-500 shrink-0">Opprettet</dt>
                                <dd class="text-sm text-zinc-700 dark:text-zinc-300">{{ $company->created_at->format('d.m.Y') }}</dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-sm text-zinc-500 shrink-0">Oppdatert</dt>
                                <dd class="text-sm text-zinc-700 dark:text-zinc-300">{{ $company->updated_at->format('d.m.Y') }}</dd>
                            </div>
                        </dl>
                    </flux:card>

                    {{-- Overview --}}
                    <flux:card class="p-4">
                        <flux:text class="text-xs font-semibold tracking-wide text-zinc-500 dark:text-zinc-400 uppercase mb-3">Oversikt</flux:text>
                        <dl class="space-y-2">
                            <div class="flex justify-between gap-2">
                                <dt class="text-sm text-zinc-500 shrink-0">Brukere</dt>
                                <dd class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $company->users->count() }}</dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-sm text-zinc-500 shrink-0">Aktive moduler</dt>
                                <dd class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ $companyModules->filter(fn($cm) => $cm->isActive())->count() }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-sm text-zinc-500 shrink-0">Månedlig kostnad</dt>
                                <dd class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ number_format($totalMonthlyOre / 100, 0, ',', ' ') }} kr/mnd
                                </dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-sm text-zinc-500 shrink-0">Utestående</dt>
                                <dd class="text-sm font-medium {{ $outstandingOre > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-700 dark:text-zinc-300' }}">
                                    {{ number_format($outstandingOre / 100, 0, ',', ' ') }} kr
                                </dd>
                            </div>
                        </dl>
                    </flux:card>
                </div>
            </div>
        </flux:tab.panel>

        {{-- Users tab --}}
        <flux:tab.panel name="users" class="pt-6">
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
        </flux:tab.panel>

        {{-- Modules & Billing tab --}}
        <flux:tab.panel name="modules" class="pt-6">
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <flux:card class="bg-white dark:bg-zinc-900 flex-1">
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
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse($companyModules as $cm)
                            <flux:table.row wire:key="cm-{{ $cm->id }}">
                                <flux:table.cell>
                                    <div>
                                        <flux:text class="font-medium">{{ $cm->module?->name ?? '—' }}</flux:text>
                                        @if($cm->module?->price_monthly)
                                            <flux:text class="text-sm text-zinc-500">{{ $cm->module->price_formatted }}</flux:text>
                                        @endif
                                    </div>
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
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center py-8">
                                    <flux:text class="text-zinc-400">Ingen moduler aktivert</flux:text>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:tab.panel>

        {{-- Billing tab --}}
        <flux:tab.panel name="billing" class="pt-6">
            <div class="space-y-6">
                {{-- Summary cards --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <flux:card class="bg-white dark:bg-zinc-900">
                        <flux:text class="text-sm text-zinc-500 mb-1">Faktura-e-post</flux:text>
                        <flux:text class="font-medium">{{ $company->effective_billing_email ?? '—' }}</flux:text>
                        @if(!$company->billing_email)
                            <flux:text class="text-xs text-zinc-400 mt-1">Ingen separat faktura-e-post</flux:text>
                        @endif
                    </flux:card>

                    <flux:card class="bg-white dark:bg-zinc-900">
                        <flux:text class="text-sm text-zinc-500 mb-1">Utestående</flux:text>
                        <flux:heading size="xl" class="{{ $outstandingOre > 0 ? 'text-red-600 dark:text-red-400' : '' }}">
                            {{ number_format($outstandingOre / 100, 0, ',', ' ') }} kr
                        </flux:heading>
                    </flux:card>

                    <flux:card class="bg-white dark:bg-zinc-900">
                        <flux:text class="text-sm text-zinc-500 mb-1">Månedlig beløp (moduler)</flux:text>
                        <flux:heading size="xl">{{ number_format($totalMonthlyOre / 100, 0, ',', ' ') }} kr/mnd</flux:heading>
                    </flux:card>
                </div>

                {{-- Invoice list --}}
                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg" level="3">Fakturaer</flux:heading>
                        <flux:button wire:click="openInvoiceModal" variant="primary" size="sm" icon="plus">
                            Ny faktura
                        </flux:button>
                    </div>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Fakturanr.</flux:table.column>
                            <flux:table.column>Beskrivelse</flux:table.column>
                            <flux:table.column>Beløp</flux:table.column>
                            <flux:table.column>Forfallsdato</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Sendt</flux:table.column>
                            <flux:table.column>Betalt</flux:table.column>
                            <flux:table.column></flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse($invoices as $invoice)
                                <flux:table.row wire:key="inv-{{ $invoice->id }}">
                                    <flux:table.cell>
                                        <flux:text class="font-mono text-sm">{{ $invoice->invoice_number }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text class="text-sm">{{ $invoice->description }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text class="font-medium">{{ $invoice->amount_formatted }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text class="text-sm {{ $invoice->isOverdue() ? 'text-red-600 dark:text-red-400 font-medium' : 'text-zinc-500' }}">
                                            {{ $invoice->due_date->format('d.m.Y') }}
                                        </flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if($invoice->isPaid())
                                            <flux:badge color="green">Betalt</flux:badge>
                                        @elseif($invoice->isOverdue())
                                            <flux:badge color="red">Forfalt</flux:badge>
                                        @else
                                            <flux:badge color="yellow">Utestående</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text class="text-sm text-zinc-500">
                                            {{ $invoice->sent_at?->format('d.m.Y') ?? '—' }}
                                        </flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text class="text-sm text-zinc-500">
                                            {{ $invoice->paid_at?->format('d.m.Y') ?? '—' }}
                                        </flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                            <flux:menu>
                                                <flux:menu.item
                                                    wire:click="sendInvoice({{ $invoice->id }})"
                                                    wire:loading.attr="disabled"
                                                    icon="paper-airplane"
                                                >
                                                    Send på e-post
                                                </flux:menu.item>
                                                <flux:menu.separator />
                                                @if($invoice->isPaid())
                                                    <flux:menu.item wire:click="markAsUnpaid({{ $invoice->id }})" icon="x-circle">
                                                        Angre betalt
                                                    </flux:menu.item>
                                                @else
                                                    <flux:menu.item wire:click="markAsPaid({{ $invoice->id }})" icon="check-circle">
                                                        Merk betalt
                                                    </flux:menu.item>
                                                @endif
                                            </flux:menu>
                                        </flux:dropdown>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="7" class="text-center py-8">
                                        <flux:text class="text-zinc-400">Ingen fakturaer registrert</flux:text>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </flux:card>
            </div>
        </flux:tab.panel>
    </flux:tab.group>

    {{-- Create Invoice Modal --}}
    <flux:modal wire:model="showInvoiceModal" name="create-invoice-modal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Ny faktura</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">
                    Opprett faktura for {{ $name }}
                </flux:text>
            </div>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Beskrivelse</flux:label>
                    <flux:textarea wire:model="invoiceDescription" rows="2" placeholder="f.eks. Månedlig lisens — Konrad Office, mars 2026" />
                    <flux:error name="invoiceDescription" />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Beløp (kr)</flux:label>
                        <flux:input wire:model="invoiceAmount" type="number" min="1" step="0.01" placeholder="0" />
                        <flux:error name="invoiceAmount" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Forfallsdato</flux:label>
                        <flux:input wire:model="invoiceDueDate" type="date" />
                        <flux:error name="invoiceDueDate" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Notater (internt)</flux:label>
                    <flux:textarea wire:model="invoiceNotes" rows="2" placeholder="Valgfrie interne notater..." />
                    <flux:error name="invoiceNotes" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeInvoiceModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="createInvoice" variant="primary">Opprett faktura</flux:button>
            </div>
        </div>
    </flux:modal>

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
