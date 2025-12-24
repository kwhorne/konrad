<x-layouts.app title="Eiendelsregister">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="assets" />
        <x-app-header current="assets" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-cyan-500 rounded-2xl flex items-center justify-center shadow-lg">
                        <flux:icon.cube class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            Eiendelsregister
                        </flux:heading>
                        <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                            Administrer og følg opp alle eiendeler
                        </flux:text>
                    </div>
                </div>
                <flux:button href="{{ route('assets.create') }}" variant="primary" class="px-6 py-3 shadow-lg shadow-indigo-500/30">
                    <flux:icon.plus class="w-5 h-5 mr-2" />
                    Ny eiendel
                </flux:button>
            </div>

            @if(session('success'))
                <flux:card class="mb-6 bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
                    <div class="p-4 flex items-center">
                        <flux:icon.check-circle class="h-5 w-5 text-green-600 dark:text-green-400 mr-3" />
                        <flux:text class="text-green-800 dark:text-green-200 font-medium">
                            {{ session('success') }}
                        </flux:text>
                    </div>
                </flux:card>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                        <flux:icon.cube class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Totalt</div>
                        <div class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $stats['total'] }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <flux:icon.check-circle class="h-5 w-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">I bruk</div>
                        <div class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $stats['in_use'] }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <flux:icon.cube class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Tilgjengelig</div>
                        <div class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $stats['available'] }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                        <flux:icon.wrench-screwdriver class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Vedlikehold</div>
                        <div class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $stats['maintenance'] }}</div>
                    </div>
                </div>
            </div>

            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-8">
                    <form method="GET" action="{{ route('assets.index') }}" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <flux:input 
                                name="search" 
                                placeholder="Søk etter eiendel..." 
                                icon="magnifying-glass"
                                value="{{ request('search') }}"
                            />
                            
                            <flux:select name="status" placeholder="Status">
                                <option value="">Alle statuser</option>
                                <option value="in_use" {{ request('status') == 'in_use' ? 'selected' : '' }}>I bruk</option>
                                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Tilgjengelig</option>
                                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Vedlikehold</option>
                                <option value="retired" {{ request('status') == 'retired' ? 'selected' : '' }}>Utfaset</option>
                                <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Tapt</option>
                                <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Solgt</option>
                            </flux:select>

                            <flux:select name="is_active" placeholder="Aktiv">
                                <option value="">Alle</option>
                                <option value="true" {{ request('is_active') == 'true' ? 'selected' : '' }}>Aktiv</option>
                                <option value="false" {{ request('is_active') == 'false' ? 'selected' : '' }}>Inaktiv</option>
                            </flux:select>

                            <div class="flex gap-2">
                                <flux:button type="submit" variant="primary" class="flex-1">
                                    Filtrer
                                </flux:button>
                                @if(request()->hasAny(['search', 'status', 'is_active']))
                                    <flux:button href="{{ route('assets.index') }}" variant="ghost">
                                        Nullstill
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if($assets->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Eiendel
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Lokasjon
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Tilstand
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Verdi
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Handlinger
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($assets as $asset)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                        {{ $asset->title }}
                                                    </flux:text>
                                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ $asset->asset_number }}
                                                    </flux:text>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:text class="text-zinc-900 dark:text-white">
                                                    {{ $asset->location ?? '-' }}
                                                </flux:text>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:badge variant="{{ $asset->status_badge_color }}">
                                                    {{ $asset->status_label }}
                                                </flux:badge>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:badge variant="{{ $asset->condition_badge_color }}">
                                                    {{ $asset->condition_label }}
                                                </flux:badge>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:text class="text-zinc-900 dark:text-white">
                                                    {{ $asset->formatted_price }}
                                                </flux:text>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <flux:button href="{{ route('assets.show', $asset) }}" variant="ghost" size="sm">
                                                    Vis
                                                </flux:button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $assets->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <flux:icon.cube class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                            <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                                Ingen eiendeler funnet
                            </flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                                @if(request()->hasAny(['search', 'status', 'is_active']))
                                    Prøv å justere filtrene dine
                                @else
                                    Kom i gang ved å opprette din første eiendel
                                @endif
                            </flux:text>
                            @if(!request()->hasAny(['search', 'status', 'is_active']))
                                <flux:button href="{{ route('assets.create') }}" variant="primary">
                                    <flux:icon.plus class="w-5 h-5 mr-2" />
                                    Opprett eiendel
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
