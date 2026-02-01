<div>
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-indigo-100 dark:bg-indigo-900/30">
                    <flux:icon.globe-alt class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Totalt IP-adresser</flux:text>
                    <flux:heading size="xl">{{ $totalEntries }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-green-100 dark:bg-green-900/30">
                    <flux:icon.check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Aktive</flux:text>
                    <flux:heading size="xl">{{ $activeEntries }}</flux:heading>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Info Box --}}
    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
        <div class="flex items-start gap-3">
            <flux:icon.information-circle class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" />
            <div>
                <flux:text class="font-medium text-blue-800 dark:text-blue-200">Om IP-whitelist</flux:text>
                <flux:text class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                    Brukere som logger inn fra whitelistede IP-adresser slipper å bruke tofaktorautentisering.
                    Dette er nyttig for kontorlokaler eller VPN-tilkoblinger.
                </flux:text>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk etter IP-adresse eller beskrivelse..." icon="magnifying-glass" />
        </div>
        <div>
            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                Ny IP-adresse
            </flux:button>
        </div>
    </div>

    {{-- IP Whitelist Table --}}
    <flux:card class="bg-white dark:bg-zinc-900">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>IP-adresse</flux:table.column>
                <flux:table.column>CIDR Range</flux:table.column>
                <flux:table.column>Beskrivelse</flux:table.column>
                <flux:table.column>Opprettet av</flux:table.column>
                <flux:table.column>Opprettet</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($entries as $entry)
                    <flux:table.row wire:key="entry-{{ $entry->id }}">
                        <flux:table.cell>
                            <flux:badge color="zinc" class="font-mono">{{ $entry->ip_address }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($entry->cidr_range)
                                <flux:badge color="zinc" class="font-mono text-xs">{{ $entry->cidr_range }}</flux:badge>
                            @else
                                <flux:text class="text-zinc-400">-</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($entry->description)
                                <flux:text class="text-sm">{{ $entry->description }}</flux:text>
                            @else
                                <flux:text class="text-zinc-400">-</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($entry->creator)
                                <flux:text class="text-sm">{{ $entry->creator->name }}</flux:text>
                            @else
                                <flux:text class="text-zinc-400">-</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:text class="text-sm text-zinc-500">{{ $entry->created_at->format('d.m.Y H:i') }}</flux:text>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($entry->is_active)
                                <flux:badge color="green">Aktiv</flux:badge>
                            @else
                                <flux:badge color="red">Inaktiv</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>
                                    <flux:menu.item wire:click="openEditModal({{ $entry->id }})" icon="pencil">
                                        Rediger
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="toggleActive({{ $entry->id }})" icon="{{ $entry->is_active ? 'x-circle' : 'check-circle' }}">
                                        {{ $entry->is_active ? 'Deaktiver' : 'Aktiver' }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item wire:click="confirmDelete({{ $entry->id }})" icon="trash" variant="danger">
                                        Slett
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8">
                            <flux:text class="text-zinc-500">Ingen IP-adresser i whitelist</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($entries->hasPages())
            <div class="mt-4 px-4 pb-4">
                {{ $entries->links() }}
            </div>
        @endif
    </flux:card>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" name="ip-whitelist-form-modal">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingEntryId ? 'Rediger IP-whitelist' : 'Ny IP-whitelist' }}</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ $editingEntryId ? 'Oppdater IP-whitelist' : 'Legg til en ny IP-adresse til whitelisten' }}
                </flux:text>
            </div>

            <flux:field>
                <flux:label for="ip_address">IP-adresse</flux:label>
                <flux:input wire:model="ip_address" id="ip_address" placeholder="f.eks. 192.168.1.1" class="font-mono" />
                <flux:description>IPv4 eller IPv6 adresse</flux:description>
                <flux:error name="ip_address" />
            </flux:field>

            <flux:field>
                <flux:label for="cidr_range">CIDR Range (valgfritt)</flux:label>
                <flux:input wire:model="cidr_range" id="cidr_range" placeholder="f.eks. 192.168.1.0/24" class="font-mono" />
                <flux:description>Bruk CIDR-notasjon for å whiteliste et helt nettverk</flux:description>
                <flux:error name="cidr_range" />
            </flux:field>

            <flux:field>
                <flux:label for="description">Beskrivelse</flux:label>
                <flux:input wire:model="description" id="description" placeholder="f.eks. Hovedkontor Oslo" />
                <flux:error name="description" />
            </flux:field>

            <flux:field>
                <flux:label>Status</flux:label>
                <div class="flex items-center gap-2 mt-2">
                    <flux:switch wire:model="is_active" />
                    <flux:text>{{ $is_active ? 'Aktiv' : 'Inaktiv' }}</flux:text>
                </div>
            </flux:field>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button wire:click="closeModal" type="button" variant="ghost">Avbryt</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingEntryId ? 'Lagre endringer' : 'Legg til' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" name="delete-confirm-modal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Slett IP-whitelist</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">
                    Er du sikker på at du vil slette denne IP-adressen fra whitelisten?
                </flux:text>
            </div>

            <flux:callout variant="warning">
                Brukere som logger inn fra denne IP-adressen vil igjen måtte bruke tofaktorautentisering.
            </flux:callout>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="cancelDelete" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="delete" variant="danger">Slett</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
