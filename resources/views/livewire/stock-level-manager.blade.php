<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk etter produkt..." icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:select wire:model.live="filterLocation" class="w-full sm:w-48">
                <option value="">Alle lokasjoner</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                @endforeach
            </flux:select>

            <flux:checkbox wire:model.live="filterBelowReorder" label="Kun under bestillingspunkt" />
        </div>
    </div>

    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-4 sm:p-6">
            @if($stockLevels->count() > 0)
                {{-- Desktop: Table --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Produkt</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Lokasjon</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Pa lager</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Reservert</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Tilgjengelig</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Gj.snitt kost</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Verdi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($stockLevels as $level)
                                @php
                                    $available = $level->quantity_on_hand - $level->quantity_reserved;
                                    $isBelowReorder = $level->product?->reorder_point && $available <= $level->product->reorder_point;
                                @endphp
                                <tr wire:key="level-{{ $level->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors {{ $isBelowReorder ? 'bg-amber-50 dark:bg-amber-900/20' : '' }}">
                                    <td class="px-6 py-4">
                                        <div>
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $level->product?->name ?? 'Ukjent' }}</flux:text>
                                            @if($level->product?->sku)
                                                <flux:text class="text-sm text-zinc-500">{{ $level->product->sku }}</flux:text>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $level->stockLocation?->name ?? '-' }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ number_format($level->quantity_on_hand, 2, ',', ' ') }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if($level->quantity_reserved > 0)
                                            <flux:badge color="amber">{{ number_format($level->quantity_reserved, 2, ',', ' ') }}</flux:badge>
                                        @else
                                            <flux:text class="text-zinc-400">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium {{ $isBelowReorder ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-900 dark:text-white' }}">
                                            {{ number_format($available, 2, ',', ' ') }}
                                        </flux:text>
                                        @if($isBelowReorder)
                                            <flux:text class="text-xs text-amber-600">Under bestillingspunkt</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ number_format($level->average_cost ?? 0, 2, ',', ' ') }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ number_format($level->total_value ?? 0, 2, ',', ' ') }}</flux:text>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile: Cards --}}
                <div class="lg:hidden space-y-3">
                    @foreach($stockLevels as $level)
                        @php
                            $available = $level->quantity_on_hand - $level->quantity_reserved;
                            $isBelowReorder = $level->product?->reorder_point && $available <= $level->product->reorder_point;
                        @endphp
                        <div wire:key="level-card-{{ $level->id }}" class="p-4 rounded-lg border {{ $isBelowReorder ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800' : 'bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700' }}">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex-1 min-w-0">
                                    <flux:text class="font-medium text-zinc-900 dark:text-white truncate">{{ $level->product?->name ?? 'Ukjent' }}</flux:text>
                                    @if($level->product?->sku)
                                        <flux:text class="text-sm text-zinc-500">{{ $level->product->sku }}</flux:text>
                                    @endif
                                </div>
                                @if($isBelowReorder)
                                    <flux:badge color="amber" size="sm">Lavt</flux:badge>
                                @endif
                            </div>

                            @if($level->stockLocation?->name)
                                <div class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">
                                    <flux:icon.map-pin class="w-4 h-4 inline-block mr-1" />
                                    {{ $level->stockLocation->name }}
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-zinc-500 dark:text-zinc-400">På lager:</span>
                                    <span class="ml-1 font-medium text-zinc-900 dark:text-white">{{ number_format($level->quantity_on_hand, 2, ',', ' ') }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-500 dark:text-zinc-400">Tilgjengelig:</span>
                                    <span class="ml-1 font-medium {{ $isBelowReorder ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-900 dark:text-white' }}">{{ number_format($available, 2, ',', ' ') }}</span>
                                </div>
                                @if($level->quantity_reserved > 0)
                                    <div>
                                        <span class="text-zinc-500 dark:text-zinc-400">Reservert:</span>
                                        <span class="ml-1 text-amber-600 dark:text-amber-400">{{ number_format($level->quantity_reserved, 2, ',', ' ') }}</span>
                                    </div>
                                @endif
                                <div>
                                    <span class="text-zinc-500 dark:text-zinc-400">Verdi:</span>
                                    <span class="ml-1 font-medium text-zinc-900 dark:text-white">{{ number_format($level->total_value ?? 0, 2, ',', ' ') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $stockLevels->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.archive-box class="mx-auto h-12 w-12 text-zinc-400" />
                    <flux:heading size="base" class="mt-2 text-zinc-900 dark:text-white">Ingen beholdning</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Ingen lagerbeholdning funnet med gjeldende filtre.</flux:text>
                </div>
            @endif
        </div>
    </flux:card>
</div>
