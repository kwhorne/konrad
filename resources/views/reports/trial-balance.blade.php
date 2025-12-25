<x-layouts.app title="Saldobalanse">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="reports" />
        <x-app-header current="reports" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <flux:button href="{{ route('reports.index') }}" variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-5 h-5" />
                </flux:button>
                <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.scale class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Saldobalanse
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Oversikt over alle kontosaldoer
                    </flux:text>
                </div>
            </div>

            <!-- Filters -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.trial-balance') }}" class="flex flex-wrap gap-4 items-end">
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
            @php
                $isBalanced = abs($totalDebit - $totalCredit) < 0.01;
            @endphp
            @if($isBalanced)
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-center gap-3">
                    <flux:icon.check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                    <flux:text class="text-green-800 dark:text-green-200">Saldobalansen balanserer. Debet = Kredit</flux:text>
                </div>
            @else
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-center gap-3">
                    <flux:icon.exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                    <flux:text class="text-red-800 dark:text-red-200">
                        Advarsel: Saldobalansen balanserer ikke. Differanse: {{ number_format(abs($totalDebit - $totalCredit), 2, ',', ' ') }}
                    </flux:text>
                </div>
            @endif

            <!-- Trial Balance Table -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-6">
                    @if($balances->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Konto</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Kontonavn</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Type</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Debet</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @php $currentClass = null; @endphp
                                    @foreach($balances as $item)
                                        @if($currentClass !== $item['account']->account_class)
                                            @php $currentClass = $item['account']->account_class; @endphp
                                            <tr class="bg-zinc-100 dark:bg-zinc-800">
                                                <td colspan="5" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                                    Klasse {{ $item['account']->account_class }}: {{ $item['account']->class_name }}
                                                </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-mono text-zinc-600 dark:text-zinc-400">{{ $item['account']->account_number }}</td>
                                            <td class="px-4 py-2 text-sm text-zinc-900 dark:text-white">{{ $item['account']->name }}</td>
                                            <td class="px-4 py-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $item['account']->type_name }}</td>
                                            <td class="px-4 py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                                {{ $item['debit'] > 0 ? number_format($item['debit'], 2, ',', ' ') : '' }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                                {{ $item['credit'] > 0 ? number_format($item['credit'], 2, ',', ' ') : '' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-zinc-100 dark:bg-zinc-800">
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-sm font-bold text-zinc-900 dark:text-white">Sum</td>
                                        <td class="px-4 py-3 text-sm text-right font-mono font-bold text-zinc-900 dark:text-white">
                                            {{ number_format($totalDebit, 2, ',', ' ') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-mono font-bold text-zinc-900 dark:text-white">
                                            {{ number_format($totalCredit, 2, ',', ' ') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <flux:icon.scale class="w-16 h-16 text-zinc-400 mx-auto mb-4" />
                            <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-2">Ingen data</flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                Ingen bokforte transaksjoner funnet.
                            </flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>
        </flux:main>
    </div>
</x-layouts.app>
