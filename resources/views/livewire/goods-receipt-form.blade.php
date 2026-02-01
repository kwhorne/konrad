<div>
    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Receipt Info -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="base" class="mb-4 text-zinc-900 dark:text-white">Mottaksdetaljer</flux:heading>

                        @if($purchaseOrder)
                            <div class="mb-4 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                                <flux:text class="text-sm text-indigo-700 dark:text-indigo-300">
                                    Basert på innkjøpsordre: <span class="font-mono font-bold">{{ $purchaseOrder->po_number }}</span>
                                </flux:text>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if(!$purchaseOrder)
                                <flux:field class="col-span-2">
                                    <flux:label>Leverandør *</flux:label>
                                    <flux:select wire:model="contact_id">
                                        <option value="">Velg leverandør</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->company_name }}</option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="contact_id" />
                                </flux:field>
                            @else
                                <flux:field class="col-span-2">
                                    <flux:label>Leverandør</flux:label>
                                    <flux:input value="{{ $purchaseOrder->contact?->company_name }}" disabled />
                                </flux:field>
                            @endif

                            <flux:field>
                                <flux:label>Lagerlokasjon *</flux:label>
                                <flux:select wire:model="stock_location_id" :disabled="(bool) $purchaseOrder">
                                    <option value="">Velg lokasjon</option>
                                    @foreach($stockLocations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="stock_location_id" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Mottaksdato *</flux:label>
                                <flux:input type="date" wire:model="receipt_date" />
                                <flux:error name="receipt_date" />
                            </flux:field>

                            <flux:field class="col-span-2">
                                <flux:label>Leverandørs pakkseddel</flux:label>
                                <flux:input wire:model="supplier_delivery_note" placeholder="Referanse fra leverandør" />
                                <flux:error name="supplier_delivery_note" />
                            </flux:field>
                        </div>
                    </div>
                </flux:card>

                <!-- Lines Section -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <flux:heading size="base" class="text-zinc-900 dark:text-white">Mottakslinjer</flux:heading>
                            @if(!$purchaseOrder)
                                <flux:button type="button" wire:click="openLineModal" variant="primary" size="sm" icon="plus">
                                    Legg til linje
                                </flux:button>
                            @endif
                        </div>

                        @if($purchaseOrder)
                            <!-- PO-based lines -->
                            @if(count($receiptLines) > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Produkt</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Bestilt</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Tidligere mottatt</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Utstaende</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Mottas na</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Enhetskost</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                            @foreach($receiptLines as $index => $line)
                                                <tr wire:key="po-line-{{ $index }}">
                                                    <td class="px-4 py-3">
                                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $line['product_name'] }}</flux:text>
                                                        @if($line['product_sku'])
                                                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $line['product_sku'] }}</flux:text>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ number_format($line['ordered'], 2, ',', ' ') }}</flux:text>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ number_format($line['previously_received'], 2, ',', ' ') }}</flux:text>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ number_format($line['outstanding'], 2, ',', ' ') }}</flux:text>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <flux:input
                                                            type="number"
                                                            step="0.01"
                                                            min="0"
                                                            max="{{ $line['outstanding'] }}"
                                                            wire:model="receiptLines.{{ $index }}.quantity"
                                                            class="w-24 text-right"
                                                        />
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <flux:input
                                                            type="number"
                                                            step="0.01"
                                                            min="0"
                                                            wire:model="receiptLines.{{ $index }}.unit_cost"
                                                            class="w-24 text-right"
                                                        />
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-8 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                    <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen utstaende linjer a motta</flux:text>
                                </div>
                            @endif
                        @else
                            <!-- Standalone lines -->
                            @if(count($manualLines) > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Produkt</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Antall</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Enhetskost</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Total</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Handlinger</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                            @foreach($manualLines as $index => $line)
                                                <tr wire:key="manual-line-{{ $index }}">
                                                    <td class="px-4 py-3">
                                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $line['description'] }}</flux:text>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <flux:text class="text-zinc-900 dark:text-white">{{ number_format($line['quantity'], 2, ',', ' ') }}</flux:text>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <flux:text class="text-zinc-900 dark:text-white">{{ number_format($line['unit_cost'], 2, ',', ' ') }}</flux:text>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ number_format($line['quantity'] * $line['unit_cost'], 2, ',', ' ') }}</flux:text>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <div class="flex justify-end gap-1">
                                                            <flux:button type="button" wire:click="openLineModal({{ $index }})" variant="ghost" size="sm" icon="pencil" />
                                                            <flux:button type="button" wire:click="deleteLine({{ $index }})" variant="ghost" size="sm" icon="trash" class="text-red-500" />
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-8 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                    <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen linjer lagt til ennå</flux:text>
                                    <flux:button type="button" wire:click="openLineModal" variant="primary" size="sm" icon="plus" class="mt-2">
                                        Legg til første linje
                                    </flux:button>
                                </div>
                            @endif
                        @endif
                    </div>
                </flux:card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Notes -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="base" class="mb-4 text-zinc-900 dark:text-white">Merknader</flux:heading>

                        <flux:field>
                            <flux:textarea wire:model="notes" rows="4" placeholder="Eventuelle merknader om mottaket" />
                            <flux:error name="notes" />
                        </flux:field>
                    </div>
                </flux:card>

                <!-- Select PO (if not already selected) -->
                @if(!$purchaseOrder && $openPurchaseOrders->count() > 0)
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="p-6">
                            <flux:heading size="base" class="mb-4 text-zinc-900 dark:text-white">Åpne innkjøpsordrer</flux:heading>

                            <div class="space-y-2">
                                @foreach($openPurchaseOrders as $po)
                                    <a href="{{ route('purchasing.goods-receipts.create') }}?po={{ $po->id }}" class="flex justify-between items-center p-3 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 transition-colors">
                                        <div>
                                            <flux:text class="font-mono text-indigo-600 dark:text-indigo-400">{{ $po->po_number }}</flux:text>
                                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $po->contact?->company_name }}</flux:text>
                                        </div>
                                        <flux:badge color="{{ $po->status_color }}">{{ $po->status_label }}</flux:badge>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </flux:card>
                @endif

                <!-- Actions -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="space-y-3">
                            <flux:button type="submit" variant="primary" class="w-full">
                                Opprett varemottak
                            </flux:button>

                            <flux:button href="{{ route('purchasing.goods-receipts.index') }}" variant="ghost" class="w-full">
                                Avbryt
                            </flux:button>
                        </div>
                    </div>
                </flux:card>
            </div>
        </div>
    </form>

    <!-- Line Modal (for standalone receipts) -->
    <flux:modal wire:model="showLineModal" class="max-w-xl">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4 text-zinc-900 dark:text-white">
                {{ $editingLineIndex !== null ? 'Rediger linje' : 'Legg til linje' }}
            </flux:heading>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Produkt</flux:label>
                    <flux:select wire:model.live="line_product_id">
                        <option value="">Velg produkt (valgfritt)</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Beskrivelse *</flux:label>
                    <flux:input wire:model="line_description" />
                    <flux:error name="line_description" />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Antall *</flux:label>
                        <flux:input type="number" step="0.01" wire:model="line_quantity" />
                        <flux:error name="line_quantity" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Enhetskost *</flux:label>
                        <flux:input type="number" step="0.01" wire:model="line_unit_cost" />
                        <flux:error name="line_unit_cost" />
                    </flux:field>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="closeLineModal" variant="ghost">
                    Avbryt
                </flux:button>
                <flux:button type="button" wire:click="saveLine" variant="primary">
                    {{ $editingLineIndex !== null ? 'Oppdater' : 'Legg til' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
