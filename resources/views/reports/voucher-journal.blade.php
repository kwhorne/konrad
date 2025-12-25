<x-layouts.app title="Bilagsjournal">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="reports" />
        <x-app-header current="reports" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <flux:button href="{{ route('reports.index') }}" variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-5 h-5" />
                </flux:button>
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.document-text class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Bilagsjournal
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Kronologisk oversikt over alle bokforte bilag
                    </flux:text>
                </div>
            </div>

            <!-- Filters -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.voucher-journal') }}" class="flex flex-wrap gap-4 items-end">
                        <flux:field class="w-40">
                            <flux:label>Fra dato</flux:label>
                            <flux:input type="date" name="from_date" value="{{ $fromDate->format('Y-m-d') }}" />
                        </flux:field>
                        <flux:field class="w-40">
                            <flux:label>Til dato</flux:label>
                            <flux:input type="date" name="to_date" value="{{ $toDate->format('Y-m-d') }}" />
                        </flux:field>
                        <flux:button type="submit" variant="primary">
                            <flux:icon.funnel class="w-4 h-4 mr-2" />
                            Oppdater
                        </flux:button>
                    </form>
                </div>
            </flux:card>

            <!-- Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <flux:card class="bg-white dark:bg-zinc-900 shadow border border-zinc-200 dark:border-zinc-700">
                    <div class="p-4">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Antall bilag</flux:text>
                        <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ $vouchers->count() }}</flux:heading>
                    </div>
                </flux:card>
                <flux:card class="bg-white dark:bg-zinc-900 shadow border border-zinc-200 dark:border-zinc-700">
                    <div class="p-4">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Sum debet</flux:text>
                        <flux:heading size="xl" class="text-zinc-900 dark:text-white font-mono">{{ number_format($vouchers->sum('total_debit'), 2, ',', ' ') }}</flux:heading>
                    </div>
                </flux:card>
                <flux:card class="bg-white dark:bg-zinc-900 shadow border border-zinc-200 dark:border-zinc-700">
                    <div class="p-4">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Sum kredit</flux:text>
                        <flux:heading size="xl" class="text-zinc-900 dark:text-white font-mono">{{ number_format($vouchers->sum('total_credit'), 2, ',', ' ') }}</flux:heading>
                    </div>
                </flux:card>
            </div>

            <!-- Journal -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-6">
                    @if($vouchers->count() > 0)
                        <div class="space-y-6">
                            @foreach($vouchers as $voucher)
                                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                                    <div class="bg-zinc-50 dark:bg-zinc-800 px-4 py-3 flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <flux:badge variant="outline" class="font-mono">{{ $voucher->voucher_number }}</flux:badge>
                                            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $voucher->voucher_date->format('d.m.Y') }}</flux:text>
                                            <flux:text class="text-zinc-900 dark:text-white font-medium">{{ $voucher->description }}</flux:text>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <flux:badge size="sm">{{ $voucher->voucher_type_label }}</flux:badge>
                                        </div>
                                    </div>
                                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                        <thead class="bg-zinc-50/50 dark:bg-zinc-800/50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase w-24">Konto</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Kontonavn</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Beskrivelse</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Kontakt</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase w-32">Debet</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase w-32">Kredit</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                            @foreach($voucher->lines as $line)
                                                <tr>
                                                    <td class="px-4 py-2 text-sm font-mono text-zinc-600 dark:text-zinc-400">{{ $line->account->account_number }}</td>
                                                    <td class="px-4 py-2 text-sm text-zinc-900 dark:text-white">{{ $line->account->name }}</td>
                                                    <td class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $line->description }}</td>
                                                    <td class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $line->contact?->company_name }}</td>
                                                    <td class="px-4 py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                                        {{ $line->debit > 0 ? number_format($line->debit, 2, ',', ' ') : '' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                                        {{ $line->credit > 0 ? number_format($line->credit, 2, ',', ' ') : '' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-zinc-50/50 dark:bg-zinc-800/50">
                                            <tr>
                                                <td colspan="4" class="px-4 py-2 text-sm font-medium text-zinc-900 dark:text-white">Sum</td>
                                                <td class="px-4 py-2 text-sm text-right font-mono font-medium text-zinc-900 dark:text-white">
                                                    {{ number_format($voucher->total_debit, 2, ',', ' ') }}
                                                </td>
                                                <td class="px-4 py-2 text-sm text-right font-mono font-medium text-zinc-900 dark:text-white">
                                                    {{ number_format($voucher->total_credit, 2, ',', ' ') }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <flux:icon.document-text class="w-16 h-16 text-zinc-400 mx-auto mb-4" />
                            <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-2">Ingen bilag</flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                Ingen bokforte bilag funnet i den valgte perioden.
                            </flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>
        </flux:main>
    </div>
</x-layouts.app>
