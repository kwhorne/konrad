<div>
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-indigo-100 dark:bg-indigo-900/30">
                    <flux:icon.building-office-2 class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Totalt selskaper</flux:text>
                    <flux:heading size="xl">{{ $totalCompanies }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-green-100 dark:bg-green-900/30">
                    <flux:icon.check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Aktive selskaper</flux:text>
                    <flux:heading size="xl">{{ $activeCompanies }}</flux:heading>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk etter navn, org.nr eller e-post..." icon="magnifying-glass" />
        </div>
        <div class="flex gap-4">
            <flux:select wire:model.live="filterStatus" placeholder="Alle statuser">
                <flux:select.option value="">Alle statuser</flux:select.option>
                <flux:select.option value="active">Aktive</flux:select.option>
                <flux:select.option value="inactive">Inaktive</flux:select.option>
            </flux:select>

            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                Nytt selskap
            </flux:button>
        </div>
    </div>

    {{-- Companies Table --}}
    <flux:card class="bg-white dark:bg-zinc-900">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Selskap</flux:table.column>
                <flux:table.column>Org.nr</flux:table.column>
                <flux:table.column>Kontakt</flux:table.column>
                <flux:table.column>Brukere</flux:table.column>
                <flux:table.column>Moduler</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Opprettet</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($companies as $company)
                    <flux:table.row wire:key="company-{{ $company->id }}">
                        <flux:table.cell>
                            <div>
                                <a href="{{ route('admin.company-detail', $company->id) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $company->name }}
                                </a>
                                @if($company->city)
                                    <flux:text class="text-sm text-zinc-500">{{ $company->city }}</flux:text>
                                @endif
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:text class="text-sm font-mono">{{ $company->formatted_organization_number }}</flux:text>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="space-y-1">
                                @if($company->email)
                                    <flux:text class="text-sm">{{ $company->email }}</flux:text>
                                @endif
                                @if($company->billing_email)
                                    <flux:text class="text-sm text-violet-600 dark:text-violet-400">
                                        <span title="Faktura-e-post">{{ $company->billing_email }}</span>
                                    </flux:text>
                                @endif
                                @if($company->phone)
                                    <flux:text class="text-sm text-zinc-500">{{ $company->phone }}</flux:text>
                                @endif
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="zinc">{{ $company->users_count }} brukere</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($company->enabledModules->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($company->enabledModules as $module)
                                        <flux:badge color="violet" size="sm">{{ $module->name }}</flux:badge>
                                    @endforeach
                                </div>
                            @else
                                <flux:text class="text-sm text-zinc-400">Ingen</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($company->is_active)
                                <flux:badge color="green">Aktiv</flux:badge>
                            @else
                                <flux:badge color="red">Inaktiv</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:text class="text-sm text-zinc-500">{{ $company->created_at->format('d.m.Y') }}</flux:text>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>
                                    <flux:menu.item href="{{ route('admin.company-detail', $company->id) }}" icon="eye">
                                        Vis detaljer
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="openModuleModal({{ $company->id }})" icon="puzzle-piece">
                                        Administrer moduler
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item wire:click="toggleActive({{ $company->id }})" icon="{{ $company->is_active ? 'x-circle' : 'check-circle' }}">
                                        {{ $company->is_active ? 'Deaktiver' : 'Aktiver' }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center py-8">
                            <flux:text class="text-zinc-500">Ingen selskaper funnet</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($companies->hasPages())
            <div class="mt-4 px-4 pb-4">
                {{ $companies->links() }}
            </div>
        @endif
    </flux:card>

    {{-- Module Management Modal --}}
    <flux:modal wire:model="showModuleModal" name="module-modal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Administrer moduler</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ $editingCompanyName }}
                </flux:text>
            </div>

            <div class="space-y-4">
                <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Premium moduler</flux:text>

                @foreach($premiumModules as $module)
                    <div class="flex items-center justify-between p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                        <div class="flex-1">
                            <flux:text class="font-medium">{{ $module->name }}</flux:text>
                            <flux:text class="text-sm text-zinc-500">{{ $module->description }}</flux:text>
                            <flux:text class="text-sm text-violet-600 dark:text-violet-400 mt-1">{{ $module->price_formatted }}</flux:text>
                        </div>
                        <flux:switch
                            wire:model="moduleStates.{{ $module->id }}"
                        />
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeModuleModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="saveModules" variant="primary">Lagre endringer</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Create Company Modal --}}
    <flux:modal wire:model="showCreateModal" name="create-company-modal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Nytt selskap</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">
                    Opprett et nytt selskap i systemet
                </flux:text>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <flux:field class="md:col-span-2">
                    <flux:label>Selskapsnavn *</flux:label>
                    <flux:input wire:model="createName" placeholder="Selskap AS" />
                    <flux:error name="createName" />
                </flux:field>

                <flux:field>
                    <flux:label>Org.nr</flux:label>
                    <flux:input wire:model="createOrganizationNumber" placeholder="123456789" />
                    <flux:error name="createOrganizationNumber" />
                </flux:field>

                <flux:field>
                    <flux:label>E-post</flux:label>
                    <flux:input wire:model="createEmail" type="email" placeholder="post@selskap.no" />
                    <flux:error name="createEmail" />
                </flux:field>

                <flux:field>
                    <flux:label>Telefon</flux:label>
                    <flux:input wire:model="createPhone" placeholder="+47 000 00 000" />
                    <flux:error name="createPhone" />
                </flux:field>

                <flux:field>
                    <flux:label>Adresse</flux:label>
                    <flux:input wire:model="createAddress" placeholder="Gateadresse 1" />
                    <flux:error name="createAddress" />
                </flux:field>

                <flux:field>
                    <flux:label>Postnr</flux:label>
                    <flux:input wire:model="createPostalCode" placeholder="0001" />
                    <flux:error name="createPostalCode" />
                </flux:field>

                <flux:field>
                    <flux:label>By</flux:label>
                    <flux:input wire:model="createCity" placeholder="Oslo" />
                    <flux:error name="createCity" />
                </flux:field>

                <flux:field>
                    <flux:label>Land</flux:label>
                    <flux:input wire:model="createCountry" placeholder="Norge" />
                    <flux:error name="createCountry" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeCreateModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="createCompany" variant="primary">Opprett selskap</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
