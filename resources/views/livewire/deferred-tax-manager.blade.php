<div>
    {{-- Header with filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:select wire:model.live="filterYear" class="w-full sm:w-32">
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </flux:select>
        </div>

        <flux:button wire:click="openModal" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Ny post
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

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Utsatt skattefordel</flux:text>
                <flux:heading size="xl" class="mt-2 text-green-600 dark:text-green-400">
                    {{ number_format($deferredTaxAssets, 0, ',', ' ') }} kr
                </flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Fremtidige skattefradrag
                </flux:text>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Utsatt skatteforpliktelse</flux:text>
                <flux:heading size="xl" class="mt-2 text-red-600 dark:text-red-400">
                    {{ number_format($deferredTaxLiabilities, 0, ',', ' ') }} kr
                </flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Fremtidige skatteforpliktelser
                </flux:text>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Netto utsatt skatt</flux:text>
                <flux:heading size="xl" class="mt-2 {{ $netDeferredTax >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                    {{ number_format($netDeferredTax, 0, ',', ' ') }} kr
                </flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ $netDeferredTax >= 0 ? 'Forpliktelse' : 'Fordel' }}
                </flux:text>
            </div>
        </flux:card>
    </div>

    {{-- Items table --}}
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Beskrivelse
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Regnskapsmessig
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Skattemessig
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Midl. forskjell
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Utsatt skatt
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Handlinger
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($items as $item)
                                <tr wire:key="item-{{ $item->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                {{ $item->description }}
                                            </flux:text>
                                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $categories[$item->category] ?? $item->category }}
                                            </flux:text>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:badge variant="{{ $item->item_type === 'asset' ? 'success' : 'warning' }}">
                                            {{ $item->item_type === 'asset' ? 'Eiendel' : 'Gjeld' }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ number_format($item->accounting_value, 0, ',', ' ') }} kr
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ number_format($item->tax_value, 0, ',', ' ') }} kr
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                                            {{ number_format($item->temporary_difference, 0, ',', ' ') }} kr
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium {{ $item->isDeferredTaxAsset() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($item->deferred_tax, 0, ',', ' ') }} kr
                                            <span class="text-xs text-zinc-500">{{ $item->isDeferredTaxAsset() ? '(F)' : '(G)' }}</span>
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:button wire:click="openModal({{ $item->id }})" variant="ghost" size="sm">
                                                <flux:icon.pencil class="w-4 h-4" />
                                            </flux:button>
                                            <flux:button wire:click="delete({{ $item->id }})" wire:confirm="Er du sikker på at du vil slette denne posten?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
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
                    <flux:icon.scale class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                        Ingen poster registrert
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                        Registrer utsatt skatt fordeler og forpliktelser
                    </flux:text>
                    <flux:button wire:click="openModal" variant="primary">
                        <flux:icon.plus class="w-5 h-5 mr-2" />
                        Ny post
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Modal --}}
    <flux:modal wire:model="showModal" variant="flyout" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Rediger post' : 'Ny utsatt skatt-post' }}
                </flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ $editingId ? 'Oppdater utsatt skatt' : 'Registrer en ny utsatt skatt-post' }}
                </flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Regnskapsår *</flux:label>
                    <flux:input wire:model="fiscal_year" type="number" min="2000" max="2100" />
                    @error('fiscal_year')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Type *</flux:label>
                    <flux:select wire:model="item_type">
                        <option value="asset">Eiendel</option>
                        <option value="liability">Gjeld</option>
                    </flux:select>
                    @error('item_type')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Kategori *</flux:label>
                    <flux:select wire:model="category">
                        <option value="">Velg kategori</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    @error('category')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Beskrivelse *</flux:label>
                    <flux:input wire:model="description" type="text" placeholder="Beskriv posten" />
                    @error('description')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Konto</flux:label>
                    <flux:select wire:model="account_id">
                        <option value="">Ingen konto</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->account_number }} - {{ $account->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('account_id')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Regnskapsmessig verdi *</flux:label>
                        <flux:input wire:model="accounting_value" type="number" step="0.01" />
                        @error('accounting_value')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Skattemessig verdi *</flux:label>
                        <flux:input wire:model="tax_value" type="number" step="0.01" />
                        @error('tax_value')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <flux:callout variant="info">
                    <flux:text class="text-sm">
                        Utsatt skatt beregnes automatisk som 22% av forskjellen mellom regnskapsmessig og skattemessig verdi.
                    </flux:text>
                </flux:callout>

                <flux:field>
                    <flux:label>Notater</flux:label>
                    <flux:textarea wire:model="notes" rows="3" placeholder="Interne notater..."></flux:textarea>
                    @error('notes')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeModal" variant="ghost">
                    Avbryt
                </flux:button>
                <flux:button wire:click="save" variant="primary">
                    {{ $editingId ? 'Oppdater' : 'Opprett' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
