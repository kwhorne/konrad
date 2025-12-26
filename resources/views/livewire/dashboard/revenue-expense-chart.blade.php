<div>
    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <flux:heading size="base" level="2" class="text-zinc-900 dark:text-white">
                        Inntekter og kostnader
                    </flux:heading>
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                        Siste 12 måneder
                    </flux:text>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                        <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Inntekter</flux:text>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500"></div>
                        <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Kostnader</flux:text>
                    </div>
                </div>
            </div>

            @if(count($chartData) > 0 && collect($chartData)->sum('revenue') + collect($chartData)->sum('expenses') > 0)
                <flux:chart :value="$chartData" class="aspect-[4/1] min-h-[160px]">
                    <flux:chart.svg>
                        <flux:chart.line field="revenue" class="text-emerald-500 dark:text-emerald-400" />
                        <flux:chart.area field="revenue" class="text-emerald-500/10 dark:text-emerald-400/10" />
                        <flux:chart.line field="expenses" class="text-rose-500 dark:text-rose-400" />
                        <flux:chart.area field="expenses" class="text-rose-500/10 dark:text-rose-400/10" />

                        <flux:chart.axis axis="x" field="month">
                            <flux:chart.axis.line />
                            <flux:chart.axis.tick />
                        </flux:chart.axis>

                        <flux:chart.axis axis="y">
                            <flux:chart.axis.grid />
                            <flux:chart.axis.tick />
                        </flux:chart.axis>

                        <flux:chart.cursor />
                    </flux:chart.svg>

                    <flux:chart.tooltip>
                        <flux:chart.tooltip.heading field="month" />
                        <flux:chart.tooltip.value field="revenue" label="Inntekter" suffix=" kr" />
                        <flux:chart.tooltip.value field="expenses" label="Kostnader" suffix=" kr" />
                    </flux:chart.tooltip>
                </flux:chart>

                @php
                    $totalRevenue = collect($chartData)->sum('revenue');
                    $totalExpenses = collect($chartData)->sum('expenses');
                    $profit = $totalRevenue - $totalExpenses;
                @endphp

                <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <div class="text-center">
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Total inntekt</flux:text>
                        <flux:text class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                            {{ number_format($totalRevenue, 0, ',', ' ') }} kr
                        </flux:text>
                    </div>
                    <div class="text-center">
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Totale kostnader</flux:text>
                        <flux:text class="text-sm font-semibold text-rose-600 dark:text-rose-400">
                            {{ number_format($totalExpenses, 0, ',', ' ') }} kr
                        </flux:text>
                    </div>
                    <div class="text-center">
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Resultat</flux:text>
                        <flux:text class="text-sm font-semibold {{ $profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ number_format($profit, 0, ',', ' ') }} kr
                        </flux:text>
                    </div>
                </div>
            @else
                <div class="aspect-[4/1] min-h-[160px] flex items-center justify-center">
                    <div class="text-center">
                        <flux:icon.chart-bar class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mx-auto mb-2" />
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                            Ingen regnskapsdata ennå
                        </flux:text>
                        <flux:text class="text-xs text-zinc-400 dark:text-zinc-500">
                            Bokfør bilag for å se grafen
                        </flux:text>
                    </div>
                </div>
            @endif
        </div>
    </flux:card>
</div>
