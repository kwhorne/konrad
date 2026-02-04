<x-layouts.app title="Kontakter">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="contacts" />
        <x-app-header current="contacts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
                        <flux:icon.users class="w-6 h-6 sm:w-7 sm:h-7 text-white" />
                    </div>
                    <div>
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            Kontakter
                        </flux:heading>
                        <flux:text class="mt-1 text-sm sm:text-base text-zinc-600 dark:text-zinc-400">
                            Administrer kunder, leverandører og partnere
                        </flux:text>
                    </div>
                </div>
                <flux:button href="{{ route('contacts.create') }}" variant="primary" class="px-4 sm:px-6 py-2.5 sm:py-3 shadow-lg shadow-blue-500/30 w-full sm:w-auto justify-center">
                    <flux:icon.plus class="w-5 h-5 mr-2" />
                    Ny kontakt
                </flux:button>
            </div>

            <div class="grid grid-cols-1 xs:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <flux:icon.users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Totalt</div>
                        <div class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $stats['total'] }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <flux:icon.shopping-bag class="h-5 w-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Kunder</div>
                        <div class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $stats['customers'] }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                        <flux:icon.truck class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Leverandører</div>
                        <div class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $stats['suppliers'] }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                        <flux:icon.user-group class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Partnere</div>
                        <div class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $stats['partners'] }}</div>
                    </div>
                </div>
            </div>

            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-8">
                    <form method="GET" action="{{ route('contacts.index') }}" class="mb-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                            <flux:input 
                                name="search" 
                                placeholder="Søk etter kontakt..." 
                                icon="magnifying-glass"
                                value="{{ request('search') }}"
                            />
                            
                            <flux:select name="type" placeholder="Type">
                                <option value="">Alle typer</option>
                                <option value="customer" {{ request('type') == 'customer' ? 'selected' : '' }}>Kunde</option>
                                <option value="supplier" {{ request('type') == 'supplier' ? 'selected' : '' }}>Leverandør</option>
                                <option value="partner" {{ request('type') == 'partner' ? 'selected' : '' }}>Partner</option>
                                <option value="prospect" {{ request('type') == 'prospect' ? 'selected' : '' }}>Prospekt</option>
                                <option value="competitor" {{ request('type') == 'competitor' ? 'selected' : '' }}>Konkurrent</option>
                                <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Annet</option>
                            </flux:select>

                            <flux:select name="status" placeholder="Status">
                                <option value="">Alle statuser</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktiv</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inaktiv</option>
                                <option value="prospect" {{ request('status') == 'prospect' ? 'selected' : '' }}>Prospekt</option>
                                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Arkivert</option>
                            </flux:select>

                            <div class="flex gap-2">
                                <flux:button type="submit" variant="primary" class="flex-1">
                                    Filtrer
                                </flux:button>
                                @if(request()->hasAny(['search', 'type', 'status']))
                                    <flux:button href="{{ route('contacts.index') }}" variant="ghost">
                                        Nullstill
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if($contacts->count() > 0)
                        {{-- Desktop: Table --}}
                        <div class="hidden md:block" x-data x-on:dblclick="
                            const row = $event.target.closest('tr[data-href]');
                            if (row) window.location.href = row.dataset.href;
                        ">
                            <flux:table :paginate="$contacts">
                                <flux:table.columns>
                                    <flux:table.column>Bedrift</flux:table.column>
                                    <flux:table.column>Type</flux:table.column>
                                    <flux:table.column>Kontaktperson</flux:table.column>
                                    <flux:table.column>E-post</flux:table.column>
                                    <flux:table.column>Status</flux:table.column>
                                    <flux:table.column></flux:table.column>
                                </flux:table.columns>

                                <flux:table.rows>
                                    @foreach($contacts as $contact)
                                        <flux:table.row
                                            :key="$contact->id"
                                            class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                                            data-href="{{ route('contacts.edit', $contact) }}"
                                        >
                                            <flux:table.cell>
                                                <div>
                                                    <flux:text class="font-medium">{{ $contact->company_name }}</flux:text>
                                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $contact->contact_number }}</flux:text>
                                                </div>
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                <flux:badge color="{{ $contact->getTypeBadgeColor() }}" size="sm">
                                                    {{ $contact->getTypeLabel() }}
                                                </flux:badge>
                                            </flux:table.cell>
                                            <flux:table.cell>{{ $contact->primaryContact?->name ?? '-' }}</flux:table.cell>
                                            <flux:table.cell>{{ $contact->email ?? '-' }}</flux:table.cell>
                                            <flux:table.cell>
                                                <flux:badge color="{{ $contact->getStatusBadgeColor() }}" size="sm">
                                                    {{ $contact->getStatusLabel() }}
                                                </flux:badge>
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                <flux:button href="{{ route('contacts.show', $contact) }}" variant="ghost" size="sm">
                                                    Vis
                                                </flux:button>
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                </flux:table.rows>
                            </flux:table>
                        </div>

                        {{-- Mobile: Cards --}}
                        <div class="md:hidden space-y-3">
                            @foreach($contacts as $contact)
                                <a href="{{ route('contacts.edit', $contact) }}" class="block">
                                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1 min-w-0">
                                                <flux:text class="font-medium text-zinc-900 dark:text-white truncate">{{ $contact->company_name }}</flux:text>
                                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $contact->contact_number }}</flux:text>
                                            </div>
                                            <div class="flex flex-col items-end gap-1 shrink-0">
                                                <flux:badge color="{{ $contact->getTypeBadgeColor() }}" size="sm">
                                                    {{ $contact->getTypeLabel() }}
                                                </flux:badge>
                                                <flux:badge color="{{ $contact->getStatusBadgeColor() }}" size="sm">
                                                    {{ $contact->getStatusLabel() }}
                                                </flux:badge>
                                            </div>
                                        </div>
                                        @if($contact->primaryContact?->name || $contact->email)
                                            <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-700 space-y-1">
                                                @if($contact->primaryContact?->name)
                                                    <div class="flex items-center gap-2 text-sm">
                                                        <flux:icon.user class="w-4 h-4 text-zinc-400" />
                                                        <span class="text-zinc-600 dark:text-zinc-400">{{ $contact->primaryContact->name }}</span>
                                                    </div>
                                                @endif
                                                @if($contact->email)
                                                    <div class="flex items-center gap-2 text-sm">
                                                        <flux:icon.envelope class="w-4 h-4 text-zinc-400" />
                                                        <span class="text-zinc-600 dark:text-zinc-400 truncate">{{ $contact->email }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach

                            {{-- Mobile pagination --}}
                            <div class="pt-4">
                                {{ $contacts->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <flux:icon.users class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                            <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                                Ingen kontakter funnet
                            </flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                                @if(request()->hasAny(['search', 'type', 'status']))
                                    Prøv å justere filtrene dine
                                @else
                                    Kom i gang ved å opprette din første kontakt
                                @endif
                            </flux:text>
                            @if(!request()->hasAny(['search', 'type', 'status']))
                                <flux:button href="{{ route('contacts.create') }}" variant="primary">
                                    <flux:icon.plus class="w-5 h-5 mr-2" />
                                    Opprett kontakt
                                </flux:button>
                            @endif
                        </div>
                    @endif
                </div>
            </flux:card>
        </flux:main>

    </div>
</x-layouts.app>
