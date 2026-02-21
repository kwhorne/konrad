<x-layouts.app title="Rediger eiendel">
    <div class="min-h-screen bg-zinc-100 dark:bg-zinc-950">
        <x-app-sidebar current="assets" />
        <x-app-header current="assets" />

        <flux:main class="bg-zinc-100 dark:bg-zinc-950">

            {{-- Header --}}
            <div class="mb-6 flex items-center gap-4">
                <flux:button href="{{ route('assets.index') }}" variant="ghost" icon="arrow-left" size="sm" />
                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $asset->title }}</h1>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 font-mono">{{ $asset->asset_number }}</p>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <flux:badge color="{{ $asset->status_badge_color }}">{{ $asset->status_label }}</flux:badge>
                    <flux:badge color="{{ $asset->condition_badge_color }}" variant="pill">{{ $asset->condition_label }}</flux:badge>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20 px-4 py-3">
                    <flux:icon.check-circle class="h-4 w-4 text-green-600 dark:text-green-400 shrink-0" />
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('assets.update', $asset) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    {{-- Left: main content (2/3) --}}
                    <div class="lg:col-span-2 space-y-5">

                        {{-- Grunnleggende informasjon --}}
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden">
                            <div class="flex items-center gap-3 px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center">
                                    <flux:icon.information-circle class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">Grunnleggende informasjon</p>
                            </div>
                            <div class="p-6 space-y-5">
                                <flux:field>
                                    <flux:label>Tittel *</flux:label>
                                    <flux:input name="title" value="{{ old('title', $asset->title) }}" required />
                                    @error('title') <flux:error>{{ $message }}</flux:error> @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label>Beskrivelse</flux:label>
                                    <flux:textarea name="description" rows="3">{{ old('description', $asset->description) }}</flux:textarea>
                                    @error('description') <flux:error>{{ $message }}</flux:error> @enderror
                                </flux:field>

                                <div class="grid grid-cols-2 gap-4">
                                    <flux:field>
                                        <flux:label>Serienummer</flux:label>
                                        <flux:input name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" />
                                        @error('serial_number') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Modell</flux:label>
                                        <flux:input name="asset_model" value="{{ old('asset_model', $asset->asset_model) }}" />
                                        @error('asset_model') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                </div>
                            </div>
                        </div>

                        {{-- Kjøpsinformasjon --}}
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden">
                            <div class="flex items-center gap-3 px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                                <div class="w-7 h-7 rounded-lg bg-green-50 dark:bg-green-950/50 flex items-center justify-center">
                                    <flux:icon.banknotes class="w-4 h-4 text-green-600 dark:text-green-400" />
                                </div>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">Kjøpsinformasjon</p>
                            </div>
                            <div class="p-6 space-y-5">
                                <div class="grid grid-cols-3 gap-4">
                                    <flux:field>
                                        <flux:label>Kjøpspris</flux:label>
                                        <flux:input name="purchase_price" type="number" step="0.01" value="{{ old('purchase_price', $asset->purchase_price) }}" />
                                        @error('purchase_price') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Valuta</flux:label>
                                        <flux:select name="currency">
                                            <option value="NOK" @selected(old('currency', $asset->currency) == 'NOK')>NOK</option>
                                            <option value="EUR" @selected(old('currency', $asset->currency) == 'EUR')>EUR</option>
                                            <option value="USD" @selected(old('currency', $asset->currency) == 'USD')>USD</option>
                                            <option value="SEK" @selected(old('currency', $asset->currency) == 'SEK')>SEK</option>
                                            <option value="DKK" @selected(old('currency', $asset->currency) == 'DKK')>DKK</option>
                                        </flux:select>
                                        @error('currency') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Kjøpsdato</flux:label>
                                        <flux:input name="purchase_date" type="date" value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}" />
                                        @error('purchase_date') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <flux:field>
                                        <flux:label>Leverandør</flux:label>
                                        <flux:input name="supplier" value="{{ old('supplier', $asset->supplier) }}" />
                                        @error('supplier') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Produsent</flux:label>
                                        <flux:input name="manufacturer" value="{{ old('manufacturer', $asset->manufacturer) }}" />
                                        @error('manufacturer') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Fakturanummer</flux:label>
                                        <flux:input name="invoice_number" value="{{ old('invoice_number', $asset->invoice_number) }}" />
                                        @error('invoice_number') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Fakturadato</flux:label>
                                        <flux:input name="invoice_date" type="date" value="{{ old('invoice_date', $asset->invoice_date?->format('Y-m-d')) }}" />
                                        @error('invoice_date') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                </div>
                            </div>
                        </div>

                        {{-- Lokasjon og organisering --}}
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden">
                            <div class="flex items-center gap-3 px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                                <div class="w-7 h-7 rounded-lg bg-cyan-50 dark:bg-cyan-950/50 flex items-center justify-center">
                                    <flux:icon.map-pin class="w-4 h-4 text-cyan-600 dark:text-cyan-400" />
                                </div>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">Lokasjon og organisering</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-3 gap-4">
                                    <flux:field>
                                        <flux:label>Lokasjon</flux:label>
                                        <flux:input name="location" value="{{ old('location', $asset->location) }}" />
                                        @error('location') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Avdeling</flux:label>
                                        <flux:input name="department" value="{{ old('department', $asset->department) }}" />
                                        @error('department') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Gruppe</flux:label>
                                        <flux:input name="group" value="{{ old('group', $asset->group) }}" />
                                        @error('group') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Forsikringsnummer</flux:label>
                                        <flux:input name="insurance_number" value="{{ old('insurance_number', $asset->insurance_number) }}" />
                                        @error('insurance_number') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                </div>
                            </div>
                        </div>

                        {{-- Ansvarlig og notater --}}
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden">
                            <div class="flex items-center gap-3 px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                                <div class="w-7 h-7 rounded-lg bg-violet-50 dark:bg-violet-950/50 flex items-center justify-center">
                                    <flux:icon.users class="w-4 h-4 text-violet-600 dark:text-violet-400" />
                                </div>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">Ansvarlig og vedlegg</p>
                            </div>
                            <div class="p-6 space-y-5">
                                <flux:field>
                                    <flux:label>Ansvarlig person</flux:label>
                                    <flux:select name="responsible_user_id">
                                        <option value="">Ingen ansvarlig</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @selected(old('responsible_user_id', $asset->responsible_user_id) == $user->id)>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </flux:select>
                                    @error('responsible_user_id') <flux:error>{{ $message }}</flux:error> @enderror
                                </flux:field>

                                @livewire('contract-file-upload')

                                <flux:field>
                                    <flux:label>Notater</flux:label>
                                    <flux:editor
                                        name="notes"
                                        toolbar="heading | bold italic underline | bullet ordered | link"
                                    >{{ old('notes', $asset->notes) }}</flux:editor>
                                    @error('notes') <flux:error>{{ $message }}</flux:error> @enderror
                                </flux:field>
                            </div>
                        </div>
                    </div>

                    {{-- Right: sidebar (1/3) --}}
                    <div class="space-y-5">

                        {{-- Status & tilstand --}}
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 p-5 sticky top-5 space-y-5">
                            <div>
                                <p class="text-xs font-semibold tracking-wide text-zinc-500 dark:text-zinc-400 uppercase mb-4">Status & tilstand</p>
                                <div class="space-y-4">
                                    <flux:field>
                                        <flux:label>Status</flux:label>
                                        <flux:select name="status" required>
                                            <option value="available"   @selected(old('status', $asset->status) == 'available')>Tilgjengelig</option>
                                            <option value="in_use"      @selected(old('status', $asset->status) == 'in_use')>I bruk</option>
                                            <option value="maintenance" @selected(old('status', $asset->status) == 'maintenance')>Vedlikehold</option>
                                            <option value="retired"     @selected(old('status', $asset->status) == 'retired')>Utfaset</option>
                                            <option value="lost"        @selected(old('status', $asset->status) == 'lost')>Tapt</option>
                                            <option value="sold"        @selected(old('status', $asset->status) == 'sold')>Solgt</option>
                                        </flux:select>
                                        @error('status') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>Tilstand</flux:label>
                                        <flux:select name="condition" required>
                                            <option value="excellent" @selected(old('condition', $asset->condition) == 'excellent')>Utmerket</option>
                                            <option value="good"      @selected(old('condition', $asset->condition) == 'good')>God</option>
                                            <option value="fair"      @selected(old('condition', $asset->condition) == 'fair')>Akseptabel</option>
                                            <option value="poor"      @selected(old('condition', $asset->condition) == 'poor')>Dårlig</option>
                                            <option value="broken"    @selected(old('condition', $asset->condition) == 'broken')>Ødelagt</option>
                                        </flux:select>
                                        @error('condition') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>

                                    <flux:checkbox name="is_active" value="1" :checked="old('is_active', $asset->is_active)">
                                        Aktiv eiendel
                                    </flux:checkbox>
                                </div>
                            </div>

                            <flux:separator variant="subtle" />

                            {{-- Garanti --}}
                            <div>
                                <p class="text-xs font-semibold tracking-wide text-zinc-500 dark:text-zinc-400 uppercase mb-4">Garanti</p>
                                @if($asset->warranty_until)
                                    @php
                                        $ws = $asset->warranty_status;
                                        $wsColor = match($ws) { 'active' => 'green', 'expiring_soon' => 'amber', default => 'red' };
                                        $wsLabel = match($ws) { 'active' => 'Aktiv', 'expiring_soon' => 'Utløper snart', default => 'Utløpt' };
                                    @endphp
                                    <flux:badge color="{{ $wsColor }}" class="mb-4">{{ $wsLabel }}</flux:badge>
                                @endif
                                <div class="space-y-4">
                                    <flux:field>
                                        <flux:label>Garanti fra</flux:label>
                                        <flux:input name="warranty_from" type="date" value="{{ old('warranty_from', $asset->warranty_from?->format('Y-m-d')) }}" />
                                        @error('warranty_from') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Garanti til</flux:label>
                                        <flux:input name="warranty_until" type="date" value="{{ old('warranty_until', $asset->warranty_until?->format('Y-m-d')) }}" />
                                        @error('warranty_until') <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                </div>
                            </div>

                            <flux:separator variant="subtle" />

                            {{-- Metadata --}}
                            <dl class="space-y-2 text-sm">
                                @if($asset->creator)
                                    <div class="flex justify-between">
                                        <dt class="text-zinc-500 dark:text-zinc-400">Opprettet av</dt>
                                        <dd class="text-zinc-700 dark:text-zinc-300">{{ $asset->creator->name }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Opprettet</dt>
                                    <dd class="text-zinc-700 dark:text-zinc-300">{{ $asset->created_at->format('d.m.Y') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Oppdatert</dt>
                                    <dd class="text-zinc-700 dark:text-zinc-300">{{ $asset->updated_at->format('d.m.Y') }}</dd>
                                </div>
                            </dl>

                            <flux:separator variant="subtle" />

                            {{-- Actions --}}
                            <div class="space-y-2">
                                <flux:button type="submit" variant="primary" class="w-full" icon="check">
                                    Lagre endringer
                                </flux:button>
                                <flux:button href="{{ route('assets.index') }}" variant="ghost" class="w-full">
                                    Avbryt
                                </flux:button>
                            </div>

                            <flux:separator variant="subtle" />

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('assets.destroy', $asset) }}"
                                  onsubmit="return confirm('Er du sikker på at du vil slette denne eiendelen?')">
                                @csrf
                                @method('DELETE')
                                <flux:button type="submit" variant="ghost" class="w-full text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30" icon="trash">
                                    Slett eiendel
                                </flux:button>
                            </form>
                        </div>
                    </div>

                </div>
            </form>

        </flux:main>
    </div>
</x-layouts.app>
