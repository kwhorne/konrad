<x-layouts.app title="Opprett kontakt">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="contacts" />
        <x-app-header current="contacts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <flux:icon.users class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            Opprett ny kontakt
                        </flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                            Fyll ut informasjonen om kontakten
                        </flux:text>
                    </div>
                </div>
            </div>

            {{-- Brønnøysund Search --}}
            <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 mb-8">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <flux:icon.magnifying-glass class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                Hent fra Brønnøysundregistrene
                            </flux:heading>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                Søk etter bedriftsnavn eller organisasjonsnummer for å fylle ut automatisk
                            </flux:text>
                        </div>
                    </div>
                    @livewire('brreg-search')
                </div>
            </flux:card>

            <form method="POST" action="{{ route('contacts.store') }}" enctype="multipart/form-data" id="contact-form">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-8">
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-8 py-6 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white dark:bg-zinc-800 rounded-lg flex items-center justify-center shadow-sm">
                                        <flux:icon.building-office class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                            Bedriftsinformasjon
                                        </flux:heading>
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Grunnleggende informasjon om bedriften
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8">
                                <div class="space-y-6">
                                    <flux:field>
                                        <flux:label for="company_name">Bedriftsnavn</flux:label>
                                        <flux:input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" required />
                                        @error('company_name')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <flux:field>
                                            <flux:label for="organization_number">Organisasjonsnummer</flux:label>
                                            <flux:input id="organization_number" name="organization_number" type="text" value="{{ old('organization_number') }}" />
                                            @error('organization_number')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <flux:field>
                                            <flux:label for="industry">Bransje</flux:label>
                                            <flux:select id="industry" name="industry">
                                                <option value="">Velg bransje</option>
                                                <option value="Advokater" @selected(old('industry') == 'Advokater')>Advokater</option>
                                                <option value="Bank/Finans" @selected(old('industry') == 'Bank/Finans')>Bank/Finans</option>
                                                <option value="Forsikring" @selected(old('industry') == 'Forsikring')>Forsikring</option>
                                                <option value="IT/EDB" @selected(old('industry') == 'IT/EDB')>IT/EDB</option>
                                                <option value="Grafisk" @selected(old('industry') == 'Grafisk')>Grafisk</option>
                                                <option value="Handel" @selected(old('industry') == 'Handel')>Handel</option>
                                                <option value="Hotel/Restaurant" @selected(old('industry') == 'Hotel/Restaurant')>Hotel/Restaurant</option>
                                                <option value="Industri" @selected(old('industry') == 'Industri')>Industri</option>
                                                <option value="Grossister" @selected(old('industry') == 'Grossister')>Grossister</option>
                                                <option value="Offentlig" @selected(old('industry') == 'Offentlig')>Offentlig</option>
                                                <option value="Service" @selected(old('industry') == 'Service')>Service</option>
                                                <option value="Transport" @selected(old('industry') == 'Transport')>Transport</option>
                                                <option value="Eiendom" @selected(old('industry') == 'Eiendom')>Eiendom</option>
                                                <option value="Klubb" @selected(old('industry') == 'Klubb')>Klubb</option>
                                                <option value="Annen" @selected(old('industry') == 'Annen')>Annen</option>
                                            </flux:select>
                                            @error('industry')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>
                                    </div>

                                    <flux:field>
                                        <flux:label for="website">Nettside</flux:label>
                                        <flux:input id="website" name="website" type="url" value="{{ old('website') }}" placeholder="https://" />
                                        @error('website')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label for="description">Beskrivelse</flux:label>
                                        <flux:textarea id="description" name="description" rows="3">{{ old('description') }}</flux:textarea>
                                        @error('description')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>
                                </div>
                            </div>
                        </flux:card>

                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-8 py-6 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white dark:bg-zinc-800 rounded-lg flex items-center justify-center shadow-sm">
                                        <flux:icon.users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                            Kontaktpersoner
                                        </flux:heading>
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Legg til personer tilknyttet denne kontakten
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8">
                                @livewire('contact-person-manager')
                            </div>
                        </flux:card>

                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-8 py-6 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white dark:bg-zinc-800 rounded-lg flex items-center justify-center shadow-sm">
                                        <flux:icon.map-pin class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                            Adresseinformasjon
                                        </flux:heading>
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Besøksadresse og fakturaadresse
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8 space-y-8">
                                <div>
                                    <flux:heading size="md" level="3" class="text-zinc-900 dark:text-white mb-4">
                                        Besøksadresse
                                    </flux:heading>
                                    <div class="space-y-4">
                                        <flux:field>
                                            <flux:label for="address">Adresse</flux:label>
                                            <flux:input id="address" name="address" type="text" value="{{ old('address') }}" />
                                            @error('address')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <flux:field>
                                                <flux:label for="postal_code">Postnummer</flux:label>
                                                <flux:input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code') }}" />
                                                @error('postal_code')
                                                    <flux:error>{{ $message }}</flux:error>
                                                @enderror
                                            </flux:field>

                                            <flux:field>
                                                <flux:label for="city">Poststed</flux:label>
                                                <flux:input id="city" name="city" type="text" value="{{ old('city') }}" />
                                                @error('city')
                                                    <flux:error>{{ $message }}</flux:error>
                                                @enderror
                                            </flux:field>

                                            <flux:field>
                                                <flux:label for="country">Land</flux:label>
                                                <flux:input id="country" name="country" type="text" value="{{ old('country', 'Norge') }}" />
                                                @error('country')
                                                    <flux:error>{{ $message }}</flux:error>
                                                @enderror
                                            </flux:field>
                                        </div>
                                    </div>
                                </div>

                                <flux:separator variant="subtle" />

                                <div>
                                    <flux:heading size="md" level="3" class="text-zinc-900 dark:text-white mb-4">
                                        Fakturaadresse (hvis forskjellig)
                                    </flux:heading>
                                    <div class="space-y-4">
                                        <flux:field>
                                            <flux:label for="billing_address">Adresse</flux:label>
                                            <flux:input id="billing_address" name="billing_address" type="text" value="{{ old('billing_address') }}" />
                                            @error('billing_address')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <flux:field>
                                                <flux:label for="billing_postal_code">Postnummer</flux:label>
                                                <flux:input id="billing_postal_code" name="billing_postal_code" type="text" value="{{ old('billing_postal_code') }}" />
                                                @error('billing_postal_code')
                                                    <flux:error>{{ $message }}</flux:error>
                                                @enderror
                                            </flux:field>

                                            <flux:field>
                                                <flux:label for="billing_city">Poststed</flux:label>
                                                <flux:input id="billing_city" name="billing_city" type="text" value="{{ old('billing_city') }}" />
                                                @error('billing_city')
                                                    <flux:error>{{ $message }}</flux:error>
                                                @enderror
                                            </flux:field>

                                            <flux:field>
                                                <flux:label for="billing_country">Land</flux:label>
                                                <flux:input id="billing_country" name="billing_country" type="text" value="{{ old('billing_country') }}" />
                                                @error('billing_country')
                                                    <flux:error>{{ $message }}</flux:error>
                                                @enderror
                                            </flux:field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </flux:card>

                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-8 py-6 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white dark:bg-zinc-800 rounded-lg flex items-center justify-center shadow-sm">
                                        <flux:icon.banknote class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                            Forretningsdetaljer
                                        </flux:heading>
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Betalingsbetingelser og økonomi
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <flux:field>
                                        <flux:label for="customer_category">Kundekategori</flux:label>
                                        <flux:select id="customer_category" name="customer_category">
                                            <option value="">Velg kategori</option>
                                            <option value="a">A-kunde</option>
                                            <option value="b">B-kunde</option>
                                            <option value="c">C-kunde</option>
                                        </flux:select>
                                        @error('customer_category')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label for="credit_limit">Kredittgrense (NOK)</flux:label>
                                        <flux:input id="credit_limit" name="credit_limit" type="number" step="0.01" value="{{ old('credit_limit') }}" />
                                        @error('credit_limit')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label for="payment_terms_days">Betalingsbetingelser (dager)</flux:label>
                                        <flux:input id="payment_terms_days" name="payment_terms_days" type="number" value="{{ old('payment_terms_days', 30) }}" />
                                        @error('payment_terms_days')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field>
                                        <flux:label for="payment_method">Betalingsmåte</flux:label>
                                        <flux:input id="payment_method" name="payment_method" type="text" value="{{ old('payment_method') }}" />
                                        @error('payment_method')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>

                                    <flux:field class="md:col-span-2">
                                        <flux:label for="bank_account">Bankkontonummer</flux:label>
                                        <flux:input id="bank_account" name="bank_account" type="text" value="{{ old('bank_account') }}" />
                                        @error('bank_account')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>
                                </div>
                            </div>
                        </flux:card>

                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-8 py-6 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white dark:bg-zinc-800 rounded-lg flex items-center justify-center shadow-sm">
                                        <flux:icon.document-text class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                            Notater og vedlegg
                                        </flux:heading>
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Intern informasjon
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8 space-y-6">
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
                        </flux:card>
                    </div>

                    <div class="lg:col-span-1 space-y-6">
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
                                                <option value="customer" selected>Kunde</option>
                                                <option value="supplier">Leverandør</option>
                                                <option value="partner">Partner</option>
                                                <option value="prospect">Prospekt</option>
                                                <option value="competitor">Konkurrent</option>
                                                <option value="other">Annet</option>
                                            </flux:select>
                                            @error('type')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <flux:field>
                                            <flux:label for="status">Status</flux:label>
                                            <flux:select id="status" name="status" required>
                                                <option value="active" selected>Aktiv</option>
                                                <option value="inactive">Inaktiv</option>
                                                <option value="prospect">Prospekt</option>
                                                <option value="archived">Arkivert</option>
                                            </flux:select>
                                            @error('status')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <flux:checkbox id="is_active" name="is_active" value="1" checked>
                                            Aktiv kontakt
                                        </flux:checkbox>
                                    </div>
                                </div>

                                <flux:separator variant="subtle" />

                                <div>
                                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-4">
                                        Datoer
                                    </flux:heading>
                                    
                                    <div class="space-y-4">
                                        <flux:field>
                                            <flux:label for="customer_since">Kunde siden</flux:label>
                                            <flux:input id="customer_since" name="customer_since" type="date" value="{{ old('customer_since') }}" />
                                            @error('customer_since')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>

                                        <flux:field>
                                            <flux:label for="last_contact_date">Siste kontakt</flux:label>
                                            <flux:input id="last_contact_date" name="last_contact_date" type="date" value="{{ old('last_contact_date') }}" />
                                            @error('last_contact_date')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>
                                    </div>
                                </div>

                                <flux:separator variant="subtle" />

                                <div>
                                    <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-4">
                                        Ansvarlig
                                    </flux:heading>
                                    
                                    <flux:field>
                                        <flux:label for="account_manager_id">Account manager</flux:label>
                                        <flux:select id="account_manager_id" name="account_manager_id">
                                            <option value="">Velg ansvarlig</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </flux:select>
                                        @error('account_manager_id')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>
                                </div>
                            </div>
                        </flux:card>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button href="{{ route('contacts.index') }}" variant="ghost" class="px-6 py-3">
                        <flux:icon.arrow-left class="w-5 h-5 mr-2" />
                        Avbryt
                    </flux:button>
                    <flux:button type="submit" variant="primary" class="px-8 py-3">
                        <flux:icon.check class="w-5 h-5 mr-2" />
                        Opprett kontakt
                    </flux:button>
                </div>
            </form>
        </flux:main>


    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('company-selected', (event) => {
                const company = event.company;

                // Fill in company info
                const setInputValue = (id, value) => {
                    const input = document.getElementById(id);
                    if (input && value) {
                        input.value = value;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                };

                setInputValue('company_name', company.navn);
                setInputValue('organization_number', company.organisasjonsnummer);
                setInputValue('website', company.hjemmeside);

                // Fill in address
                setInputValue('address', company.adresse);
                setInputValue('postal_code', company.postnummer);
                setInputValue('city', company.poststed);
                setInputValue('country', company.land || 'Norge');

                // Fill in billing address if postadresse exists
                if (company.postadresse) {
                    setInputValue('billing_address', company.postadresse);
                    setInputValue('billing_postal_code', company.postadresse_postnummer);
                    setInputValue('billing_city', company.postadresse_poststed);
                    setInputValue('billing_country', company.postadresse_land || 'Norge');
                }

                // Scroll to form
                document.getElementById('contact-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>
</x-layouts.app>
