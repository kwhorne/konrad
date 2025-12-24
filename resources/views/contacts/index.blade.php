<x-layouts.app title="Kontakter">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="contacts" />
        <x-app-header current="contacts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg">
                        <flux:icon.users class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            Kontakter
                        </flux:heading>
                        <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                            Administrer kunder, leverandører og partnere
                        </flux:text>
                    </div>
                </div>
                <flux:button href="{{ route('contacts.create') }}" variant="primary" class="px-6 py-3 shadow-lg shadow-blue-500/30">
                    <flux:icon.plus class="w-5 h-5 mr-2" />
                    Ny kontakt
                </flux:button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
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
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Bedrift
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Kontaktperson
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            E-post
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Handlinger
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($contacts as $contact)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                        {{ $contact->company_name }}
                                                    </flux:text>
                                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ $contact->contact_number }}
                                                    </flux:text>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:badge variant="{{ $contact->getTypeBadgeColor() }}">
                                                    {{ $contact->getTypeLabel() }}
                                                </flux:badge>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:text class="text-zinc-900 dark:text-white">
                                                    {{ $contact->primaryContact?->name ?? '-' }}
                                                </flux:text>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:text class="text-zinc-900 dark:text-white">
                                                    {{ $contact->email ?? '-' }}
                                                </flux:text>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:badge variant="{{ $contact->getStatusBadgeColor() }}">
                                                    {{ $contact->getStatusLabel() }}
                                                </flux:badge>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <flux:button href="{{ route('contacts.show', $contact) }}" variant="ghost" size="sm">
                                                    Vis
                                                </flux:button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $contacts->links() }}
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

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</x-layouts.app>
