<x-layouts.app title="Balanse">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="reports" />
        <x-app-header current="reports" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <flux:button href="{{ route('reports.index') }}" variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-5 h-5" />
                </flux:button>
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.building-library class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Balanse
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Eiendeler, gjeld og egenkapital
                    </flux:text>
                </div>
            </div>

            <!-- Filters -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.balance-sheet') }}" class="flex flex-wrap gap-4 items-end">
                        <flux:field class="w-40">
                            <flux:label>Per dato</flux:label>
                            <flux:input type="date" name="at_date" value="{{ $atDate->format('Y-m-d') }}" />
                        </flux:field>
                        <flux:button type="submit" variant="primary">
                            <flux:icon.funnel class="w-4 h-4 mr-2" />
                            Oppdater
                        </flux:button>
                    </form>
                </div>
            </flux:card>

            <!-- Balance check -->
            @if($balance['is_balanced'])
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-center gap-3">
                    <flux:icon.check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                    <flux:text class="text-green-800 dark:text-green-200">Balansen stemmer. Eiendeler = Egenkapital + Gjeld</flux:text>
                </div>
            @else
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-center gap-3">
                    <flux:icon.exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                    <flux:text class="text-red-800 dark:text-red-200">
                        Advarsel: Balansen stemmer ikke. Differanse: {{ number_format(abs($balance['total_assets'] - $balance['total_equity_and_liabilities']), 2, ',', ' ') }}
                    </flux:text>
                </div>
            @endif

            <!-- Balance Sheet -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Assets -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <flux:icon.cube class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <flux:heading size="lg" class="text-zinc-900 dark:text-white">Eiendeler</flux:heading>
                        </div>

                        <table class="min-w-full">
                            <tbody>
                                @foreach($balance['assets'] as $item)
                                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                        <td class="py-2 text-sm text-zinc-700 dark:text-zinc-300">
                                            <span class="font-mono text-zinc-500 mr-2">{{ $item['account']->account_number }}</span>
                                            {{ $item['account']->name }}
                                        </td>
                                        <td class="py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                            {{ number_format($item['amount'], 2, ',', ' ') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-blue-50 dark:bg-blue-900/20">
                                    <td class="py-3 text-sm font-bold text-blue-800 dark:text-blue-300">Sum eiendeler</td>
                                    <td class="py-3 text-sm text-right font-mono font-bold text-blue-800 dark:text-blue-300">
                                        {{ number_format($balance['total_assets'], 2, ',', ' ') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </flux:card>

                <!-- Equity and Liabilities -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                <flux:icon.building-office class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <flux:heading size="lg" class="text-zinc-900 dark:text-white">Egenkapital og gjeld</flux:heading>
                        </div>

                        <table class="min-w-full">
                            <tbody>
                                @foreach($balance['equity_and_liabilities'] as $item)
                                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                        <td class="py-2 text-sm text-zinc-700 dark:text-zinc-300">
                                            <span class="font-mono text-zinc-500 mr-2">{{ $item['account']->account_number }}</span>
                                            {{ $item['account']->name }}
                                        </td>
                                        <td class="py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                            {{ number_format($item['amount'], 2, ',', ' ') }}
                                        </td>
                                    </tr>
                                @endforeach

                                <!-- Current year profit -->
                                @if($balance['current_year_profit'] != 0)
                                    <tr class="border-b border-zinc-100 dark:border-zinc-800 bg-green-50/50 dark:bg-green-900/10">
                                        <td class="py-2 text-sm text-zinc-700 dark:text-zinc-300 font-medium">
                                            <span class="font-mono text-zinc-500 mr-2">&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                            Arets resultat
                                        </td>
                                        <td class="py-2 text-sm text-right font-mono {{ $balance['current_year_profit'] >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                                            {{ number_format($balance['current_year_profit'], 2, ',', ' ') }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr class="bg-purple-50 dark:bg-purple-900/20">
                                    <td class="py-3 text-sm font-bold text-purple-800 dark:text-purple-300">Sum egenkapital og gjeld</td>
                                    <td class="py-3 text-sm text-right font-mono font-bold text-purple-800 dark:text-purple-300">
                                        {{ number_format($balance['total_equity_and_liabilities'], 2, ',', ' ') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </flux:card>
            </div>

            <!-- Summary -->
            <flux:card class="bg-zinc-900 dark:bg-zinc-100 shadow-lg mt-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                        <div>
                            <flux:text class="text-zinc-400 dark:text-zinc-600 text-sm mb-1">Sum eiendeler</flux:text>
                            <flux:heading size="xl" class="text-white dark:text-zinc-900 font-mono">
                                {{ number_format($balance['total_assets'], 2, ',', ' ') }}
                            </flux:heading>
                        </div>
                        <div>
                            <flux:text class="text-zinc-400 dark:text-zinc-600 text-sm mb-1">=</flux:text>
                            <flux:heading size="xl" class="text-zinc-400 dark:text-zinc-600">=</flux:heading>
                        </div>
                        <div>
                            <flux:text class="text-zinc-400 dark:text-zinc-600 text-sm mb-1">Sum EK + Gjeld</flux:text>
                            <flux:heading size="xl" class="text-white dark:text-zinc-900 font-mono">
                                {{ number_format($balance['total_equity_and_liabilities'], 2, ',', ' ') }}
                            </flux:heading>
                        </div>
                    </div>
                </div>
            </flux:card>
        </flux:main>
    </div>
</x-layouts.app>
