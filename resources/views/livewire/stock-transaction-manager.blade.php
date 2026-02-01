<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Sok..." icon="magnifying-glass" class="w-full sm:w-48" />

            <flux:select wire:model.live="filterLocation" class="w-full sm:w-44">
                <option value="">Alle lokasjoner</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterType" class="w-full sm:w-44">
                <option value="">Alle typer</option>
                <option value="receipt">Mottak</option>
                <option value="issue">Uttak</option>
                <option value="transfer_in">Overforing inn</option>
                <option value="transfer_out">Overforing ut</option>
                <option value="adjustment_in">Justering inn</option>
                <option value="adjustment_out">Justering ut</option>
            </flux:select>

            <flux:input type="date" wire:model.live="dateFrom" class="w-full sm:w-40" />
            <flux:input type="date" wire:model.live="dateTo" class="w-full sm:w-40" />
        </div>
    </div>

    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($transactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Nummer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Dato</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Produkt</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Lokasjon</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Antall</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Enhetskost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Opprettet av</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($transactions as $tx)
                                <tr wire:key="tx-{{ $tx->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="font-mono text-zinc-900 dark:text-white">{{ $tx->transaction_number }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $tx->transaction_date?->format('d.m.Y H:i') }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:badge color="{{ $tx->type_color }}">{{ $tx->type_label }}</flux:badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $tx->product?->name ?? '-' }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $tx->stockLocation?->name ?? '-' }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium {{ $tx->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $tx->quantity >= 0 ? '+' : '' }}{{ number_format($tx->quantity, 2, ',', ' ') }}
                                        </flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ number_format($tx->unit_cost ?? 0, 2, ',', ' ') }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $tx->creator?->name ?? '-' }}</flux:text>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.arrow-path class="mx-auto h-12 w-12 text-zinc-400" />
                    <flux:heading size="base" class="mt-2 text-zinc-900 dark:text-white">Ingen transaksjoner</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Ingen lagertransaksjoner funnet med gjeldende filtre.</flux:text>
                </div>
            @endif
        </div>
    </flux:card>
</div>
