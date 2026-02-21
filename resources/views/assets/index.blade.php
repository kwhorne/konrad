<x-layouts.app title="Eiendeler">
    <div class="min-h-screen bg-zinc-100 dark:bg-zinc-950">
        <x-app-sidebar current="assets" />
        <x-app-header current="assets" />

        <flux:main class="bg-zinc-100 dark:bg-zinc-950">

            {{-- Header --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">Eiendeler</h1>
                    <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Administrer og følg opp alle eiendeler</p>
                </div>
                <flux:button href="{{ route('assets.create') }}" variant="primary" icon="plus">
                    Ny eiendel
                </flux:button>
            </div>

            @if(session('success'))
                <div class="mb-4 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20 px-4 py-3">
                    <flux:icon.check-circle class="h-4 w-4 text-green-600 dark:text-green-400 shrink-0" />
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            @endif

            {{-- KPI strip --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Totalt</p>
                            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total'] }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center shrink-0">
                            <flux:icon.cube class="w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">I bruk</p>
                            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['in_use'] }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-green-50 dark:bg-green-950/50 flex items-center justify-center shrink-0">
                            <flux:icon.check-circle class="w-4.5 h-4.5 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Tilgjengelig</p>
                            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['available'] }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center shrink-0">
                            <flux:icon.inbox class="w-4.5 h-4.5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Vedlikehold</p>
                            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['maintenance'] }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center shrink-0">
                            <flux:icon.wrench-screwdriver class="w-4.5 h-4.5 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter + table --}}
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden">

                {{-- Filter bar --}}
                <form method="GET" action="{{ route('assets.index') }}">
                    <div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                        <flux:input
                            name="search"
                            placeholder="Søk på navn, serienummer, lokasjon..."
                            icon="magnifying-glass"
                            value="{{ request('search') }}"
                            class="w-64"
                        />
                        <flux:select name="status" class="w-44">
                            <option value="">Alle statuser</option>
                            <option value="in_use"      {{ request('status') == 'in_use'      ? 'selected' : '' }}>I bruk</option>
                            <option value="available"   {{ request('status') == 'available'   ? 'selected' : '' }}>Tilgjengelig</option>
                            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Vedlikehold</option>
                            <option value="retired"     {{ request('status') == 'retired'     ? 'selected' : '' }}>Utfaset</option>
                            <option value="lost"        {{ request('status') == 'lost'        ? 'selected' : '' }}>Tapt</option>
                            <option value="sold"        {{ request('status') == 'sold'        ? 'selected' : '' }}>Solgt</option>
                        </flux:select>
                        <flux:select name="is_active" class="w-36">
                            <option value="">Alle</option>
                            <option value="true"  {{ request('is_active') == 'true'  ? 'selected' : '' }}>Aktiv</option>
                            <option value="false" {{ request('is_active') == 'false' ? 'selected' : '' }}>Inaktiv</option>
                        </flux:select>
                        <flux:button type="submit" variant="primary" size="sm">Filtrer</flux:button>
                        @if(request()->hasAny(['search', 'status', 'is_active']))
                            <flux:button href="{{ route('assets.index') }}" variant="ghost" size="sm">Nullstill</flux:button>
                        @endif
                    </div>
                </form>

                {{-- Table --}}
                @if($assets->count() > 0)
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Eiendel</flux:table.column>
                            <flux:table.column>Lokasjon / Avdeling</flux:table.column>
                            <flux:table.column>Ansvarlig</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Tilstand</flux:table.column>
                            <flux:table.column class="text-right">Verdi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($assets as $asset)
                                <flux:table.row
                                    wire:key="asset-{{ $asset->id }}"
                                    class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                                    onclick="window.location='{{ route('assets.edit', $asset) }}'"
                                >
                                    <flux:table.cell>
                                        <div>
                                            <p class="font-medium text-zinc-900 dark:text-white">{{ $asset->title }}</p>
                                            <p class="text-xs text-zinc-400 dark:text-zinc-500 font-mono mt-0.5">{{ $asset->asset_number }}</p>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div>
                                            <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $asset->location ?? '—' }}</p>
                                            @if($asset->department)
                                                <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $asset->department }}</p>
                                            @endif
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if($asset->responsibleUser)
                                            <div class="flex items-center gap-2">
                                                <flux:avatar size="xs" name="{{ $asset->responsibleUser->name }}" />
                                                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $asset->responsibleUser->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-sm text-zinc-400">—</span>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge color="{{ $asset->status_badge_color }}">{{ $asset->status_label }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge color="{{ $asset->condition_badge_color }}" variant="pill">{{ $asset->condition_label }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right">
                                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $asset->formatted_price }}</span>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>

                    @if($assets->hasPages())
                        <div class="px-5 py-4 border-t border-zinc-100 dark:border-zinc-800">
                            {{ $assets->links() }}
                        </div>
                    @endif
                @else
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-12 h-12 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mb-4">
                            <flux:icon.cube class="w-6 h-6 text-zinc-400" />
                        </div>
                        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Ingen eiendeler funnet</p>
                        <p class="text-sm text-zinc-400 dark:text-zinc-500 mb-4">
                            @if(request()->hasAny(['search', 'status', 'is_active']))
                                Prøv å justere filtrene
                            @else
                                Kom i gang ved å opprette din første eiendel
                            @endif
                        </p>
                        @if(!request()->hasAny(['search', 'status', 'is_active']))
                            <flux:button href="{{ route('assets.create') }}" variant="primary" size="sm" icon="plus">
                                Opprett eiendel
                            </flux:button>
                        @endif
                    </div>
                @endif
            </div>

        </flux:main>
    </div>
</x-layouts.app>
