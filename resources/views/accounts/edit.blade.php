<x-layouts.app title="Rediger konto">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="accounting" />
        <x-app-header current="accounting" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="max-w-2xl mx-auto">
                <div class="flex items-center gap-4 mb-8">
                    <flux:button href="{{ route('accounts.index') }}" variant="ghost" size="sm">
                        <flux:icon.arrow-left class="w-5 h-5" />
                    </flux:button>
                    <div>
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            Rediger konto
                        </flux:heading>
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                            {{ $account->account_number }} - {{ $account->name }}
                        </flux:text>
                    </div>
                </div>

                @if($account->is_system)
                    <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                        <flux:text class="text-amber-800 dark:text-amber-200">
                            <strong>Systemkonto:</strong> Kontonummer, klasse og type kan ikke endres for systemkontoer.
                        </flux:text>
                    </div>
                @endif

                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <form action="{{ route('accounts.update', $account) }}" method="POST" class="p-6 space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label for="account_number">Kontonummer *</flux:label>
                                <flux:input id="account_number" name="account_number" type="text" value="{{ old('account_number', $account->account_number) }}" required :disabled="$account->is_system" />
                                @error('account_number')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label for="name">Kontonavn *</flux:label>
                                <flux:input id="name" name="name" type="text" value="{{ old('name', $account->name) }}" required />
                                @error('name')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label for="account_class">Kontoklasse *</flux:label>
                                <flux:select id="account_class" name="account_class" required :disabled="$account->is_system">
                                    <option value="1" {{ old('account_class', $account->account_class) === '1' ? 'selected' : '' }}>1 - Eiendeler</option>
                                    <option value="2" {{ old('account_class', $account->account_class) === '2' ? 'selected' : '' }}>2 - Egenkapital og gjeld</option>
                                    <option value="3" {{ old('account_class', $account->account_class) === '3' ? 'selected' : '' }}>3 - Salgsinntekter</option>
                                    <option value="4" {{ old('account_class', $account->account_class) === '4' ? 'selected' : '' }}>4 - Varekostnad</option>
                                    <option value="5" {{ old('account_class', $account->account_class) === '5' ? 'selected' : '' }}>5 - Lønn og personal</option>
                                    <option value="6" {{ old('account_class', $account->account_class) === '6' ? 'selected' : '' }}>6 - Avskrivninger</option>
                                    <option value="7" {{ old('account_class', $account->account_class) === '7' ? 'selected' : '' }}>7 - Andre driftskostnader</option>
                                    <option value="8" {{ old('account_class', $account->account_class) === '8' ? 'selected' : '' }}>8 - Finansposter</option>
                                </flux:select>
                                @error('account_class')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label for="account_type">Kontotype *</flux:label>
                                <flux:select id="account_type" name="account_type" required :disabled="$account->is_system">
                                    <option value="asset" {{ old('account_type', $account->account_type) === 'asset' ? 'selected' : '' }}>Eiendel</option>
                                    <option value="liability" {{ old('account_type', $account->account_type) === 'liability' ? 'selected' : '' }}>Gjeld</option>
                                    <option value="equity" {{ old('account_type', $account->account_type) === 'equity' ? 'selected' : '' }}>Egenkapital</option>
                                    <option value="revenue" {{ old('account_type', $account->account_type) === 'revenue' ? 'selected' : '' }}>Inntekt</option>
                                    <option value="expense" {{ old('account_type', $account->account_type) === 'expense' ? 'selected' : '' }}>Kostnad</option>
                                </flux:select>
                                @error('account_type')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label for="parent_id">Overkonto</flux:label>
                            <flux:select id="parent_id" name="parent_id">
                                <option value="">Ingen (toppnivå)</option>
                                @foreach($parentAccounts as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id', $account->parent_id) == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->account_number }} - {{ $parent->name }}
                                    </option>
                                @endforeach
                            </flux:select>
                            <flux:description>Valgfritt. Velg en overkonto for hierarkisk struktur.</flux:description>
                            @error('parent_id')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label for="vat_code">MVA-kode</flux:label>
                            <flux:input id="vat_code" name="vat_code" type="text" value="{{ old('vat_code', $account->vat_code) }}" placeholder="f.eks. 1, 3" />
                            <flux:description>Standard MVA-kode for denne kontoen</flux:description>
                            @error('vat_code')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label for="description">Beskrivelse</flux:label>
                            <flux:textarea id="description" name="description" rows="3">{{ old('description', $account->description) }}</flux:textarea>
                            @error('description')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <flux:field>
                            <flux:checkbox id="is_active" name="is_active" value="1" label="Aktiv" :checked="old('is_active', $account->is_active)" />
                        </flux:field>

                        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button href="{{ route('accounts.index') }}" variant="ghost">
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
