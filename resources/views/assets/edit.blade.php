<x-layouts.app title="Rediger eiendel">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="assets" />
        <x-app-header current="assets" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                        <flux:icon.cube class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div>
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            Rediger eiendel
                        </flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $asset->asset_number }} - {{ $asset->title }}
                        </flux:text>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('assets.update', $asset) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-8">
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-50 to-cyan-50 dark:from-indigo-950/20 dark:to-cyan-950/20 px-8 py-6 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white dark:bg-zinc-800 rounded-lg flex items-center justify-center shadow-sm">
                                        <flux:icon.information-circle class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                            Grunnleggende informasjon
                                        </flux:heading>
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Tittel, beskrivelse og identifikasjon
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8">
                                <div class="space-y-6">
                                    <flux:field>
                                        <flux:label for="title">Tittel</flux:label>
                                        <flux:input id="title" name="title" type="text" value="{{ old('title', $asset->title) }}" required />
                                        @error('title')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label for="description">Beskrivelse</flux:label>
                                        <flux:textarea id="description" name="description" rows="3">{{ old('description', $asset->description) }}</flux:textarea>
                                        @error('description')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <flux:field>
                                            <flux:label for="serial_number">Serienummer</flux:label>
                                            <flux:input id="serial_number" name="serial_number" type="text" value="{{ old('serial_number', $asset->serial_number) }}" />
                                            @error('serial_number')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <flux:field>
                                            <flux:label for="asset_model">Eiendelsmodell</flux:label>
                                            <flux:input id="asset_model" name="asset_model" type="text" value="{{ old('asset_model', $asset->asset_model) }}" />
                                            @error('asset_model')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>
                                    </div>
                                </div>
                            </div>
                        </flux:card>

                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-50 to-cyan-50 dark:from-indigo-950/20 dark:to-cyan-950/20 px-8 py-6 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white dark:bg-zinc-800 rounded-lg flex items-center justify-center shadow-sm">
                                        <flux:icon.banknotes class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                            Kjøpsinformasjon
                                        </flux:heading>
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Pris, leverandør og faktura
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <flux:field>
                                        <flux:label for="purchase_price">Kjøpspris</flux:label>
                                        <flux:input id="purchase_price" name="purchase_price" type="number" step="0.01" value="{{ old('purchase_price', $asset->purchase_price) }}" />
                                        @error('purchase_price')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label for="currency">Valuta</flux:label>
                                        <flux:select id="currency" name="currency">
                                            <option value="NOK" @selected(old('currency', $asset->currency) == 'NOK')>NOK</option>
                                            <option value="EUR" @selected(old('currency', $asset->currency) == 'EUR')>EUR</option>
                                            <option value="USD" @selected(old('currency', $asset->currency) == 'USD')>USD</option>
                                            <option value="SEK" @selected(old('currency', $asset->currency) == 'SEK')>SEK</option>
                                            <option value="DKK" @selected(old('currency', $asset->currency) == 'DKK')>DKK</option>
                                        </flux:select>
                                        @error('currency')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label for="purchase_date">Kjøpsdato</flux:label>
                                        <flux:input id="purchase_date" name="purchase_date" type="date" value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}" />
                                        @error('purchase_date')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                    <flux:field>
                                        <flux:label for="supplier">Leverandør</flux:label>
                                        <flux:input id="supplier" name="supplier" type="text" value="{{ old('supplier', $asset->supplier) }}" />
                                        @error('supplier')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label for="manufacturer">Produsent</flux:label>
                                        <flux:input id="manufacturer" name="manufacturer" type="text" value="{{ old('manufacturer', $asset->manufacturer) }}" />
                                        @error('manufacturer')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label for="invoice_number">Fakturanummer</flux:label>
                                        <flux:input id="invoice_number" name="invoice_number" type="text" value="{{ old('invoice_number', $asset->invoice_number) }}" />
                                        @error('invoice_number')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label for="invoice_date">Fakturadato</flux:label>
                                        <flux:input id="invoice_date" name="invoice_date" type="date" value="{{ old('invoice_date', $asset->invoice_date?->format('Y-m-d')) }}" />
                                        @error('invoice_date')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>
                                </div>
                            </div>
                        </flux:card>

                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-50 to-cyan-50 dark:from-indigo-950/20 dark:to-cyan-950/20 px-8 py-6 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white dark:bg-zinc-800 rounded-lg flex items-center justify-center shadow-sm">
                                        <flux:icon.map-pin class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                            Lokasjon og organisering
                                        </flux:heading>
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Plassering og tilhørighet
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <flux:field>
                                        <flux:label for="location">Lokasjon</flux:label>
                                        <flux:input id="location" name="location" type="text" value="{{ old('location', $asset->location) }}" />
                                        @error('location')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label for="department">Avdeling</flux:label>
                                        <flux:input id="department" name="department" type="text" value="{{ old('department', $asset->department) }}" />
                                        @error('department')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label for="group">Gruppe</flux:label>
                                        <flux:input id="group" name="group" type="text" value="{{ old('group', $asset->group) }}" />
                                        @error('group')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>
                                </div>

                                <flux:field class="mt-6">
                                    <flux:label for="insurance_number">Forsikringsnummer</flux:label>
                                    <flux:input id="insurance_number" name="insurance_number" type="text" value="{{ old('insurance_number', $asset->insurance_number) }}" />
                                    @error('insurance_number')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>
                        </flux:card>

                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-50 to-cyan-50 dark:from-indigo-950/20 dark:to-cyan-950/20 px-8 py-6 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white dark:bg-zinc-800 rounded-lg flex items-center justify-center shadow-sm">
                                        <flux:icon.users class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                            Ansvarlig og vedlegg
                                        </flux:heading>
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Tilordning og dokumenter
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8">
                                <div class="space-y-6">
                                    <flux:field>
                                        <flux:label for="responsible_user_id">Ansvarlig person</flux:label>
                                        <flux:select id="responsible_user_id" name="responsible_user_id">
                                            <option value="">Velg ansvarlig</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" @selected(old('responsible_user_id', $asset->responsible_user_id) == $user->id)>{{ $user->name }}</option>
                                            @endforeach
                                        </flux:select>
                                        @error('responsible_user_id')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    @livewire('contract-file-upload')

                                    <flux:field>
                                        <flux:label for="notes">Notater</flux:label>
                                        <flux:editor
                                            name="notes"
                                            toolbar="heading | bold italic underline | bullet ordered | link"
                                        >{{ old('notes', $asset->notes) }}</flux:editor>
                                        @error('notes')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>
                                </div>
                            </div>
                        </flux:card>
                    </div>

                    <div class="lg:col-span-1 space-y-6">
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 sticky top-6">
                            <div class="p-6 space-y-6">
                                <div>
                                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-4">
                                        Status & Tilstand
                                    </flux:heading>

                                    <div class="space-y-4">
                                        <flux:field>
                                            <flux:label for="status">Status</flux:label>
                                            <flux:select id="status" name="status" required>
                                                <option value="available" @selected(old('status', $asset->status) == 'available')>Tilgjengelig</option>
                                                <option value="in_use" @selected(old('status', $asset->status) == 'in_use')>I bruk</option>
                                                <option value="maintenance" @selected(old('status', $asset->status) == 'maintenance')>Vedlikehold</option>
                                                <option value="retired" @selected(old('status', $asset->status) == 'retired')>Utfaset</option>
                                                <option value="lost" @selected(old('status', $asset->status) == 'lost')>Tapt</option>
                                                <option value="sold" @selected(old('status', $asset->status) == 'sold')>Solgt</option>
                                            </flux:select>
                                            @error('status')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <flux:field>
                                            <flux:label for="condition">Tilstand</flux:label>
                                            <flux:select id="condition" name="condition" required>
                                                <option value="good" @selected(old('condition', $asset->condition) == 'good')>God</option>
                                                <option value="excellent" @selected(old('condition', $asset->condition) == 'excellent')>Utmerket</option>
                                                <option value="fair" @selected(old('condition', $asset->condition) == 'fair')>Akseptabel</option>
                                                <option value="poor" @selected(old('condition', $asset->condition) == 'poor')>Dårlig</option>
                                                <option value="broken" @selected(old('condition', $asset->condition) == 'broken')>Ødelagt</option>
                                            </flux:select>
                                            @error('condition')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <flux:checkbox id="is_active" name="is_active" value="1" :checked="old('is_active', $asset->is_active)">
                                            Aktiv eiendel
                                        </flux:checkbox>
                                    </div>
                                </div>

                                <flux:separator variant="subtle" />

                                <div>
                                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-4">
                                        Garanti
                                    </flux:heading>

                                    <div class="space-y-4">
                                        <flux:field>
                                            <flux:label for="warranty_from">Garanti fra</flux:label>
                                            <flux:input id="warranty_from" name="warranty_from" type="date" value="{{ old('warranty_from', $asset->warranty_from?->format('Y-m-d')) }}" />
                                            @error('warranty_from')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <flux:field>
                                            <flux:label for="warranty_until">Garanti til</flux:label>
                                            <flux:input id="warranty_until" name="warranty_until" type="date" value="{{ old('warranty_until', $asset->warranty_until?->format('Y-m-d')) }}" />
                                            @error('warranty_until')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>
                                    </div>
                                </div>
                            </div>
                        </flux:card>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button href="{{ route('assets.show', $asset) }}" variant="ghost" class="px-6 py-3">
                        <flux:icon.arrow-left class="w-5 h-5 mr-2" />
                        Avbryt
                    </flux:button>
                    <flux:button type="submit" variant="primary" class="px-8 py-3">
                        <flux:icon.check class="w-5 h-5 mr-2" />
                        Oppdater eiendel
                    </flux:button>
                </div>
            </form>
        </flux:main>

    </div>
</x-layouts.app>
