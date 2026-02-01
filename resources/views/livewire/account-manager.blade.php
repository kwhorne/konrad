<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk etter konto..." icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:select wire:model.live="filterClass" class="w-full sm:w-48">
                <option value="">Alle klasser</option>
                @foreach($accountClasses as $key => $class)
                    <option value="{{ $key }}">{{ $class['name'] ?? $key }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterType" class="w-full sm:w-40">
                <option value="">Alle typer</option>
                <option value="asset">Eiendel</option>
                <option value="liability">Gjeld</option>
                <option value="equity">Egenkapital</option>
                <option value="revenue">Inntekt</option>
                <option value="expense">Kostnad</option>
            </flux:select>

            <flux:select wire:model.live="filterActive" class="w-full sm:w-40">
                <option value="">Alle statuser</option>
                <option value="1">Aktive</option>
                <option value="0">Inaktive</option>
            </flux:select>
        </div>

        <flux:button wire:click="create" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Ny konto
        </flux:button>
    </div>

    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($accounts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Konto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Navn</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Klasse</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">MVA</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Handlinger</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($accounts as $account)
                                <tr wire:key="account-{{ $account->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="font-mono font-medium text-zinc-900 dark:text-white">{{ $account->account_number }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text class="text-zinc-900 dark:text-white">{{ $account->name }}</flux:text>
                                        @if($account->is_system)
                                            <flux:badge size="sm" color="blue" class="ml-2">System</flux:badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $account->class_name }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $account->type_name }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($account->vat_code)
                                            <flux:badge variant="outline">{{ $account->vat_code }}</flux:badge>
                                        @else
                                            <flux:text class="text-zinc-400">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($account->is_active)
                                            <flux:badge color="green">Aktiv</flux:badge>
                                        @else
                                            <flux:badge color="zinc">Inaktiv</flux:badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if(!$account->is_system)
                                            <div class="flex justify-end gap-2">
                                                <flux:button wire:click="edit({{ $account->id }})" variant="ghost" size="sm">
                                                    <flux:icon.pencil class="w-4 h-4" />
                                                </flux:button>
                                                <flux:button wire:click="delete({{ $account->id }})" wire:confirm="Er du sikker på at du vil slette denne kontoen?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
                                                    <flux:icon.trash class="w-4 h-4" />
                                                </flux:button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $accounts->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.table-cells class="mx-auto h-12 w-12 text-zinc-400" />
                    <flux:heading size="base" class="mt-2 text-zinc-900 dark:text-white">Ingen kontoer</flux:heading>
                    <flux:text class="mt-1 text-zinc-500 mb-6">Kom i gang ved a opprette norsk standard kontoplan eller opprett kontoer manuelt.</flux:text>
                    <div class="flex flex-col sm:flex-row justify-center gap-3">
                        <flux:button wire:click="confirmCreateNs4102" variant="primary">
                            <flux:icon.document-text class="w-5 h-5 mr-2" />
                            Opprett NS 4102 kontoplan
                        </flux:button>
                        <flux:button wire:click="create" variant="ghost">
                            <flux:icon.plus class="w-5 h-5 mr-2" />
                            Ny konto manuelt
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </flux:card>

    <flux:modal wire:model="showModal" variant="flyout" class="max-w-lg">
        <div class="p-6">
            <flux:heading size="lg" class="mb-6">
                {{ $editingId ? 'Rediger konto' : 'Ny konto' }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Kontonummer</flux:label>
                        <flux:input wire:model="account_number" placeholder="f.eks. 1920" />
                        <flux:error name="account_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>MVA-kode</flux:label>
                        <flux:input wire:model="vat_code" placeholder="f.eks. 3" />
                        <flux:error name="vat_code" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Kontonavn</flux:label>
                    <flux:input wire:model="name" placeholder="f.eks. Bankinnskudd" />
                    <flux:error name="name" />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Kontoklasse</flux:label>
                        <flux:select wire:model="account_class">
                            <option value="">Velg klasse</option>
                            @foreach($accountClasses as $key => $class)
                                <option value="{{ $key }}">{{ $class['name'] ?? $key }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="account_class" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Kontotype</flux:label>
                        <flux:select wire:model="account_type">
                            <option value="asset">Eiendel</option>
                            <option value="liability">Gjeld</option>
                            <option value="equity">Egenkapital</option>
                            <option value="revenue">Inntekt</option>
                            <option value="expense">Kostnad</option>
                        </flux:select>
                        <flux:error name="account_type" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Beskrivelse</flux:label>
                    <flux:textarea wire:model="description" rows="2" placeholder="Valgfri beskrivelse..." />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model="is_active" label="Aktiv" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="$set('showModal', false)" variant="ghost">Avbryt</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? 'Lagre endringer' : 'Opprett konto' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- NS 4102 Confirmation Modal -->
    <flux:modal wire:model="showConfirmNs4102Modal" name="confirm-ns4102-modal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Opprett NS 4102 kontoplan</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 mt-2">
                    Dette vil opprette norsk standard kontoplan (NS 4102) for aksjeselskaper. Kontoplanen inneholder over 200 forhåndsdefinerte kontoer.
                </flux:text>
            </div>

            <flux:callout variant="info">
                <flux:callout.text>
                    Eksisterende kontoer med samme kontonummer vil ikke bli overskrevet.
                </flux:callout.text>
            </flux:callout>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="$set('showConfirmNs4102Modal', false)" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="createNs4102ChartOfAccounts" variant="primary">
                    <flux:icon.check class="w-4 h-4 mr-2" />
                    Opprett kontoplan
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
