<div>
    @if(!$departmentsEnabled)
        <flux:callout variant="warning" class="mb-6">
            <flux:callout.heading>Avdelinger er ikke aktivert</flux:callout.heading>
            <flux:callout.text>
                Aktiver avdelinger i regnskapsinnstillingene for a bruke avdelinger som konteringsdimensjon.
            </flux:callout.text>
        </flux:callout>
    @endif

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk etter avdeling..." icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:select wire:model.live="filterActive" class="w-full sm:w-40">
                <option value="">Alle statuser</option>
                <option value="1">Aktive</option>
                <option value="0">Inaktive</option>
            </flux:select>
        </div>

        <flux:button wire:click="create" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Ny avdeling
        </flux:button>
    </div>

    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($departments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Navn</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Beskrivelse</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Sortering</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Handlinger</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($departments as $department)
                                <tr wire:key="department-{{ $department->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="font-mono text-zinc-900 dark:text-white">{{ $department->code }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $department->name }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($department->description)
                                            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ Str::limit($department->description, 50) }}</flux:text>
                                        @else
                                            <flux:text class="text-zinc-400">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $department->sort_order }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($department->is_active)
                                            <flux:badge color="green">Aktiv</flux:badge>
                                        @else
                                            <flux:badge color="zinc">Inaktiv</flux:badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex justify-end gap-2">
                                            <flux:button wire:click="edit({{ $department->id }})" variant="ghost" size="sm">
                                                <flux:icon.pencil class="w-4 h-4" />
                                            </flux:button>
                                            <flux:button wire:click="delete({{ $department->id }})" wire:confirm="Er du sikker på at du vil slette denne avdelingen?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
                                                <flux:icon.trash class="w-4 h-4" />
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $departments->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.building-office class="mx-auto h-12 w-12 text-zinc-400" />
                    <flux:heading size="base" class="mt-2 text-zinc-900 dark:text-white">Ingen avdelinger</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Kom i gang ved å opprette din første avdeling.</flux:text>
                    <div class="mt-6">
                        <flux:button wire:click="create" variant="primary">
                            <flux:icon.plus class="w-5 h-5 mr-2" />
                            Ny avdeling
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </flux:card>

    <flux:modal wire:model="showModal" variant="flyout" class="max-w-lg">
        <div class="p-6">
            <flux:heading size="lg" class="mb-6">
                {{ $editingId ? 'Rediger avdeling' : 'Ny avdeling' }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Avdelingskode</flux:label>
                        <flux:input wire:model="code" placeholder="f.eks. SAL" />
                        <flux:error name="code" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Sortering</flux:label>
                        <flux:input wire:model="sort_order" type="number" min="0" />
                        <flux:error name="sort_order" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Avdelingsnavn</flux:label>
                    <flux:input wire:model="name" placeholder="f.eks. Salgsavdeling" />
                    <flux:error name="name" />
                </flux:field>

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
                        {{ $editingId ? 'Lagre endringer' : 'Opprett avdeling' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
