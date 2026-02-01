<div>
    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="base" class="mb-4 text-zinc-900 dark:text-white">Ordredetaljer</flux:heading>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field class="col-span-2">
                                <flux:label>Leverandor *</flux:label>
                                <flux:select wire:model="contact_id">
                                    <option value="">Velg leverandor</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->company_name }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="contact_id" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Mottakslokasjon *</flux:label>
                                <flux:select wire:model="stock_location_id">
                                    <option value="">Velg lokasjon</option>
                                    @foreach($stockLocations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="stock_location_id" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Leverandorreferanse</flux:label>
                                <flux:input wire:model="supplier_reference" placeholder="f.eks. tilbudsnummer" />
                                <flux:error name="supplier_reference" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Ordredato *</flux:label>
                                <flux:input type="date" wire:model="order_date" />
                                <flux:error name="order_date" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Forventet levering</flux:label>
                                <flux:input type="date" wire:model="expected_date" />
                                <flux:error name="expected_date" />
                            </flux:field>

                            <flux:field class="col-span-2">
                                <flux:label>Leveringsadresse</flux:label>
                                <flux:textarea wire:model="shipping_address" rows="3" placeholder="Adresse for levering" />
                                <flux:error name="shipping_address" />
                            </flux:field>
                        </div>
                    </div>
                </flux:card>

                <!-- Lines Section -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <flux:heading size="base" class="text-zinc-900 dark:text-white">Ordrelinjer</flux:heading>
                            @if($purchaseOrder)
                                <flux:button type="button" wire:click="openLineModal" variant="primary" size="sm" icon="plus">
                                    Legg til linje
                                </flux:button>
                            @endif
                        </div>

                        @if(!$purchaseOrder)
                            <div class="text-center py-8 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                <flux:text class="text-zinc-500 dark:text-zinc-400">Lagre ordren forst for a legge til linjer</flux:text>
                            </div>
                        @elseif(count($lines) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Produkt</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Antall</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Pris</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Sum</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Handlinger</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach($lines as $line)
                                            <tr wire:key="line-{{ $line['id'] }}">
                                                <td class="px-4 py-3">
                                                    <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $line['description'] }}</flux:text>
                                                    @if(isset($line['product']))
                                                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $line['product']['sku'] ?? '' }}</flux:text>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <flux:text class="text-zinc-900 dark:text-white">{{ number_format($line['quantity'], 2, ',', ' ') }} {{ $line['unit'] }}</flux:text>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <flux:text class="text-zinc-900 dark:text-white">{{ number_format($line['unit_price'], 2, ',', ' ') }}</flux:text>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    @php
                                                        $lineTotal = $line['quantity'] * $line['unit_price'] * (1 - ($line['discount_percent'] ?? 0) / 100);
                                                    @endphp
                                                    <flux:text class="font-medium text-zinc-900 dark:text-white">{{ number_format($lineTotal, 2, ',', ' ') }}</flux:text>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <div class="flex justify-end gap-1">
                                                        <flux:button type="button" wire:click="openLineModal({{ $line['id'] }})" variant="ghost" size="sm" icon="pencil" />
                                                        <flux:button type="button" wire:click="deleteLine({{ $line['id'] }})" wire:confirm="Slett denne linjen?" variant="ghost" size="sm" icon="trash" class="text-red-500" />
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($purchaseOrder)
                                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                                    <div class="flex justify-end">
                                        <div class="w-64 space-y-2">
                                            <div class="flex justify-between">
                                                <flux:text class="text-zinc-600 dark:text-zinc-400">Subtotal</flux:text>
                                                <flux:text class="font-medium text-zinc-900 dark:text-white">{{ number_format($purchaseOrder->subtotal ?? 0, 2, ',', ' ') }}</flux:text>
                                            </div>
                                            <div class="flex justify-between">
                                                <flux:text class="text-zinc-600 dark:text-zinc-400">MVA</flux:text>
                                                <flux:text class="font-medium text-zinc-900 dark:text-white">{{ number_format($purchaseOrder->vat_total ?? 0, 2, ',', ' ') }}</flux:text>
                                            </div>
                                            <div class="flex justify-between pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                                <flux:text class="font-bold text-zinc-900 dark:text-white">Total</flux:text>
                                                <flux:text class="font-bold text-zinc-900 dark:text-white">{{ number_format($purchaseOrder->total ?? 0, 2, ',', ' ') }}</flux:text>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-8 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen linjer lagt til enna</flux:text>
                                <flux:button type="button" wire:click="openLineModal" variant="primary" size="sm" icon="plus" class="mt-2">
                                    Legg til forste linje
                                </flux:button>
                            </div>
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

                        <div class="space-y-4">
                            <flux:field>
                                <flux:label>Merknader til leverandor</flux:label>
                                <flux:textarea wire:model="notes" rows="3" />
                                <flux:error name="notes" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Interne merknader</flux:label>
                                <flux:textarea wire:model="internal_notes" rows="3" />
                                <flux:error name="internal_notes" />
                            </flux:field>
                        </div>
                    </div>
                </flux:card>

                <!-- Actions -->
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <div class="space-y-3">
                            <flux:button type="submit" variant="primary" class="w-full">
                                {{ $purchaseOrder ? 'Oppdater innkjopsordre' : 'Opprett innkjopsordre' }}
                            </flux:button>

                            @if($purchaseOrder)
                                <flux:button href="{{ route('purchasing.purchase-orders.show', $purchaseOrder) }}" variant="ghost" class="w-full">
                                    Vis innkjopsordre
                                </flux:button>
                            @endif

                            <flux:button href="{{ route('purchasing.purchase-orders.index') }}" variant="ghost" class="w-full">
                                Avbryt
                            </flux:button>
                        </div>
                    </div>
                </flux:card>
            </div>
        </div>
    </form>

    <!-- Line Modal -->
    <flux:modal wire:model="showLineModal" class="max-w-xl">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4 text-zinc-900 dark:text-white">
                {{ $editingLineId ? 'Rediger linje' : 'Legg til linje' }}
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
                        <flux:label>Enhet</flux:label>
                        <flux:input wire:model="line_unit" />
                        <flux:error name="line_unit" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Enhetspris *</flux:label>
                        <flux:input type="number" step="0.01" wire:model="line_unit_price" />
                        <flux:error name="line_unit_price" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Rabatt %</flux:label>
                        <flux:input type="number" step="0.01" wire:model="line_discount_percent" />
                        <flux:error name="line_discount_percent" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>MVA-sats</flux:label>
                        <flux:select wire:model.live="line_vat_rate_id">
                            <option value="">Velg MVA-sats</option>
                            @foreach($vatRates as $vatRate)
                                <option value="{{ $vatRate->id }}">{{ $vatRate->name }} ({{ $vatRate->rate }}%)</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>MVA %</flux:label>
                        <flux:input type="number" step="0.01" wire:model="line_vat_percent" />
                        <flux:error name="line_vat_percent" />
                    </flux:field>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="closeLineModal" variant="ghost">
                    Avbryt
                </flux:button>
                <flux:button type="button" wire:click="saveLine" variant="primary">
                    {{ $editingLineId ? 'Oppdater' : 'Legg til' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
