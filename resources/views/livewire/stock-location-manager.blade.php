<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Sok etter lokasjon..." icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:select wire:model.live="filterType" class="w-full sm:w-40">
                <option value="">Alle typer</option>
                <option value="warehouse">Lager</option>
                <option value="zone">Sone</option>
                <option value="bin">Hylle</option>
            </flux:select>

            <flux:select wire:model.live="filterActive" class="w-full sm:w-40">
                <option value="">Alle statuser</option>
                <option value="1">Aktive</option>
                <option value="0">Inaktive</option>
            </flux:select>
        </div>

        <flux:button wire:click="create" variant="primary">
            <flux:icon.plus class="w-5 h-5 mr-2" />
            Ny lokasjon
        </flux:button>
    </div>

    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($locations->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Navn</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Overordnet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Handlinger</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($locations as $location)
                                <tr wire:key="location-{{ $location->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="font-mono text-zinc-900 dark:text-white">{{ $location->code }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $location->name }}</flux:text>
                                        @if($location->description)
                                            <flux:text class="text-sm text-zinc-500">{{ Str::limit($location->description, 50) }}</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:badge color="{{ $location->type_color }}">{{ $location->type_label }}</flux:badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($location->parent)
                                            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $location->parent->name }}</flux:text>
                                        @else
                                            <flux:text class="text-zinc-400">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($location->is_active)
                                            <flux:badge color="green">Aktiv</flux:badge>
                                        @else
                                            <flux:badge color="zinc">Inaktiv</flux:badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex justify-end gap-2">
                                            <flux:button wire:click="edit({{ $location->id }})" variant="ghost" size="sm">
                                                <flux:icon.pencil class="w-4 h-4" />
                                            </flux:button>
                                            <flux:button wire:click="delete({{ $location->id }})" wire:confirm="Er du sikker pa at du vil slette denne lokasjonen?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
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
                    {{ $locations->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.archive-box class="mx-auto h-12 w-12 text-zinc-400" />
                    <flux:heading size="base" class="mt-2 text-zinc-900 dark:text-white">Ingen lokasjoner</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Kom i gang ved a opprette din forste lagerlokasjon.</flux:text>
                    <div class="mt-6">
                        <flux:button wire:click="create" variant="primary">
                            <flux:icon.plus class="w-5 h-5 mr-2" />
                            Ny lokasjon
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </flux:card>

    <flux:modal wire:model="showModal" variant="flyout" class="max-w-lg">
        <div class="p-6">
            <flux:heading size="lg" class="mb-6">
                {{ $editingId ? 'Rediger lokasjon' : 'Ny lokasjon' }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Kode</flux:label>
                        <flux:input wire:model="code" placeholder="f.eks. LAGER-1" />
                        <flux:error name="code" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Type</flux:label>
                        <flux:select wire:model="location_type">
                            <option value="warehouse">Lager</option>
                            <option value="zone">Sone</option>
                            <option value="bin">Hylle</option>
                        </flux:select>
                        <flux:error name="location_type" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Navn</flux:label>
                    <flux:input wire:model="name" placeholder="Hovedlager" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Beskrivelse</flux:label>
                    <flux:textarea wire:model="description" rows="2" placeholder="Valgfri beskrivelse..." />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Adresse</flux:label>
                    <flux:textarea wire:model="address" rows="2" placeholder="Valgfri adresse..." />
                    <flux:error name="address" />
                </flux:field>

                <flux:field>
                    <flux:label>Overordnet lokasjon</flux:label>
                    <flux:select wire:model="parent_id">
                        <option value="">Ingen (toppniva)</option>
                        @foreach($parentLocations as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->code }} - {{ $parent->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="parent_id" />
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model="is_active" label="Aktiv" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="$set('showModal', false)" variant="ghost">Avbryt</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? 'Lagre endringer' : 'Opprett lokasjon' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
