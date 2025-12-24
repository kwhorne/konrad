<x-layouts.app title="Opprett kontrakt">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="contracts" />
        <x-app-header current="contracts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                        <flux:icon.document-text class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div>
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            Opprett ny kontrakt
                        </flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                            Fyll ut informasjonen om kontrakten
                        </flux:text>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('contracts.store') }}" enctype="multipart/form-data">
                @csrf
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column (2/3) - Main Content -->
                    <div class="lg:col-span-2 space-y-8">
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-50 to-cyan-50 dark:from-indigo-950/20 dark:to-cyan-950/20 px-8 py-6 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white dark:bg-zinc-800 rounded-lg flex items-center justify-center shadow-sm">
                                        <flux:icon.file-text class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                            Grunnleggende informasjon
                                        </flux:heading>
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Tittel og beskrivelse
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8">
                                <div class="space-y-6">
                                    <flux:field>
                                        <flux:label for="title">Tittel</flux:label>
                                        <flux:input id="title" name="title" type="text" value="{{ old('title') }}" required />
                                        @error('title')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label for="description">Beskrivelse</flux:label>
                                        <flux:textarea id="description" name="description" rows="4">{{ old('description') }}</flux:textarea>
                                        @error('description')
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
                                    <flux:icon.building class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div>
                                    <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                        Bedriftsinformasjon
                                    </flux:heading>
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Kontaktdetaljer og organisering
                                    </flux:text>
                                </div>
                            </div>
                        </div>
                        <div class="p-8">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <flux:field>
                                    <flux:label for="company_name">Bedriftsnavn</flux:label>
                                    <flux:input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" required />
                                    @error('company_name')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="company_contact">Kontaktperson</flux:label>
                                    <flux:input id="company_contact" name="company_contact" type="text" value="{{ old('company_contact') }}" />
                                    @error('company_contact')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="company_email">E-post</flux:label>
                                    <flux:input id="company_email" name="company_email" type="email" value="{{ old('company_email') }}" />
                                    @error('company_email')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="company_phone">Telefon</flux:label>
                                    <flux:input id="company_phone" name="company_phone" type="tel" value="{{ old('company_phone') }}" />
                                    @error('company_phone')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="department">Avdeling</flux:label>
                                    <flux:input id="department" name="department" type="text" value="{{ old('department') }}" />
                                    @error('department')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="group">Gruppe</flux:label>
                                    <flux:input id="group" name="group" type="text" value="{{ old('group') }}" />
                                    @error('group')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="asset_reference">Eiendel-referanse</flux:label>
                                    <flux:input id="asset_reference" name="asset_reference" type="text" value="{{ old('asset_reference') }}" />
                                    @error('asset_reference')
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
                                    <flux:icon.banknote class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div>
                                    <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                        Økonomi
                                    </flux:heading>
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Verdi og betalingsinformasjon
                                    </flux:text>
                                </div>
                            </div>
                        </div>
                        <div class="p-8">

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <flux:field>
                                    <flux:label for="value">Verdi</flux:label>
                                    <flux:input id="value" name="value" type="number" step="0.01" value="{{ old('value') }}" />
                                    @error('value')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="currency">Valuta</flux:label>
                                    <flux:select id="currency" name="currency">
                                        <option value="NOK" selected>NOK</option>
                                        <option value="EUR">EUR</option>
                                        <option value="USD">USD</option>
                                        <option value="SEK">SEK</option>
                                        <option value="DKK">DKK</option>
                                    </flux:select>
                                    @error('currency')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="payment_frequency">Betalingsfrekvens</flux:label>
                                    <flux:select id="payment_frequency" name="payment_frequency">
                                        <option value="">Velg frekvens</option>
                                        <option value="monthly">Månedlig</option>
                                        <option value="quarterly">Kvartalsvis</option>
                                        <option value="yearly">Årlig</option>
                                        <option value="one_time">Engangsbeløp</option>
                                    </flux:select>
                                    @error('payment_frequency')
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
                                    <flux:icon.users class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div>
                                    <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                        Ansvarlig og notater
                                    </flux:heading>
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Tilordning og tilleggsinformasjon
                                    </flux:text>
                                </div>
                            </div>
                        </div>
                        <div class="p-8">

                            <div class="grid grid-cols-1 gap-6">
                                <flux:field>
                                    <flux:label for="responsible_user_id">Ansvarlig person</flux:label>
                                    <flux:select id="responsible_user_id" name="responsible_user_id">
                                        <option value="">Velg ansvarlig</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
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
                                    >{{ old('notes') }}</flux:editor>
                                    @error('notes')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>
                        </div>
                    </flux:card>

                    </div>

                    <!-- Right Column (1/3) - Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Type & Status -->
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 sticky top-6">
                            <div class="p-6 space-y-6">
                                <div>
                                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-4">
                                        Type & Status
                                    </flux:heading>
                                    
                                    <div class="space-y-4">
                                        <flux:field>
                                            <flux:label for="type">Type</flux:label>
                                            <flux:select id="type" name="type" required>
                                                <option value="">Velg type</option>
                                                <option value="service">Tjeneste</option>
                                                <option value="lease">Leie</option>
                                                <option value="maintenance">Vedlikehold</option>
                                                <option value="software">Programvare</option>
                                                <option value="insurance">Forsikring</option>
                                                <option value="employment">Ansettelse</option>
                                                <option value="supplier">Leverandør</option>
                                                <option value="other">Annet</option>
                                            </flux:select>
                                            @error('type')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <flux:field>
                                            <flux:label for="status">Status</flux:label>
                                            <flux:select id="status" name="status" required>
                                                <option value="draft" selected>Utkast</option>
                                                <option value="active">Aktiv</option>
                                                <option value="expiring_soon">Utgår snart</option>
                                                <option value="expired">Utgått</option>
                                                <option value="terminated">Avsluttet</option>
                                                <option value="renewed">Fornyet</option>
                                            </flux:select>
                                            @error('status')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>
                                    </div>
                                </div>

                                <flux:separator variant="subtle" />

                                <div>
                                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-4">
                                        Datoer
                                    </flux:heading>
                                    
                                    <div class="space-y-4">
                                        <flux:field>
                                            <flux:label for="established_date">Etablert</flux:label>
                                            <flux:input id="established_date" name="established_date" type="date" value="{{ old('established_date') }}" required />
                                            @error('established_date')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <flux:field>
                                            <flux:label for="start_date">Start</flux:label>
                                            <flux:input id="start_date" name="start_date" type="date" value="{{ old('start_date') }}" required />
                                            @error('start_date')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <flux:field>
                                            <flux:label for="end_date">Slutt</flux:label>
                                            <flux:input id="end_date" name="end_date" type="date" value="{{ old('end_date') }}" required />
                                            @error('end_date')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <flux:field>
                                            <flux:label for="notice_period_days">Oppsigelse (dager)</flux:label>
                                            <flux:input id="notice_period_days" name="notice_period_days" type="number" value="{{ old('notice_period_days', 90) }}" required />
                                            @error('notice_period_days')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <flux:checkbox id="auto_renewal" name="auto_renewal" value="1">
                                            Automatisk fornyelse
                                        </flux:checkbox>

                                        <flux:field>
                                            <flux:label for="renewal_period_months">Fornyelse (mnd)</flux:label>
                                            <flux:input id="renewal_period_months" name="renewal_period_months" type="number" value="{{ old('renewal_period_months') }}" />
                                            @error('renewal_period_months')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>
                                    </div>
                                </div>
                            </div>
                        </flux:card>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button href="{{ route('contracts.index') }}" variant="ghost" class="px-6 py-3">
                        <flux:icon.arrow-left class="w-5 h-5 mr-2" />
                        Avbryt
                    </flux:button>
                    <flux:button type="submit" variant="primary" class="px-8 py-3">
                        <flux:icon.check class="w-5 h-5 mr-2" />
                        Opprett kontrakt
                    </flux:button>
                </div>
            </form>
        </flux:main>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</x-layouts.app>
