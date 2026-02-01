<div>
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-indigo-100 dark:bg-indigo-900/30">
                    <flux:icon.puzzle-piece class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Totalt moduler</flux:text>
                    <flux:heading size="xl">{{ $totalModules }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-violet-100 dark:bg-violet-900/30">
                    <flux:icon.sparkles class="w-6 h-6 text-violet-600 dark:text-violet-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Premium moduler</flux:text>
                    <flux:heading size="xl">{{ $premiumModules }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-green-100 dark:bg-green-900/30">
                    <flux:icon.check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Standard moduler</flux:text>
                    <flux:heading size="xl">{{ $standardModules }}</flux:heading>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk etter navn eller slug..." icon="magnifying-glass" />
        </div>
        <div class="flex gap-4">
            <flux:select wire:model.live="filterType" placeholder="Alle typer">
                <flux:select.option value="">Alle typer</flux:select.option>
                <flux:select.option value="premium">Premium</flux:select.option>
                <flux:select.option value="standard">Standard</flux:select.option>
            </flux:select>
            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                Ny modul
            </flux:button>
        </div>
    </div>

    {{-- Modules Table --}}
    <flux:card class="bg-white dark:bg-zinc-900">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Modul</flux:table.column>
                <flux:table.column>Slug</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Pris</flux:table.column>
                <flux:table.column>Stripe Price ID</flux:table.column>
                <flux:table.column>Selskaper</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Sortering</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($modules as $module)
                    <flux:table.row wire:key="module-{{ $module->id }}">
                        <flux:table.cell>
                            <div>
                                <flux:text class="font-medium">{{ $module->name }}</flux:text>
                                @if($module->description)
                                    <flux:text class="text-sm text-zinc-500 truncate max-w-xs">{{ Str::limit($module->description, 50) }}</flux:text>
                                @endif
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="zinc" class="font-mono">{{ $module->slug }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($module->is_premium)
                                <flux:badge color="violet">Premium</flux:badge>
                            @else
                                <flux:badge color="green">Standard</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($module->is_premium && $module->price_monthly > 0)
                                <flux:text class="font-medium">{{ $module->price_formatted }}</flux:text>
                            @else
                                <flux:text class="text-zinc-400">-</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($module->stripe_price_id)
                                <flux:badge color="zinc" size="sm" class="font-mono text-xs">{{ Str::limit($module->stripe_price_id, 20) }}</flux:badge>
                            @else
                                <flux:text class="text-zinc-400 text-sm">Ikke konfigurert</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="zinc">{{ $module->company_modules_count }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($module->is_active)
                                <flux:badge color="green">Aktiv</flux:badge>
                            @else
                                <flux:badge color="red">Inaktiv</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:text class="text-sm text-zinc-500">{{ $module->sort_order }}</flux:text>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>
                                    <flux:menu.item wire:click="openEditModal({{ $module->id }})" icon="pencil">
                                        Rediger
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="toggleActive({{ $module->id }})" icon="{{ $module->is_active ? 'x-circle' : 'check-circle' }}">
                                        {{ $module->is_active ? 'Deaktiver' : 'Aktiver' }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item wire:click="confirmDelete({{ $module->id }})" icon="trash" variant="danger">
                                        Slett
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="text-center py-8">
                            <flux:text class="text-zinc-500">Ingen moduler funnet</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($modules->hasPages())
            <div class="mt-4 px-4 pb-4">
                {{ $modules->links() }}
            </div>
        @endif
    </flux:card>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" name="module-form-modal">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingModuleId ? 'Rediger modul' : 'Ny modul' }}</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ $editingModuleId ? 'Oppdater modulinformasjon' : 'Opprett en ny modul' }}
                </flux:text>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label for="name">Navn</flux:label>
                    <flux:input wire:model="name" id="name" placeholder="f.eks. Kontrakter" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label for="slug">Slug</flux:label>
                    <flux:input wire:model="slug" id="slug" placeholder="f.eks. contracts" class="font-mono" />
                    <flux:error name="slug" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label for="description">Beskrivelse</flux:label>
                <flux:textarea wire:model="description" id="description" placeholder="Kort beskrivelse av modulen..." rows="2" />
                <flux:error name="description" />
            </flux:field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label for="is_premium">Type</flux:label>
                    <flux:select wire:model="is_premium" id="is_premium">
                        <flux:select.option :value="true">Premium (betalt)</flux:select.option>
                        <flux:select.option :value="false">Standard (inkludert)</flux:select.option>
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label for="price_monthly">Pris (øre/mnd)</flux:label>
                    <flux:input wire:model="price_monthly" id="price_monthly" type="number" min="0" placeholder="14900 = 149 kr" />
                    <flux:description>14900 = 149 kr/mnd</flux:description>
                    <flux:error name="price_monthly" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label for="stripe_price_id">Stripe Price ID</flux:label>
                <flux:input wire:model="stripe_price_id" id="stripe_price_id" placeholder="price_..." class="font-mono" />
                <flux:description>Price ID fra Stripe Dashboard</flux:description>
                <flux:error name="stripe_price_id" />
            </flux:field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label for="sort_order">Sorteringsrekkefølge</flux:label>
                    <flux:input wire:model="sort_order" id="sort_order" type="number" min="0" />
                    <flux:error name="sort_order" />
                </flux:field>

                <flux:field>
                    <flux:label>Status</flux:label>
                    <div class="flex items-center gap-2 mt-2">
                        <flux:switch wire:model="is_active" />
                        <flux:text>{{ $is_active ? 'Aktiv' : 'Inaktiv' }}</flux:text>
                    </div>
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button wire:click="closeModal" type="button" variant="ghost">Avbryt</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingModuleId ? 'Lagre endringer' : 'Opprett modul' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" name="delete-confirm-modal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Slett modul</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">
                    Er du sikker på at du vil slette denne modulen? Denne handlingen kan ikke angres.
                </flux:text>
            </div>

            <flux:callout variant="danger">
                Modulen kan kun slettes hvis den ikke er aktivert for noen selskaper.
            </flux:callout>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="cancelDelete" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="delete" variant="danger">Slett modul</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
