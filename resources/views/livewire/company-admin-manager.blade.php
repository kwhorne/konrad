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
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Sok etter navn, org.nr eller e-post..." icon="magnifying-glass" />
        </div>
        <div class="flex gap-4">
            <flux:select wire:model.live="filterStatus" placeholder="Alle statuser">
                <flux:select.option value="">Alle statuser</flux:select.option>
                <flux:select.option value="active">Aktive</flux:select.option>
                <flux:select.option value="inactive">Inaktive</flux:select.option>
            </flux:select>
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
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Opprettet</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($companies as $company)
                    <flux:table.row wire:key="company-{{ $company->id }}">
                        <flux:table.cell>
                            <div>
                                <flux:text class="font-medium">{{ $company->name }}</flux:text>
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
                                @if($company->phone)
                                    <flux:text class="text-sm text-zinc-500">{{ $company->phone }}</flux:text>
                                @endif
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="zinc">{{ $company->users_count }} brukere</flux:badge>
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
                                    <flux:menu.item wire:click="toggleActive({{ $company->id }})" icon="{{ $company->is_active ? 'x-circle' : 'check-circle' }}">
                                        {{ $company->is_active ? 'Deaktiver' : 'Aktiver' }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8">
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
</div>
