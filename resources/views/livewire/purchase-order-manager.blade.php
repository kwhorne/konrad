<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Sok etter PO-nummer eller leverandor..." icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:select wire:model.live="filterStatus" class="w-full sm:w-48">
                <option value="">Alle statuser</option>
                <option value="draft">Utkast</option>
                <option value="pending_approval">Til godkjenning</option>
                <option value="approved">Godkjent</option>
                <option value="sent">Sendt</option>
                <option value="partially_received">Delvis mottatt</option>
                <option value="received">Mottatt</option>
                <option value="cancelled">Kansellert</option>
            </flux:select>
        </div>
    </div>

    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            @if($purchaseOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">PO-nummer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Leverandor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Dato</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Handlinger</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($purchaseOrders as $po)
                                <tr wire:key="po-{{ $po->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('purchasing.purchase-orders.show', $po) }}" class="font-mono text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                            {{ $po->po_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $po->contact?->company_name ?? '-' }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $po->order_date?->format('d.m.Y') }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <flux:badge color="{{ $po->status_color }}">{{ $po->status_label }}</flux:badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ number_format($po->total ?? 0, 2, ',', ' ') }}</flux:text>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                            <flux:menu>
                                                <flux:menu.item href="{{ route('purchasing.purchase-orders.show', $po) }}" icon="eye">
                                                    Vis
                                                </flux:menu.item>
                                                @if($po->can_edit)
                                                    <flux:menu.item href="{{ route('purchasing.purchase-orders.edit', $po) }}" icon="pencil">
                                                        Rediger
                                                    </flux:menu.item>
                                                @endif
                                                @if($po->can_approve)
                                                    <flux:menu.item wire:click="approve({{ $po->id }})" icon="check">
                                                        Godkjenn
                                                    </flux:menu.item>
                                                @endif
                                                @if($po->can_send)
                                                    <flux:menu.item wire:click="markAsSent({{ $po->id }})" icon="paper-airplane">
                                                        Merk som sendt
                                                    </flux:menu.item>
                                                @endif
                                                @if($po->can_receive)
                                                    <flux:menu.item href="{{ route('purchasing.goods-receipts.create') }}?po={{ $po->id }}" icon="archive-box-arrow-down">
                                                        Registrer mottak
                                                    </flux:menu.item>
                                                @endif
                                                @if($po->can_cancel)
                                                    <flux:menu.separator />
                                                    <flux:menu.item wire:click="cancel({{ $po->id }})" wire:confirm="Er du sikker pa at du vil kansellere denne innkjopsordren?" icon="x-mark" class="text-red-600">
                                                        Kanseller
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
                    {{ $purchaseOrders->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon.document-text class="mx-auto h-12 w-12 text-zinc-400" />
                    <flux:heading size="base" class="mt-2 text-zinc-900 dark:text-white">Ingen innkjopsordrer</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Kom i gang ved a opprette din forste innkjopsordre.</flux:text>
                    <div class="mt-6">
                        <flux:button href="{{ route('purchasing.purchase-orders.create') }}" variant="primary">
                            <flux:icon.plus class="w-5 h-5 mr-2" />
                            Ny innkjopsordre
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </flux:card>
</div>
