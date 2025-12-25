<div>
    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="p-4">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Total aksjekapital</flux:text>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">
                    {{ number_format($totalCapital, 2, ',', ' ') }} NOK
                </flux:heading>
            </div>
        </flux:card>
        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="p-4">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Totalt antall aksjer</flux:text>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">
                    {{ number_format($totalShares, 0, ',', ' ') }}
                </flux:heading>
            </div>
        </flux:card>
        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="p-4">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Antall aksjeklasser</flux:text>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">
                    {{ $shareClasses->where('is_active', true)->count() }}
                </flux:heading>
            </div>
        </flux:card>
    </div>

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <flux:heading size="lg">Aksjeklasser</flux:heading>
        <flux:button wire:click="openModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Ny aksjeklasse
        </flux:button>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <flux:text class="text-green-800 dark:text-green-200">{{ session('success') }}</flux:text>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <flux:text class="text-red-800 dark:text-red-200">{{ session('error') }}</flux:text>
        </div>
    @endif

    {{-- Share classes table --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($shareClasses->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Navn</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Palydende</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Antall</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Kapital</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Rettigheter</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Handlinger</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($shareClasses as $class)
                                <tr wire:key="class-{{ $class->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-6 py-4">
                                        <flux:badge variant="primary">{{ $class->code }}</flux:badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $class->name }}</flux:text>
                                        @if($class->isin)
                                            <flux:text class="text-sm text-zinc-500">ISIN: {{ $class->isin }}</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <flux:text>{{ $class->getFormattedParValue() }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <flux:text class="font-medium">{{ number_format($class->total_shares, 0, ',', ' ') }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <flux:text class="font-medium">{{ $class->getFormattedTotalCapital() }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text class="text-sm">{{ $class->getRightsDescription() }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button wire:click="toggleActive({{ $class->id }})">
                                            <flux:badge variant="{{ $class->is_active ? 'success' : 'outline' }}">
                                                {{ $class->is_active ? 'Aktiv' : 'Inaktiv' }}
                                            </flux:badge>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:button wire:click="openModal({{ $class->id }})" variant="ghost" size="sm">
                                                <flux:icon.pencil class="w-4 h-4" />
                                            </flux:button>
                                            <flux:button wire:click="delete({{ $class->id }})" wire:confirm="Er du sikker?" variant="ghost" size="sm" class="text-red-600">
                                                <flux:icon.trash class="w-4 h-4" />
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.squares-2x2 class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="mb-2">Ingen aksjeklasser</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">Opprett din forste aksjeklasse for a komme i gang</flux:text>
                    <flux:button wire:click="openModal" variant="primary">
                        <flux:icon.plus class="w-5 h-5 mr-2" />
                        Opprett aksjeklasse
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Modal --}}
    <flux:modal wire:model="showModal" variant="flyout" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Rediger aksjeklasse' : 'Ny aksjeklasse' }}</flux:heading>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Kode *</flux:label>
                        <flux:input wire:model="code" type="text" placeholder="A" maxlength="10" />
                        @error('code') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Navn *</flux:label>
                        <flux:input wire:model="name" type="text" placeholder="A-aksjer" />
                        @error('name') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>ISIN</flux:label>
                    <flux:input wire:model="isin" type="text" placeholder="NO0000000000" maxlength="12" />
                    @error('isin') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Palydende (NOK) *</flux:label>
                        <flux:input wire:model="par_value" type="number" step="0.01" min="0.01" />
                        @error('par_value') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Antall aksjer</flux:label>
                        <flux:input wire:model="total_shares" type="number" min="0" />
                        @error('total_shares') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:checkbox wire:model="has_voting_rights" label="Stemmerett" />
                    </flux:field>
                    <flux:field>
                        <flux:checkbox wire:model="has_dividend_rights" label="Utbytterett" />
                    </flux:field>
                </div>

                @if($has_voting_rights)
                    <flux:field>
                        <flux:label>Stemmevekt</flux:label>
                        <flux:input wire:model="voting_weight" type="number" step="0.01" min="0" />
                        <flux:description>1.00 = en stemme per aksje</flux:description>
                        @error('voting_weight') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Rekkefolge</flux:label>
                    <flux:input wire:model="sort_order" type="number" min="0" />
                    @error('sort_order') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model="is_active" label="Aktiv" />
                </flux:field>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="save" variant="primary">{{ $editingId ? 'Oppdater' : 'Opprett' }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
