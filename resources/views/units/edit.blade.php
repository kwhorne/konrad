<x-layouts.app title="Rediger enhet">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="products" />
        <x-app-header current="products" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="max-w-2xl mx-auto">
                <div class="flex items-center gap-4 mb-8">
                    <flux:button href="{{ route('units.index') }}" variant="ghost" size="sm">
                        <flux:icon.arrow-left class="w-5 h-5" />
                    </flux:button>
                    <div>
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            Rediger enhet
                        </flux:heading>
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                            {{ $unit->name }}
                        </flux:text>
                    </div>
                </div>

                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <form action="{{ route('units.update', $unit) }}" method="POST" class="p-6 space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label for="name">Navn *</flux:label>
                                <flux:input id="name" name="name" type="text" value="{{ old('name', $unit->name) }}" required />
                                @error('name')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label for="code">Kode *</flux:label>
                                <flux:input id="code" name="code" type="text" value="{{ old('code', $unit->code) }}" required />
                                @error('code')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label for="symbol">Symbol</flux:label>
                            <flux:input id="symbol" name="symbol" type="text" value="{{ old('symbol', $unit->symbol) }}" />
                            @error('symbol')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label for="description">Beskrivelse</flux:label>
                            <flux:textarea id="description" name="description" rows="3">{{ old('description', $unit->description) }}</flux:textarea>
                            @error('description')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label for="sort_order">Rekkefølge</flux:label>
                            <flux:input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $unit->sort_order) }}" />
                            <flux:description>Lavere tall vises først</flux:description>
                            @error('sort_order')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <flux:field>
                            <flux:checkbox id="is_active" name="is_active" value="1" label="Aktiv" :checked="old('is_active', $unit->is_active)" />
                        </flux:field>

                        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button href="{{ route('units.index') }}" variant="ghost">
                                Avbryt
                            </flux:button>
                            <flux:button type="submit" variant="primary">
                                Lagre endringer
                            </flux:button>
                        </div>
                    </form>
                </flux:card>
            </div>
        </flux:main>
    </div>
</x-layouts.app>
