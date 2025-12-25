<x-layouts.app title="Kontoplan">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="accounting" />
        <x-app-header current="accounting" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-violet-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
                        <flux:icon.table-cells class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            Kontoplan
                        </flux:heading>
                        <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                            NS 4102 - Norsk standard kontoplan for AS
                        </flux:text>
                    </div>
                </div>
                <flux:button href="{{ route('accounts.create') }}" variant="primary" class="px-6 py-3 shadow-lg shadow-violet-500/30">
                    <flux:icon.plus class="w-5 h-5 mr-2" />
                    Ny konto
                </flux:button>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <flux:text class="text-green-800 dark:text-green-200">{{ session('success') }}</flux:text>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <flux:text class="text-red-800 dark:text-red-200">{{ session('error') }}</flux:text>
                </div>
            @endif

            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 mb-6">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                    <form action="{{ route('accounts.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <flux:input name="search" type="text" placeholder="Søk etter kontonummer eller navn..." value="{{ request('search') }}" />
                        </div>
                        <div class="w-40">
                            <flux:select name="class">
                                <option value="">Alle klasser</option>
                                <option value="1" {{ request('class') === '1' ? 'selected' : '' }}>1 - Eiendeler</option>
                                <option value="2" {{ request('class') === '2' ? 'selected' : '' }}>2 - EK og gjeld</option>
                                <option value="3" {{ request('class') === '3' ? 'selected' : '' }}>3 - Inntekter</option>
                                <option value="4" {{ request('class') === '4' ? 'selected' : '' }}>4 - Varekostnad</option>
                                <option value="5" {{ request('class') === '5' ? 'selected' : '' }}>5 - Lønn</option>
                                <option value="6" {{ request('class') === '6' ? 'selected' : '' }}>6 - Avskrivninger</option>
                                <option value="7" {{ request('class') === '7' ? 'selected' : '' }}>7 - Driftskostn.</option>
                                <option value="8" {{ request('class') === '8' ? 'selected' : '' }}>8 - Finans</option>
                            </flux:select>
                        </div>
                        <div class="w-40">
                            <flux:select name="type">
                                <option value="">Alle typer</option>
                                <option value="asset" {{ request('type') === 'asset' ? 'selected' : '' }}>Eiendel</option>
                                <option value="liability" {{ request('type') === 'liability' ? 'selected' : '' }}>Gjeld</option>
                                <option value="equity" {{ request('type') === 'equity' ? 'selected' : '' }}>Egenkapital</option>
                                <option value="revenue" {{ request('type') === 'revenue' ? 'selected' : '' }}>Inntekt</option>
                                <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Kostnad</option>
                            </flux:select>
                        </div>
                        <flux:button type="submit" variant="primary">
                            <flux:icon.magnifying-glass class="w-4 h-4 mr-2" />
                            Søk
                        </flux:button>
                        @if(request()->hasAny(['search', 'class', 'type']))
                            <flux:button href="{{ route('accounts.index') }}" variant="ghost">
                                Nullstill
                            </flux:button>
                        @endif
                    </form>
                </div>

                <div class="p-6">
                    @if($accounts->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Kontonr.
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Navn
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Klasse
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Type
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
                                    @foreach($accounts as $account)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:text class="font-mono font-medium text-zinc-900 dark:text-white">
                                                    {{ $account->account_number }}
                                                </flux:text>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:text class="text-zinc-900 dark:text-white">
                                                    {{ $account->name }}
                                                </flux:text>
                                                @if($account->is_system)
                                                    <flux:badge size="sm" color="violet" class="ml-2">System</flux:badge>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:badge variant="outline">{{ $account->account_class }}</flux:badge>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                                    {{ $account->type_name }}
                                                </flux:text>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:badge color="{{ $account->is_active ? 'green' : 'zinc' }}">
                                                    {{ $account->is_active ? 'Aktiv' : 'Inaktiv' }}
                                                </flux:badge>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end gap-2">
                                                    <flux:button href="{{ route('accounts.edit', $account) }}" variant="ghost" size="sm">
                                                        <flux:icon.pencil class="w-4 h-4" />
                                                    </flux:button>
                                                    @unless($account->is_system)
                                                        <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline" onsubmit="return confirm('Er du sikker på at du vil slette denne kontoen?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <flux:button type="submit" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
                                                                <flux:icon.trash class="w-4 h-4" />
                                                            </flux:button>
                                                        </form>
                                                    @endunless
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $accounts->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <flux:icon.table-cells class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                            <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                                Ingen kontoer funnet
                            </flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                                @if(request()->hasAny(['search', 'class', 'type']))
                                    Prøv et annet søk eller fjern filteret
                                @else
                                    Kom i gang ved å opprette din første konto
                                @endif
                            </flux:text>
                            <flux:button href="{{ route('accounts.create') }}" variant="primary">
                                <flux:icon.plus class="w-5 h-5 mr-2" />
                                Opprett konto
                            </flux:button>
                        </div>
                    @endif
                </div>
            </flux:card>
        </flux:main>
    </div>
</x-layouts.app>
