<div class="space-y-6">

    {{-- KPI Strip --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Lagerverdi</p>
                    <p class="mt-2 text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($kpis->total_stock_value ?? 0, 0, ',', ' ') }}</p>
                </div>
                <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center shrink-0">
                    <flux:icon.currency-dollar class="w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400" />
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Produkter</p>
                    <p class="mt-2 text-xl font-bold text-zinc-900 dark:text-white">{{ $kpis->total_skus ?? 0 }}</p>
                </div>
                <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center shrink-0">
                    <flux:icon.archive-box class="w-4.5 h-4.5 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Enheter</p>
                    <p class="mt-2 text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($kpis->total_units ?? 0, 0, ',', ' ') }}</p>
                </div>
                <div class="w-9 h-9 rounded-lg bg-cyan-50 dark:bg-cyan-950/50 flex items-center justify-center shrink-0">
                    <flux:icon.cube class="w-4.5 h-4.5 text-cyan-600 dark:text-cyan-400" />
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Reservert</p>
                    <p class="mt-2 text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($kpis->total_reserved ?? 0, 0, ',', ' ') }}</p>
                </div>
                <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center shrink-0">
                    <flux:icon.lock-closed class="w-4.5 h-4.5 text-amber-600 dark:text-amber-400" />
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Bestillingspunkt</p>
                    @php $criticalCount = $criticalItems->count(); @endphp
                    <p class="mt-2 text-xl font-bold {{ $criticalCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }}">{{ $criticalCount }}</p>
                </div>
                <div class="w-9 h-9 rounded-lg {{ $criticalCount > 0 ? 'bg-red-50 dark:bg-red-950/50' : 'bg-zinc-50 dark:bg-zinc-800' }} flex items-center justify-center shrink-0">
                    <flux:icon.exclamation-triangle class="w-4.5 h-4.5 {{ $criticalCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-400' }}" />
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">Nullbeholdning</p>
                    @php $zeroCount = $kpis->zero_stock_count ?? 0; @endphp
                    <p class="mt-2 text-xl font-bold {{ $zeroCount > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-zinc-900 dark:text-white' }}">{{ $zeroCount }}</p>
                </div>
                <div class="w-9 h-9 rounded-lg {{ $zeroCount > 0 ? 'bg-orange-50 dark:bg-orange-950/50' : 'bg-zinc-50 dark:bg-zinc-800' }} flex items-center justify-center shrink-0">
                    <flux:icon.archive-box-x-mark class="w-4.5 h-4.5 {{ $zeroCount > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-zinc-400' }}" />
                </div>
            </div>
        </div>
    </div>

    {{-- Critical reorder alert --}}
    @if($criticalItems->count() > 0)
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-red-200 dark:border-red-800/60 overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-3.5 bg-red-50 dark:bg-red-950/30 border-b border-red-200 dark:border-red-800/60">
                <flux:icon.exclamation-triangle class="w-4.5 h-4.5 text-red-600 dark:text-red-400 shrink-0" />
                <p class="text-sm font-semibold text-red-800 dark:text-red-200">{{ $criticalItems->count() }} {{ $criticalItems->count() === 1 ? 'vare' : 'varer' }} under bestillingspunkt — bestilling anbefales</p>
                <flux:button href="{{ route('inventory.stock-levels', ['filter_below_reorder' => 1]) }}" variant="ghost" size="xs" class="ml-auto">Se alle</flux:button>
            </div>
            <table class="min-w-full divide-y divide-zinc-100 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                    <tr>
                        <th class="px-5 py-2.5 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Produkt</th>
                        <th class="px-5 py-2.5 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">Tilgjengelig</th>
                        <th class="px-5 py-2.5 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">Bestillingspunkt</th>
                        <th class="px-5 py-2.5 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">Mangler</th>
                        <th class="px-5 py-2.5 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($criticalItems as $item)
                        <tr class="{{ $item['is_critical'] ? 'bg-red-50/50 dark:bg-red-950/10' : '' }}">
                            <td class="px-5 py-3">
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $item['name'] }}</p>
                                @if($item['sku'])
                                    <p class="text-xs text-zinc-400 font-mono">{{ $item['sku'] }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="text-sm font-semibold {{ $item['is_critical'] ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}">
                                    {{ number_format($item['available'], 0, ',', ' ') }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-sm text-zinc-500">{{ number_format($item['reorder_point'], 0, ',', ' ') }}</td>
                            <td class="px-5 py-3 text-right">
                                <flux:badge color="{{ $item['is_critical'] ? 'red' : 'amber' }}" size="sm">
                                    +{{ number_format($item['shortfall'], 0, ',', ' ') }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <flux:button href="{{ route('purchasing.purchase-orders.create') }}" variant="ghost" size="xs">Bestill</flux:button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Open POs + Top value products --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

        {{-- Open purchase orders --}}
        <div class="lg:col-span-3 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center">
                        <flux:icon.shopping-cart class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Åpne innkjøpsordrer</p>
                        @if($openPurchaseOrders->count() > 0)
                            <p class="text-xs text-zinc-500">Totalverdi: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($openPurchaseOrdersTotal, 0, ',', ' ') }} kr</span></p>
                        @endif
                    </div>
                </div>
                <flux:button href="{{ route('purchasing.purchase-orders.index') }}" variant="ghost" size="xs">Se alle</flux:button>
            </div>
            @if($openPurchaseOrders->count() > 0)
                <table class="min-w-full divide-y divide-zinc-100 dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Bestilling</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Leverandør</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Forventet</th>
                            <th class="px-5 py-2.5 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">Beløp</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($openPurchaseOrders as $po)
                            @php
                                $isOverdue = $po->expected_date && $po->expected_date->isPast() && !in_array($po->status, ['received','cancelled']);
                            @endphp
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors cursor-pointer" onclick="window.location='{{ route('purchasing.purchase-orders.show', $po) }}'">
                                <td class="px-5 py-3">
                                    <span class="text-sm font-mono text-indigo-600 dark:text-indigo-400">{{ $po->po_number }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $po->contact?->company_name ?? '—' }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    @if($po->expected_date)
                                        <span class="text-sm {{ $isOverdue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-zinc-600 dark:text-zinc-400' }}">
                                            {{ $po->expected_date->format('d.m.Y') }}
                                            @if($isOverdue) <span class="text-xs">(forsinket)</span> @endif
                                        </span>
                                    @else
                                        <span class="text-sm text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ number_format($po->total ?? 0, 0, ',', ' ') }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <flux:badge color="{{ $po->status_color }}" size="sm">{{ $po->status_label }}</flux:badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <flux:icon.check-circle class="w-8 h-8 text-green-500 mb-2" />
                    <p class="text-sm text-zinc-500">Ingen åpne innkjøpsordrer</p>
                </div>
            @endif
        </div>

        {{-- Top value products (ABC analysis) --}}
        <div class="lg:col-span-2 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center">
                        <flux:icon.chart-bar class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Topp 5 etter verdi</p>
                </div>
                <flux:button href="{{ route('inventory.stock-levels') }}" variant="ghost" size="xs">Se alle</flux:button>
            </div>
            @if($topValueProducts->count() > 0)
                <div class="p-5 space-y-4">
                    @foreach($topValueProducts as $i => $product)
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-xs font-bold w-5 text-center shrink-0 {{ $i === 0 ? 'text-amber-500' : ($i === 1 ? 'text-zinc-400' : 'text-orange-700 dark:text-orange-600') }}">{{ chr(65 + $i) }}</span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-zinc-900 dark:text-white truncate">{{ $product['name'] }}</p>
                                        @if($product['sku'])
                                            <p class="text-xs text-zinc-400 font-mono">{{ $product['sku'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-3">
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ number_format($product['value'], 0, ',', ' ') }}</p>
                                    <p class="text-xs text-zinc-400">{{ $product['pct'] }}%</p>
                                </div>
                            </div>
                            <div class="h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $i === 0 ? 'bg-indigo-500' : ($i === 1 ? 'bg-indigo-400' : ($i === 2 ? 'bg-indigo-300' : 'bg-indigo-200')) }}"
                                     style="width: {{ $product['pct'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex items-center justify-center py-10">
                    <p class="text-sm text-zinc-500">Ingen lagervarer</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Recent transactions + receipts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Recent transactions --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-cyan-50 dark:bg-cyan-950/50 flex items-center justify-center">
                        <flux:icon.arrow-path class="w-4 h-4 text-cyan-600 dark:text-cyan-400" />
                    </div>
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Siste bevegelser</p>
                </div>
                <flux:button href="{{ route('inventory.transactions') }}" variant="ghost" size="xs">Se alle</flux:button>
            </div>
            @if($recentTransactions->count() > 0)
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($recentTransactions as $tx)
                        <div class="flex items-center justify-between px-5 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-7 h-7 rounded-full {{ $tx->quantity >= 0 ? 'bg-green-50 dark:bg-green-950/50' : 'bg-red-50 dark:bg-red-950/50' }} flex items-center justify-center shrink-0">
                                    @if($tx->quantity >= 0)
                                        <flux:icon.arrow-down class="w-3.5 h-3.5 text-green-600 dark:text-green-400" />
                                    @else
                                        <flux:icon.arrow-up class="w-3.5 h-3.5 text-red-600 dark:text-red-400" />
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white truncate">{{ $tx->product?->name ?? '—' }}</p>
                                    <p class="text-xs text-zinc-400">{{ $tx->stockLocation?->name }} · {{ $tx->transaction_date?->format('d.m.Y') }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0 ml-3">
                                <p class="text-sm font-semibold {{ $tx->quantity >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $tx->quantity >= 0 ? '+' : '' }}{{ number_format($tx->quantity, 0, ',', ' ') }}
                                </p>
                                <p class="text-xs text-zinc-400">{{ $tx->type_label }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <flux:icon.arrow-path class="w-8 h-8 text-zinc-300 dark:text-zinc-600 mb-2" />
                    <p class="text-sm text-zinc-500">Ingen bevegelser ennå</p>
                </div>
            @endif
        </div>

        {{-- Recent goods receipts --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-950/50 flex items-center justify-center">
                        <flux:icon.truck class="w-4 h-4 text-green-600 dark:text-green-400" />
                    </div>
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Siste varemottak</p>
                </div>
                <flux:button href="{{ route('purchasing.goods-receipts.index') }}" variant="ghost" size="xs">Se alle</flux:button>
            </div>
            @if($recentReceipts->count() > 0)
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($recentReceipts as $receipt)
                        <a href="{{ route('purchasing.goods-receipts.show', $receipt) }}"
                           class="flex items-center justify-between px-5 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-green-50 dark:bg-green-950/50 flex items-center justify-center shrink-0">
                                    <flux:icon.inbox-arrow-down class="w-3.5 h-3.5 text-green-600 dark:text-green-400" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-mono font-medium text-indigo-600 dark:text-indigo-400">{{ $receipt->receipt_number }}</p>
                                    <p class="text-xs text-zinc-400 truncate">{{ $receipt->contact?->company_name ?? '—' }} · {{ $receipt->receipt_date->format('d.m.Y') }}</p>
                                </div>
                            </div>
                            <div class="shrink-0 ml-3">
                                <flux:badge color="{{ $receipt->status_color }}" size="sm">{{ $receipt->status_label }}</flux:badge>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <flux:icon.truck class="w-8 h-8 text-zinc-300 dark:text-zinc-600 mb-2" />
                    <p class="text-sm text-zinc-500">Ingen varemottak ennå</p>
                </div>
            @endif
        </div>
    </div>

</div>
