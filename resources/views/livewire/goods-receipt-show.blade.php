<div>
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ $goodsReceipt->receipt_number }}</flux:heading>
                <flux:badge color="{{ $goodsReceipt->status_color }}">{{ $goodsReceipt->status_label }}</flux:badge>
            </div>
            <flux:text class="text-zinc-600 dark:text-zinc-400 mt-1">
                {{ $goodsReceipt->contact?->company_name ?? 'Ingen leverandor' }}
            </flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            @if($goodsReceipt->can_post)
                <flux:button wire:click="post" variant="primary" icon="check">
                    Bokfor
                </flux:button>
            @endif

            @if($goodsReceipt->status === 'posted')
                <flux:button wire:click="reverse" wire:confirm="Er du sikker pa at du vil reversere dette varemottaket? Dette vil reversere alle lagertransaksjoner." variant="danger" icon="arrow-uturn-left">
                    Reverser
                </flux:button>
            @endif

            @if($goodsReceipt->can_cancel)
                <flux:button wire:click="delete" wire:confirm="Er du sikker pa at du vil slette dette varemottaket?" variant="danger" icon="trash">
                    Slett
                </flux:button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Receipt Details -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-6">
                    <flux:heading size="base" class="mb-4 text-zinc-900 dark:text-white">Mottaksdetaljer</flux:heading>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Mottaksdato</flux:text>
                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $goodsReceipt->receipt_date?->format('d.m.Y') ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Lagerlokasjon</flux:text>
                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $goodsReceipt->stockLocation?->name ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Innkjopsordre</flux:text>
                            @if($goodsReceipt->purchaseOrder)
                                <a href="{{ route('purchasing.purchase-orders.show', $goodsReceipt->purchaseOrder) }}" class="font-mono text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                    {{ $goodsReceipt->purchaseOrder->po_number }}
                                </a>
                            @else
                                <flux:text class="font-medium text-zinc-900 dark:text-white">-</flux:text>
                            @endif
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Leverandors pakkseddel</flux:text>
                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $goodsReceipt->supplier_delivery_note ?? '-' }}</flux:text>
                        </div>
                    </div>

                    @if($goodsReceipt->notes)
                        <div class="mt-4">
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Merknader</flux:text>
                            <flux:text class="mt-1 text-zinc-900 dark:text-white whitespace-pre-line">{{ $goodsReceipt->notes }}</flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>

            <!-- Receipt Lines -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-6">
                    <flux:heading size="base" class="mb-4 text-zinc-900 dark:text-white">Mottakslinjer</flux:heading>

                    @if($goodsReceipt->lines->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Produkt</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Bestilt</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Mottatt</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Enhetskost</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Total verdi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($goodsReceipt->lines as $line)
                                        <tr wire:key="line-{{ $line->id }}">
                                            <td class="px-4 py-3">
                                                <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $line->product?->name ?? $line->description }}</flux:text>
                                                @if($line->product?->sku)
                                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $line->product->sku }}</flux:text>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ number_format($line->quantity_ordered ?? 0, 2, ',', ' ') }}</flux:text>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <flux:text class="font-medium text-zinc-900 dark:text-white">{{ number_format($line->quantity_received, 2, ',', ' ') }}</flux:text>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <flux:text class="text-zinc-900 dark:text-white">{{ number_format($line->unit_cost, 2, ',', ' ') }}</flux:text>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <flux:text class="font-medium text-zinc-900 dark:text-white">{{ number_format($line->quantity_received * $line->unit_cost, 2, ',', ' ') }}</flux:text>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <td colspan="2" class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-400">Totalt mottatt</td>
                                        <td class="px-4 py-3 text-right font-bold text-zinc-900 dark:text-white">{{ number_format($goodsReceipt->total_quantity, 2, ',', ' ') }}</td>
                                        <td class="px-4 py-3"></td>
                                        <td class="px-4 py-3 text-right font-bold text-zinc-900 dark:text-white">{{ number_format($goodsReceipt->total_value, 2, ',', ' ') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen mottakslinjer</flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Supplier Info -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-6">
                    <flux:heading size="base" class="mb-4 text-zinc-900 dark:text-white">Leverandor</flux:heading>

                    @if($goodsReceipt->contact)
                        <div class="space-y-2">
                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $goodsReceipt->contact->company_name }}</flux:text>
                            @if($goodsReceipt->contact->address)
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ $goodsReceipt->contact->address }}</flux:text>
                            @endif
                            @if($goodsReceipt->contact->postal_code || $goodsReceipt->contact->city)
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ $goodsReceipt->contact->postal_code }} {{ $goodsReceipt->contact->city }}</flux:text>
                            @endif
                        </div>
                    @else
                        <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen leverandor</flux:text>
                    @endif
                </div>
            </flux:card>

            <!-- Activity Log -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-6">
                    <flux:heading size="base" class="mb-4 text-zinc-900 dark:text-white">Aktivitet</flux:heading>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Opprettet av</flux:text>
                            <flux:text class="text-sm text-zinc-900 dark:text-white">{{ $goodsReceipt->creator?->name ?? '-' }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Opprettet</flux:text>
                            <flux:text class="text-sm text-zinc-900 dark:text-white">{{ $goodsReceipt->created_at->format('d.m.Y H:i') }}</flux:text>
                        </div>
                        @if($goodsReceipt->posted_at)
                            <div class="flex justify-between">
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Bokfort av</flux:text>
                                <flux:text class="text-sm text-zinc-900 dark:text-white">{{ $goodsReceipt->poster?->name ?? '-' }}</flux:text>
                            </div>
                            <div class="flex justify-between">
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Bokfort</flux:text>
                                <flux:text class="text-sm text-zinc-900 dark:text-white">{{ $goodsReceipt->posted_at->format('d.m.Y H:i') }}</flux:text>
                            </div>
                        @endif
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>
