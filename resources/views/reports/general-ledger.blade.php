<x-layouts.app title="Hovedbok">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="reports" />
        <x-app-header current="reports" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <flux:button href="{{ route('reports.index') }}" variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-5 h-5" />
                </flux:button>
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.book-open class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Hovedbok
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Detaljert kontooversikt med alle transaksjoner
                    </flux:text>
                </div>
            </div>

            <!-- Filters -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.general-ledger') }}" class="flex flex-wrap gap-4 items-end">
                        <flux:field class="w-40">
                            <flux:label>Fra dato</flux:label>
                            <flux:input type="date" name="from_date" value="{{ $fromDate->format('Y-m-d') }}" />
                        </flux:field>
                        <flux:field class="w-40">
                            <flux:label>Til dato</flux:label>
                            <flux:input type="date" name="to_date" value="{{ $toDate->format('Y-m-d') }}" />
                        </flux:field>
                        <flux:field class="w-64">
                            <flux:label>Konto</flux:label>
                            <flux:select name="account_id">
                                <option value="">Alle kontoer</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ $accountId == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_number }} - {{ $account->name }}
                                    </option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                        <flux:button type="submit" variant="primary">
                            <flux:icon.funnel class="w-4 h-4 mr-2" />
                            Oppdater
                        </flux:button>
                    </form>
                </div>
            </flux:card>

            <!-- Ledger -->
            @forelse($ledger as $item)
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <flux:badge variant="outline" class="font-mono">{{ $item['account']->account_number }}</flux:badge>
                                <flux:heading size="md" class="text-zinc-900 dark:text-white">{{ $item['account']->name }}</flux:heading>
                            </div>
                            <div class="text-right">
                                <flux:text class="text-sm text-zinc-500">IB: {{ number_format($item['opening_balance'], 2, ',', ' ') }}</flux:text>
                            </div>
                        </div>

                        @if(count($item['entries']) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Dato</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Bilag</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Beskrivelse</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Kontakt</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Debet</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Kredit</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach($item['entries'] as $entry)
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $entry['date']->format('d.m.Y') }}</td>
                                                <td class="px-4 py-2 text-sm font-mono text-zinc-600 dark:text-zinc-400">{{ $entry['voucher_number'] }}</td>
                                                <td class="px-4 py-2 text-sm text-zinc-900 dark:text-white">{{ $entry['description'] }}</td>
                                                <td class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $entry['contact'] }}</td>
                                                <td class="px-4 py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                                    {{ $entry['debit'] > 0 ? number_format($entry['debit'], 2, ',', ' ') : '' }}
                                                </td>
                                                <td class="px-4 py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                                    {{ $entry['credit'] > 0 ? number_format($entry['credit'], 2, ',', ' ') : '' }}
                                                </td>
                                                <td class="px-4 py-2 text-sm text-right font-mono font-medium text-zinc-900 dark:text-white">
                                                    {{ number_format($entry['balance'], 2, ',', ' ') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-zinc-50 dark:bg-zinc-800">
                                        <tr>
                                            <td colspan="4" class="px-4 py-2 text-sm font-medium text-zinc-900 dark:text-white">Sum / UB</td>
                                            <td class="px-4 py-2 text-sm text-right font-mono font-medium text-zinc-900 dark:text-white">
                                                {{ number_format($item['total_debit'], 2, ',', ' ') }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right font-mono font-medium text-zinc-900 dark:text-white">
                                                {{ number_format($item['total_credit'], 2, ',', ' ') }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right font-mono font-bold text-zinc-900 dark:text-white">
                                                {{ number_format($item['closing_balance'], 2, ',', ' ') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen bevegelser i perioden. IB/UB: {{ number_format($item['opening_balance'], 2, ',', ' ') }}</flux:text>
                        @endif
                    </div>
                </flux:card>
            @empty
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-12 text-center">
                        <flux:icon.book-open class="w-16 h-16 text-zinc-400 mx-auto mb-4" />
                        <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-2">Ingen data</flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                            Ingen bokforte bilag funnet i den valgte perioden.
                        </flux:text>
                    </div>
                </flux:card>
            @endforelse
        </flux:main>
    </div>
</x-layouts.app>
