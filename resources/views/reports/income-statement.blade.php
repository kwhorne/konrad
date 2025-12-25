<x-layouts.app title="Resultatregnskap">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="reports" />
        <x-app-header current="reports" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <flux:button href="{{ route('reports.index') }}" variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-5 h-5" />
                </flux:button>
                <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.arrow-trending-up class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Resultatregnskap
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Inntekter og kostnader for perioden
                    </flux:text>
                </div>
            </div>

            <!-- Filters -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.income-statement') }}" class="flex flex-wrap gap-4 items-end">
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

            <!-- Income Statement -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-6">
                    <div class="max-w-3xl mx-auto">
                        <table class="min-w-full">
                            <tbody>
                                <!-- Revenues -->
                                <tr class="bg-green-50 dark:bg-green-900/20">
                                    <td colspan="2" class="px-4 py-3 text-sm font-bold text-green-800 dark:text-green-300">
                                        Driftsinntekter
                                    </td>
                                </tr>
                                @foreach($statement['revenues'] as $item)
                                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                        <td class="px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300">
                                            <span class="font-mono text-zinc-500 mr-2">{{ $item['account']->account_number }}</span>
                                            {{ $item['account']->name }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                            {{ number_format($item['amount'], 2, ',', ' ') }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-green-100 dark:bg-green-900/30">
                                    <td class="px-4 py-2 text-sm font-medium text-green-800 dark:text-green-300">Sum driftsinntekter</td>
                                    <td class="px-4 py-2 text-sm text-right font-mono font-bold text-green-800 dark:text-green-300">
                                        {{ number_format($statement['total_revenue'], 2, ',', ' ') }}
                                    </td>
                                </tr>

                                <!-- Cost of goods -->
                                <tr><td colspan="2" class="py-2"></td></tr>
                                <tr class="bg-red-50 dark:bg-red-900/20">
                                    <td colspan="2" class="px-4 py-3 text-sm font-bold text-red-800 dark:text-red-300">
                                        Varekostnad
                                    </td>
                                </tr>
                                @foreach($statement['cost_of_goods'] as $item)
                                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                        <td class="px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300">
                                            <span class="font-mono text-zinc-500 mr-2">{{ $item['account']->account_number }}</span>
                                            {{ $item['account']->name }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                            {{ number_format($item['amount'], 2, ',', ' ') }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-red-100 dark:bg-red-900/30">
                                    <td class="px-4 py-2 text-sm font-medium text-red-800 dark:text-red-300">Sum varekostnad</td>
                                    <td class="px-4 py-2 text-sm text-right font-mono font-bold text-red-800 dark:text-red-300">
                                        {{ number_format($statement['total_cost_of_goods'], 2, ',', ' ') }}
                                    </td>
                                </tr>

                                <!-- Gross profit -->
                                <tr class="bg-zinc-200 dark:bg-zinc-700">
                                    <td class="px-4 py-3 text-sm font-bold text-zinc-900 dark:text-white">Bruttofortjeneste</td>
                                    <td class="px-4 py-3 text-sm text-right font-mono font-bold text-zinc-900 dark:text-white">
                                        {{ number_format($statement['gross_profit'], 2, ',', ' ') }}
                                    </td>
                                </tr>

                                <!-- Operating costs -->
                                <tr><td colspan="2" class="py-2"></td></tr>
                                <tr class="bg-orange-50 dark:bg-orange-900/20">
                                    <td colspan="2" class="px-4 py-3 text-sm font-bold text-orange-800 dark:text-orange-300">
                                        Driftskostnader
                                    </td>
                                </tr>

                                <!-- Payroll -->
                                @if($statement['payroll_costs']->count() > 0)
                                    <tr class="bg-zinc-50 dark:bg-zinc-800">
                                        <td colspan="2" class="px-4 py-2 text-sm font-medium text-zinc-600 dark:text-zinc-400">Lonn og personal</td>
                                    </tr>
                                    @foreach($statement['payroll_costs'] as $item)
                                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                            <td class="px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 pl-8">
                                                <span class="font-mono text-zinc-500 mr-2">{{ $item['account']->account_number }}</span>
                                                {{ $item['account']->name }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                                {{ number_format($item['amount'], 2, ',', ' ') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                <!-- Depreciation -->
                                @if($statement['depreciation']->count() > 0)
                                    <tr class="bg-zinc-50 dark:bg-zinc-800">
                                        <td colspan="2" class="px-4 py-2 text-sm font-medium text-zinc-600 dark:text-zinc-400">Avskrivninger</td>
                                    </tr>
                                    @foreach($statement['depreciation'] as $item)
                                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                            <td class="px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 pl-8">
                                                <span class="font-mono text-zinc-500 mr-2">{{ $item['account']->account_number }}</span>
                                                {{ $item['account']->name }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                                {{ number_format($item['amount'], 2, ',', ' ') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                <!-- Other operating costs -->
                                @if($statement['other_operating_costs']->count() > 0)
                                    <tr class="bg-zinc-50 dark:bg-zinc-800">
                                        <td colspan="2" class="px-4 py-2 text-sm font-medium text-zinc-600 dark:text-zinc-400">Andre driftskostnader</td>
                                    </tr>
                                    @foreach($statement['other_operating_costs'] as $item)
                                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                            <td class="px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 pl-8">
                                                <span class="font-mono text-zinc-500 mr-2">{{ $item['account']->account_number }}</span>
                                                {{ $item['account']->name }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                                {{ number_format($item['amount'], 2, ',', ' ') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                <tr class="bg-orange-100 dark:bg-orange-900/30">
                                    <td class="px-4 py-2 text-sm font-medium text-orange-800 dark:text-orange-300">Sum driftskostnader</td>
                                    <td class="px-4 py-2 text-sm text-right font-mono font-bold text-orange-800 dark:text-orange-300">
                                        {{ number_format($statement['total_operating_costs'], 2, ',', ' ') }}
                                    </td>
                                </tr>

                                <!-- Operating profit -->
                                <tr class="bg-zinc-200 dark:bg-zinc-700">
                                    <td class="px-4 py-3 text-sm font-bold text-zinc-900 dark:text-white">Driftsresultat</td>
                                    <td class="px-4 py-3 text-sm text-right font-mono font-bold {{ $statement['operating_profit'] >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                                        {{ number_format($statement['operating_profit'], 2, ',', ' ') }}
                                    </td>
                                </tr>

                                <!-- Financial items -->
                                @if($statement['financial_items']->count() > 0)
                                    <tr><td colspan="2" class="py-2"></td></tr>
                                    <tr class="bg-blue-50 dark:bg-blue-900/20">
                                        <td colspan="2" class="px-4 py-3 text-sm font-bold text-blue-800 dark:text-blue-300">
                                            Finansposter
                                        </td>
                                    </tr>
                                    @foreach($statement['financial_items'] as $item)
                                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                            <td class="px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300">
                                                <span class="font-mono text-zinc-500 mr-2">{{ $item['account']->account_number }}</span>
                                                {{ $item['account']->name }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right font-mono text-zinc-900 dark:text-white">
                                                {{ number_format($item['amount'], 2, ',', ' ') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-blue-100 dark:bg-blue-900/30">
                                        <td class="px-4 py-2 text-sm font-medium text-blue-800 dark:text-blue-300">Sum finansposter</td>
                                        <td class="px-4 py-2 text-sm text-right font-mono font-bold text-blue-800 dark:text-blue-300">
                                            {{ number_format($statement['total_financial'], 2, ',', ' ') }}
                                        </td>
                                    </tr>
                                @endif

                                <!-- Profit before tax -->
                                <tr><td colspan="2" class="py-2"></td></tr>
                                <tr class="bg-zinc-900 dark:bg-zinc-100">
                                    <td class="px-4 py-4 text-base font-bold text-white dark:text-zinc-900">Arsresultat for skatt</td>
                                    <td class="px-4 py-4 text-base text-right font-mono font-bold {{ $statement['profit_before_tax'] >= 0 ? 'text-green-400 dark:text-green-700' : 'text-red-400 dark:text-red-700' }}">
                                        {{ number_format($statement['profit_before_tax'], 2, ',', ' ') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </flux:card>
        </flux:main>
    </div>
</x-layouts.app>
