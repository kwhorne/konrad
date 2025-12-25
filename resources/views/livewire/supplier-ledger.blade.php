<div>
    <!-- Aging Summary Cards -->
    <div class="grid grid-cols-5 gap-4 mb-8">
        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-700">
            <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-1">Ikke forfalt</div>
            <div class="text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($this->aging['current']['total'], 2, ',', ' ') }}</div>
            <div class="text-xs text-zinc-400 mt-1">{{ $this->aging['current']['invoices']->count() }} fakturaer</div>
        </div>
        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-green-200 dark:border-green-800">
            <div class="text-sm text-green-600 dark:text-green-400 mb-1">1-30 dager</div>
            <div class="text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($this->aging['1-30']['total'], 2, ',', ' ') }}</div>
            <div class="text-xs text-zinc-400 mt-1">{{ $this->aging['1-30']['invoices']->count() }} fakturaer</div>
        </div>
        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-yellow-200 dark:border-yellow-800">
            <div class="text-sm text-yellow-600 dark:text-yellow-400 mb-1">31-60 dager</div>
            <div class="text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($this->aging['31-60']['total'], 2, ',', ' ') }}</div>
            <div class="text-xs text-zinc-400 mt-1">{{ $this->aging['31-60']['invoices']->count() }} fakturaer</div>
        </div>
        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-orange-200 dark:border-orange-800">
            <div class="text-sm text-orange-600 dark:text-orange-400 mb-1">61-90 dager</div>
            <div class="text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($this->aging['61-90']['total'], 2, ',', ' ') }}</div>
            <div class="text-xs text-zinc-400 mt-1">{{ $this->aging['61-90']['invoices']->count() }} fakturaer</div>
        </div>
        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-red-200 dark:border-red-800">
            <div class="text-sm text-red-600 dark:text-red-400 mb-1">Over 90 dager</div>
            <div class="text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($this->aging['90+']['total'], 2, ',', ' ') }}</div>
            <div class="text-xs text-zinc-400 mt-1">{{ $this->aging['90+']['invoices']->count() }} fakturaer</div>
        </div>
    </div>

    <!-- Total Balance -->
    <div class="bg-gradient-to-r from-orange-500 to-red-600 rounded-xl p-6 mb-8 text-white">
        <div class="text-sm opacity-80 mb-1">Total leverandørgjeld</div>
        <div class="text-3xl font-bold">{{ number_format($this->totalBalance, 2, ',', ' ') }} NOK</div>
    </div>

    <!-- Search -->
    <div class="mb-6">
        <flux:input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="Søk etter fakturanummer, leverandør..."
            class="max-w-md"
        />
    </div>

    <!-- Invoice Table -->
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($invoices->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th wire:click="sort('internal_number')" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-700 dark:hover:text-zinc-200">
                                    Intern nr.
                                    @if($sortBy === 'internal_number')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Lev.fakturanr.
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Leverandør
                                </th>
                                <th wire:click="sort('invoice_date')" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-700 dark:hover:text-zinc-200">
                                    Fakturadato
                                    @if($sortBy === 'invoice_date')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th wire:click="sort('due_date')" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-700 dark:hover:text-zinc-200">
                                    Forfall
                                    @if($sortBy === 'due_date')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th wire:click="sort('total')" class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-700 dark:hover:text-zinc-200">
                                    Totalbeløp
                                    @if($sortBy === 'total')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th wire:click="sort('balance')" class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-700 dark:hover:text-zinc-200">
                                    Utestående
                                    @if($sortBy === 'balance')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($invoices as $invoice)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-medium text-zinc-900 dark:text-white">
                                            {{ $invoice->internal_number }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-zinc-600 dark:text-zinc-400">
                                        {{ $invoice->invoice_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($invoice->contact)
                                            <div class="text-zinc-900 dark:text-white">{{ $invoice->contact->company_name }}</div>
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-zinc-600 dark:text-zinc-400">
                                        {{ $invoice->invoice_date?->format('d.m.Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="{{ $invoice->is_overdue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-zinc-600 dark:text-zinc-400' }}">
                                            {{ $invoice->due_date?->format('d.m.Y') }}
                                        </span>
                                        @if($invoice->is_overdue)
                                            <span class="text-xs text-red-500 ml-1">(forfalt)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-zinc-900 dark:text-white">
                                        {{ number_format($invoice->total, 2, ',', ' ') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-zinc-900 dark:text-white">
                                        {{ number_format($invoice->balance, 2, ',', ' ') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:badge color="{{ $invoice->status_color }}">
                                            {{ $invoice->status_label }}
                                        </flux:badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $invoices->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.check-circle class="h-16 w-16 text-green-400 mx-auto mb-4" />
                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                        Ingen utestående leverandørgjeld
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        Alle leverandørfakturaer er betalt
                    </flux:text>
                </div>
            @endif
        </div>
    </flux:card>
</div>
