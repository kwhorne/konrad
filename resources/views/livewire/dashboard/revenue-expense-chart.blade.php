<div>
    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                        Inntekter og kostnader
                    </flux:heading>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        Siste 12 måneder
                    </flux:text>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">Inntekter</flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-rose-500"></div>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">Kostnader</flux:text>
                    </div>
                </div>
            </div>

            @if(count($chartData) > 0 && collect($chartData)->sum('revenue') + collect($chartData)->sum('expenses') > 0)
                <flux:chart :value="$chartData" class="aspect-[3/1] min-h-[200px]">
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

                <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                    <div class="text-center">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Total inntekt</flux:text>
                        <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400">
                            {{ number_format($totalRevenue, 0, ',', ' ') }} kr
                        </flux:heading>
                    </div>
                    <div class="text-center">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Totale kostnader</flux:text>
                        <flux:heading size="lg" class="text-rose-600 dark:text-rose-400">
                            {{ number_format($totalExpenses, 0, ',', ' ') }} kr
                        </flux:heading>
                    </div>
                    <div class="text-center">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Resultat</flux:text>
                        <flux:heading size="lg" class="{{ $profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ number_format($profit, 0, ',', ' ') }} kr
                        </flux:heading>
                    </div>
                </div>
            @else
                <div class="aspect-[3/1] min-h-[200px] flex items-center justify-center">
                    <div class="text-center">
                        <flux:icon.chart-bar class="h-12 w-12 text-zinc-300 dark:text-zinc-600 mx-auto mb-3" />
                        <flux:text class="text-zinc-500 dark:text-zinc-400">
                            Ingen regnskapsdata ennå
                        </flux:text>
                        <flux:text class="text-sm text-zinc-400 dark:text-zinc-500">
                            Bokfør bilag for å se grafen
                        </flux:text>
                    </div>
                </div>
            @endif
        </div>
    </flux:card>
</div>
