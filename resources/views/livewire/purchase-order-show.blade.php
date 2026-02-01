<div>
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ $purchaseOrder->po_number }}</flux:heading>
                <flux:badge color="{{ $purchaseOrder->status_color }}">{{ $purchaseOrder->status_label }}</flux:badge>
            </div>
            <flux:text class="text-zinc-600 dark:text-zinc-400 mt-1">
                {{ $purchaseOrder->contact?->company_name ?? 'Ingen leverandor' }}
            </flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            @if($purchaseOrder->can_edit)
                <flux:button href="{{ route('purchasing.purchase-orders.edit', $purchaseOrder) }}" variant="primary" icon="pencil">
                    Rediger
                </flux:button>
            @endif

            @if($purchaseOrder->status === 'draft')
                <flux:button wire:click="submitForApproval" variant="filled" icon="paper-airplane">
                    Send til godkjenning
                </flux:button>
            @endif

            @if($purchaseOrder->can_approve)
                <flux:button wire:click="approve" variant="primary" icon="check">
                    Godkjenn
                </flux:button>
            @endif

            @if($purchaseOrder->can_send)
                <flux:button wire:click="markAsSent" variant="filled" icon="paper-airplane">
                    Merk som sendt
                </flux:button>
            @endif

            @if($purchaseOrder->can_receive)
                <flux:button href="{{ route('purchasing.goods-receipts.create') }}?po={{ $purchaseOrder->id }}" variant="primary" icon="archive-box-arrow-down">
                    Registrer mottak
                </flux:button>
            @endif

            @if($purchaseOrder->can_cancel)
                <flux:button wire:click="cancel" wire:confirm="Er du sikker pa at du vil kansellere denne innkjopsordren?" variant="danger" icon="x-mark">
                    Kanseller
                </flux:button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Details -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-6">
                    <flux:heading size="base" class="mb-4 text-zinc-900 dark:text-white">Ordredetaljer</flux:heading>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Ordredato</flux:text>
                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $purchaseOrder->order_date?->format('d.m.Y') ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Forventet levering</flux:text>
                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $purchaseOrder->expected_date?->format('d.m.Y') ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Mottakslokasjon</flux:text>
                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $purchaseOrder->stockLocation?->name ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Leverandorreferanse</flux:text>
                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $purchaseOrder->supplier_reference ?? '-' }}</flux:text>
                        </div>
                    </div>

                    @if($purchaseOrder->shipping_address)
                        <div class="mt-4">
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Leveringsadresse</flux:text>
                            <flux:text class="font-medium text-zinc-900 dark:text-white whitespace-pre-line">{{ $purchaseOrder->shipping_address }}</flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>

            <!-- Order Lines -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-6">
                    <flux:heading size="base" class="mb-4 text-zinc-900 dark:text-white">Ordrelinjer</flux:heading>

                    @if($purchaseOrder->lines->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Produkt</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Antall</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Mottatt</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Enhetspris</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">MVA</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Sum</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($purchaseOrder->lines as $line)
                                        <tr wire:key="line-{{ $line->id }}">
                                            <td class="px-4 py-3">
                                                <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $line->product?->name ?? $line->description }}</flux:text>
                                                @if($line->product && $line->description !== $line->product->name)
                                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $line->description }}</flux:text>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <flux:text class="text-zinc-900 dark:text-white">{{ number_format($line->quantity, 2, ',', ' ') }} {{ $line->unit }}</flux:text>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                @if($line->quantity_received > 0)
                                                    <flux:badge color="{{ $line->quantity_received >= $line->quantity ? 'green' : 'yellow' }}">
                                                        {{ number_format($line->quantity_received, 2, ',', ' ') }}
                                                    </flux:badge>
                                                @else
                                                    <flux:text class="text-zinc-400">0</flux:text>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <flux:text class="text-zinc-900 dark:text-white">{{ number_format($line->unit_price, 2, ',', ' ') }}</flux:text>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ number_format($line->vat_percent, 0) }}%</flux:text>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <flux:text class="font-medium text-zinc-900 dark:text-white">{{ number_format($line->line_total, 2, ',', ' ') }}</flux:text>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <td colspan="5" class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-400">Subtotal</td>
                                        <td class="px-4 py-3 text-right font-medium text-zinc-900 dark:text-white">{{ number_format($purchaseOrder->subtotal ?? 0, 2, ',', ' ') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-400">MVA</td>
                                        <td class="px-4 py-3 text-right font-medium text-zinc-900 dark:text-white">{{ number_format($purchaseOrder->vat_total ?? 0, 2, ',', ' ') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="px-4 py-3 text-right text-lg font-bold text-zinc-900 dark:text-white">Total</td>
                                        <td class="px-4 py-3 text-right text-lg font-bold text-zinc-900 dark:text-white">{{ number_format($purchaseOrder->total ?? 0, 2, ',', ' ') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen ordrelinjer</flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>

            <!-- Notes -->
            @if($purchaseOrder->notes || $purchaseOrder->internal_notes)
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="base" class="mb-4 text-zinc-900 dark:text-white">Merknader</flux:heading>

                        @if($purchaseOrder->notes)
                            <div class="mb-4">
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Merknader til leverandor</flux:text>
                                <flux:text class="mt-1 text-zinc-900 dark:text-white whitespace-pre-line">{{ $purchaseOrder->notes }}</flux:text>
                            </div>
                        @endif

                        @if($purchaseOrder->internal_notes)
                            <div>
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Interne merknader</flux:text>
                                <flux:text class="mt-1 text-zinc-900 dark:text-white whitespace-pre-line">{{ $purchaseOrder->internal_notes }}</flux:text>
                            </div>
                        @endif
                    </div>
                </flux:card>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Supplier Info -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-6">
                    <flux:heading size="base" class="mb-4 text-zinc-900 dark:text-white">Leverandor</flux:heading>

                    @if($purchaseOrder->contact)
                        <div class="space-y-2">
                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $purchaseOrder->contact->company_name }}</flux:text>
                            @if($purchaseOrder->contact->address)
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ $purchaseOrder->contact->address }}</flux:text>
                            @endif
                            @if($purchaseOrder->contact->postal_code || $purchaseOrder->contact->city)
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ $purchaseOrder->contact->postal_code }} {{ $purchaseOrder->contact->city }}</flux:text>
                            @endif
                            @if($purchaseOrder->contact->email)
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ $purchaseOrder->contact->email }}</flux:text>
                            @endif
                            @if($purchaseOrder->contact->phone)
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ $purchaseOrder->contact->phone }}</flux:text>
                            @endif
                        </div>
                    @else
                        <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen leverandor valgt</flux:text>
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
                            <flux:text class="text-sm text-zinc-900 dark:text-white">{{ $purchaseOrder->creator?->name ?? '-' }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Opprettet</flux:text>
                            <flux:text class="text-sm text-zinc-900 dark:text-white">{{ $purchaseOrder->created_at->format('d.m.Y H:i') }}</flux:text>
                        </div>
                        @if($purchaseOrder->approved_at)
                            <div class="flex justify-between">
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Godkjent av</flux:text>
                                <flux:text class="text-sm text-zinc-900 dark:text-white">{{ $purchaseOrder->approver?->name ?? '-' }}</flux:text>
                            </div>
                            <div class="flex justify-between">
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Godkjent</flux:text>
                                <flux:text class="text-sm text-zinc-900 dark:text-white">{{ $purchaseOrder->approved_at->format('d.m.Y H:i') }}</flux:text>
                            </div>
                        @endif
                        @if($purchaseOrder->sent_at)
                            <div class="flex justify-between">
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Sendt</flux:text>
                                <flux:text class="text-sm text-zinc-900 dark:text-white">{{ $purchaseOrder->sent_at->format('d.m.Y H:i') }}</flux:text>
                            </div>
                        @endif
                    </div>
                </div>
            </flux:card>

            <!-- Goods Receipts -->
            @if($purchaseOrder->goodsReceipts->count() > 0)
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="base" class="mb-4 text-zinc-900 dark:text-white">Varemottak</flux:heading>

                        <div class="space-y-2">
                            @foreach($purchaseOrder->goodsReceipts as $receipt)
                                <a href="{{ route('purchasing.goods-receipts.show', $receipt) }}" class="flex justify-between items-center p-2 rounded hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                    <div>
                                        <flux:text class="font-mono text-indigo-600 dark:text-indigo-400">{{ $receipt->receipt_number }}</flux:text>
                                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $receipt->receipt_date?->format('d.m.Y') }}</flux:text>
                                    </div>
                                    <flux:badge color="{{ $receipt->status_color }}">{{ $receipt->status_label }}</flux:badge>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </flux:card>
            @endif
        </div>
    </div>
</div>
