<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk etter mottaksnummer eller leverandør..." icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:select wire:model.live="filterStatus" class="w-full sm:w-48">
                <option value="">Alle statuser</option>
                <option value="draft">Utkast</option>
                <option value="posted">Bokført</option>
                <option value="cancelled">Kansellert</option>
            </flux:select>
        </div>
    </div>

    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($receipts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Mottaksnummer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Leverandør</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Innkjøpsordre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Dato</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Lokasjon</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Handlinger</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($receipts as $receipt)
                                <tr wire:key="receipt-{{ $receipt->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('purchasing.goods-receipts.show', $receipt) }}" class="font-mono text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                            {{ $receipt->receipt_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $receipt->contact?->company_name ?? '-' }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($receipt->purchaseOrder)
                                            <a href="{{ route('purchasing.purchase-orders.show', $receipt->purchaseOrder) }}" class="font-mono text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                                {{ $receipt->purchaseOrder->po_number }}
                                            </a>
                                        @else
                                            <flux:text class="text-zinc-400">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $receipt->receipt_date?->format('d.m.Y') }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:badge color="{{ $receipt->status_color }}">{{ $receipt->status_label }}</flux:badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $receipt->stockLocation?->name ?? '-' }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                            <flux:menu>
                                                <flux:menu.item href="{{ route('purchasing.goods-receipts.show', $receipt) }}" icon="eye">
                                                    Vis
                                                </flux:menu.item>
                                                @if($receipt->status === 'draft')
                                                    <flux:menu.item wire:click="post({{ $receipt->id }})" icon="check">
                                                        Bokfør
                                                    </flux:menu.item>
                                                    <flux:menu.separator />
                                                    <flux:menu.item wire:click="delete({{ $receipt->id }})" wire:confirm="Er du sikker på at du vil slette dette varemottaket?" icon="trash" class="text-red-600">
                                                        Slett
                                                    </flux:menu.item>
                                                @endif
                                            </flux:menu>
                                        </flux:dropdown>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $receipts->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.archive-box-arrow-down class="mx-auto h-12 w-12 text-zinc-400" />
                    <flux:heading size="base" class="mt-2 text-zinc-900 dark:text-white">Ingen varemottak</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Kom i gang ved å registrere ditt første varemottak.</flux:text>
                    <div class="mt-6">
                        <flux:button href="{{ route('purchasing.goods-receipts.create') }}" variant="primary">
                            <flux:icon.plus class="w-5 h-5 mr-2" />
                            Nytt varemottak
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </flux:card>
</div>
