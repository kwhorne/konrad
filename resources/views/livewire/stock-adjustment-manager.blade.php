<div>
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 max-w-2xl">
        <div class="p-6">
            <flux:heading size="lg" class="mb-6">Manuell lagerjustering</flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:field>
                    <flux:label>Produkt</flux:label>
                    <flux:select wire:model="product_id">
                        <option value="">Velg produkt...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->sku ? $product->sku . ' - ' : '' }}{{ $product->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="product_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Lokasjon</flux:label>
                    <flux:select wire:model="stock_location_id">
                        <option value="">Velg lokasjon...</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->code }} - {{ $location->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="stock_location_id" />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Antall</flux:label>
                        <flux:input type="number" wire:model="quantity" step="0.01" placeholder="Positivt = okning, negativt = reduksjon" />
                        <flux:description>Positivt antall oker beholdningen, negativt reduserer.</flux:description>
                        <flux:error name="quantity" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Enhetskost (valgfritt)</flux:label>
                        <flux:input type="number" wire:model="unit_cost" step="0.01" placeholder="Bruker eksisterende hvis tom" />
                        <flux:description>La sta tom for a bruke eksisterende gjennomsnittskost.</flux:description>
                        <flux:error name="unit_cost" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Begrunnelse</flux:label>
                    <flux:textarea wire:model="notes" rows="3" placeholder="Beskriv arsaken til justeringen..." />
                    <flux:error name="notes" />
                </flux:field>

                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                    <flux:text class="text-amber-800 dark:text-amber-200 text-sm">
                        <strong>Viktig:</strong> Lagerjusteringer pavirker umiddelbart lagerbeholdningen og oppretter transaksjonshistorikk. Kontroller at informasjonen er korrekt for lagring.
                    </flux:text>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button href="{{ route('inventory.stock-levels') }}" variant="ghost">Avbryt</flux:button>
                    <flux:button type="submit" variant="primary">Opprett justering</flux:button>
                </div>
            </form>
        </div>
    </flux:card>
</div>
