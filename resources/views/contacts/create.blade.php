<x-layouts.app title="Opprett kontakt">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="contacts" />
        <x-app-header current="contacts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            {{-- CRM Header --}}
            <div class="bg-white dark:bg-zinc-900 -mx-6 -mt-6 px-6 pt-6 pb-6 mb-8 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex items-start justify-between mb-4">
                    <flux:button href="{{ route('contacts.index') }}" variant="ghost" size="sm">
                        <flux:icon.arrow-left class="w-4 h-4 mr-1" />
                        Kontakter
                    </flux:button>
                </div>

                <div class="flex items-start gap-5">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                        <flux:icon.building-office-2 class="w-8 h-8 text-white" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            Opprett ny kontakt
                        </flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                            Fyll ut informasjon om den nye kontakten
                        </flux:text>
                    </div>
                </div>
            </div>

            {{-- Brønnøysund Search --}}
            <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 mb-6">
                <div class="p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <flux:icon.magnifying-glass class="h-5 w-5 text-green-600 dark:text-green-400" />
                        <div>
                            <flux:heading size="base" level="2" class="text-zinc-900 dark:text-white">
                                Hent fra Brønnøysundregistrene
                            </flux:heading>
                            <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">
                                Søk etter bedriftsnavn eller organisasjonsnummer
                            </flux:text>
                        </div>
                    </div>
                    @livewire('brreg-search')
                </div>
            </flux:card>

            <form method="POST" action="{{ route('contacts.store') }}" enctype="multipart/form-data" id="contact-form">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        {{-- Company Info with Tabs --}}
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="p-6">
                                <flux:tab.group>
                                    <flux:tabs variant="segmented">
                                        <flux:tab name="company" icon="building-office">Bedrift</flux:tab>
                                        <flux:tab name="visit" icon="map-pin">Besøksadresse</flux:tab>
                                        <flux:tab name="billing" icon="document-text">Faktura</flux:tab>
                                        <flux:tab name="business" icon="banknotes">Forretning</flux:tab>
                                    </flux:tabs>

                                    <flux:tab.panel name="company" class="pt-6">
                                        <div class="space-y-4">
                                            <flux:field>
                                                <flux:label for="company_name">Bedriftsnavn</flux:label>
                                                <flux:input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" required />
                                                @error('company_name')<flux:error>{{ $message }}</flux:error>@enderror
                                            </flux:field>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <flux:field>
                                                    <flux:label for="organization_number">Organisasjonsnummer</flux:label>
                                                    <flux:input id="organization_number" name="organization_number" type="text" value="{{ old('organization_number') }}" />
                                                    @error('organization_number')<flux:error>{{ $message }}</flux:error>@enderror
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
                                                    @error('industry')<flux:error>{{ $message }}</flux:error>@enderror
                                                </flux:field>
                                            </div>
                                            <flux:field>
                                                <flux:label for="website">Nettside</flux:label>
                                                <flux:input id="website" name="website" type="url" value="{{ old('website') }}" placeholder="https://" />
                                                @error('website')<flux:error>{{ $message }}</flux:error>@enderror
                                            </flux:field>
                                            <flux:field>
                                                <flux:label for="description">Beskrivelse</flux:label>
                                                <flux:textarea id="description" name="description" rows="2">{{ old('description') }}</flux:textarea>
                                                @error('description')<flux:error>{{ $message }}</flux:error>@enderror
                                            </flux:field>
                                        </div>
                                    </flux:tab.panel>

                                    <flux:tab.panel name="visit" class="pt-6">
                                        <div class="space-y-4">
                                            <flux:field>
                                                <flux:label for="address">Adresse</flux:label>
                                                <flux:input id="address" name="address" type="text" value="{{ old('address') }}" />
                                                @error('address')<flux:error>{{ $message }}</flux:error>@enderror
                                            </flux:field>
                                            <div class="grid grid-cols-3 gap-4">
                                                <flux:field>
                                                    <flux:label for="postal_code">Postnr.</flux:label>
                                                    <flux:input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code') }}" />
                                                </flux:field>
                                                <flux:field>
                                                    <flux:label for="city">Sted</flux:label>
                                                    <flux:input id="city" name="city" type="text" value="{{ old('city') }}" />
                                                </flux:field>
                                                <flux:field>
                                                    <flux:label for="country">Land</flux:label>
                                                    <flux:input id="country" name="country" type="text" value="{{ old('country', 'Norge') }}" />
                                                </flux:field>
                                            </div>
                                        </div>
                                    </flux:tab.panel>

                                    <flux:tab.panel name="billing" class="pt-6">
                                        <div class="space-y-4">
                                            <flux:field>
                                                <flux:label for="billing_address">Adresse</flux:label>
                                                <flux:input id="billing_address" name="billing_address" type="text" value="{{ old('billing_address') }}" />
                                            </flux:field>
                                            <div class="grid grid-cols-3 gap-4">
                                                <flux:field>
                                                    <flux:label for="billing_postal_code">Postnr.</flux:label>
                                                    <flux:input id="billing_postal_code" name="billing_postal_code" type="text" value="{{ old('billing_postal_code') }}" />
                                                </flux:field>
                                                <flux:field>
                                                    <flux:label for="billing_city">Sted</flux:label>
                                                    <flux:input id="billing_city" name="billing_city" type="text" value="{{ old('billing_city') }}" />
                                                </flux:field>
                                                <flux:field>
                                                    <flux:label for="billing_country">Land</flux:label>
                                                    <flux:input id="billing_country" name="billing_country" type="text" value="{{ old('billing_country') }}" />
                                                </flux:field>
                                            </div>
                                        </div>
                                    </flux:tab.panel>

                                    <flux:tab.panel name="business" class="pt-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <flux:field>
                                                <flux:label for="customer_category">Kundekategori</flux:label>
                                                <flux:select id="customer_category" name="customer_category">
                                                    <option value="">Velg</option>
                                                    <option value="a" @selected(old('customer_category') == 'a')>A-kunde</option>
                                                    <option value="b" @selected(old('customer_category') == 'b')>B-kunde</option>
                                                    <option value="c" @selected(old('customer_category') == 'c')>C-kunde</option>
                                                </flux:select>
                                            </flux:field>
                                            <flux:field>
                                                <flux:label for="credit_limit">Kredittgrense (NOK)</flux:label>
                                                <flux:input id="credit_limit" name="credit_limit" type="number" step="0.01" value="{{ old('credit_limit') }}" />
                                            </flux:field>
                                            <flux:field>
                                                <flux:label for="payment_terms_days">Betalingsfrist (dager)</flux:label>
                                                <flux:input id="payment_terms_days" name="payment_terms_days" type="number" value="{{ old('payment_terms_days', 30) }}" />
                                            </flux:field>
                                            <flux:field>
                                                <flux:label for="payment_method">Betalingsmåte</flux:label>
                                                <flux:input id="payment_method" name="payment_method" type="text" value="{{ old('payment_method') }}" />
                                            </flux:field>
                                            <flux:field class="md:col-span-2">
                                                <flux:label for="bank_account">Bankkontonummer</flux:label>
                                                <flux:input id="bank_account" name="bank_account" type="text" value="{{ old('bank_account') }}" />
                                            </flux:field>
                                        </div>
                                    </flux:tab.panel>
                                </flux:tab.group>
                            </div>
                        </flux:card>

                        {{-- Contact Info Card --}}
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <flux:icon.phone class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                        Kontaktinformasjon
                                    </flux:heading>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <flux:field>
                                        <flux:label for="email">E-post</flux:label>
                                        <flux:input id="email" name="email" type="email" value="{{ old('email') }}" />
                                        @error('email')<flux:error>{{ $message }}</flux:error>@enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label for="phone">Telefon</flux:label>
                                        <flux:input id="phone" name="phone" type="tel" value="{{ old('phone') }}" />
                                        @error('phone')<flux:error>{{ $message }}</flux:error>@enderror
                                    </flux:field>
                                </div>
                            </div>
                        </flux:card>

                        {{-- Contact Persons --}}
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <flux:icon.users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                        Kontaktpersoner
                                    </flux:heading>
                                </div>
                            </div>
                            <div class="p-6">
                                @livewire('contact-person-manager')
                            </div>
                        </flux:card>

                        {{-- Social Media --}}
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <flux:icon.share class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                        Sosiale medier
                                    </flux:heading>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <flux:field>
                                        <flux:label for="linkedin">LinkedIn</flux:label>
                                        <flux:input id="linkedin" name="linkedin" type="url" value="{{ old('linkedin') }}" placeholder="https://linkedin.com/..." />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label for="facebook">Facebook</flux:label>
                                        <flux:input id="facebook" name="facebook" type="url" value="{{ old('facebook') }}" placeholder="https://facebook.com/..." />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label for="twitter">X (Twitter)</flux:label>
                                        <flux:input id="twitter" name="twitter" type="url" value="{{ old('twitter') }}" placeholder="https://x.com/..." />
                                    </flux:field>
                                </div>
                            </div>
                        </flux:card>

                        {{-- Notes & Attachments --}}
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <flux:icon.document-text class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                        Notater og vedlegg
                                    </flux:heading>
                                </div>
                            </div>
                            <div class="p-6 space-y-4">
                                @livewire('contract-file-upload')

                                <flux:field>
                                    <flux:label for="notes">Notater</flux:label>
                                    <flux:editor
                                        name="notes"
                                        toolbar="heading | bold italic underline | bullet ordered | link"
                                    >{{ old('notes') }}</flux:editor>
                                    @error('notes')<flux:error>{{ $message }}</flux:error>@enderror
                                </flux:field>
                            </div>
                        </flux:card>
                    </div>

                    {{-- Sidebar --}}
                    <div class="lg:col-span-1 space-y-6">
                        <div class="sticky top-6 space-y-6">
                            {{-- Save Button --}}
                            <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                                <div class="p-4">
                                    <flux:button type="submit" variant="primary" class="w-full">
                                        <flux:icon.check class="w-5 h-5 mr-2" />
                                        Opprett kontakt
                                    </flux:button>
                                </div>
                            </flux:card>

                            {{-- Type & Status --}}
                            <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                                <div class="p-5 space-y-4">
                                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Type & Status</div>
                                    <flux:field>
                                        <flux:label for="type">Type</flux:label>
                                        <flux:select id="type" name="type" required>
                                            <option value="customer" @selected(old('type', 'customer') == 'customer')>Kunde</option>
                                            <option value="supplier" @selected(old('type') == 'supplier')>Leverandør</option>
                                            <option value="partner" @selected(old('type') == 'partner')>Partner</option>
                                            <option value="prospect" @selected(old('type') == 'prospect')>Prospekt</option>
                                            <option value="competitor" @selected(old('type') == 'competitor')>Konkurrent</option>
                                            <option value="other" @selected(old('type') == 'other')>Annet</option>
                                        </flux:select>
                                        @error('type')<flux:error>{{ $message }}</flux:error>@enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label for="status">Status</flux:label>
                                        <flux:select id="status" name="status" required>
                                            <option value="active" @selected(old('status', 'active') == 'active')>Aktiv</option>
                                            <option value="inactive" @selected(old('status') == 'inactive')>Inaktiv</option>
                                            <option value="prospect" @selected(old('status') == 'prospect')>Prospekt</option>
                                            <option value="archived" @selected(old('status') == 'archived')>Arkivert</option>
                                        </flux:select>
                                        @error('status')<flux:error>{{ $message }}</flux:error>@enderror
                                    </flux:field>
                                    <flux:checkbox id="is_active" name="is_active" value="1" checked>
                                        Aktiv kontakt
                                    </flux:checkbox>
                                </div>
                            </flux:card>

                            {{-- Dates --}}
                            <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                                <div class="p-5 space-y-4">
                                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Datoer</div>
                                    <flux:field>
                                        <flux:label for="customer_since">Kunde siden</flux:label>
                                        <flux:input id="customer_since" name="customer_since" type="date" value="{{ old('customer_since') }}" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label for="last_contact_date">Siste kontakt</flux:label>
                                        <flux:input id="last_contact_date" name="last_contact_date" type="date" value="{{ old('last_contact_date') }}" />
                                    </flux:field>
                                </div>
                            </flux:card>

                            {{-- Account Manager --}}
                            <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                                <div class="p-5 space-y-4">
                                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Ansvarlig</div>
                                    <flux:field>
                                        <flux:label for="account_manager_id">Account manager</flux:label>
                                        <flux:select id="account_manager_id" name="account_manager_id">
                                            <option value="">Ingen</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" @selected(old('account_manager_id') == $user->id)>{{ $user->name }}</option>
                                            @endforeach
                                        </flux:select>
                                    </flux:field>
                                </div>
                            </flux:card>
                        </div>
                    </div>
                </div>
            </form>
        </flux:main>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('company-selected', (event) => {
                const company = event.company;

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

                setInputValue('address', company.adresse);
                setInputValue('postal_code', company.postnummer);
                setInputValue('city', company.poststed);
                setInputValue('country', company.land || 'Norge');

                if (company.postadresse) {
                    setInputValue('billing_address', company.postadresse);
                    setInputValue('billing_postal_code', company.postadresse_postnummer);
                    setInputValue('billing_city', company.postadresse_poststed);
                    setInputValue('billing_country', company.postadresse_land || 'Norge');
                }

                document.getElementById('contact-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>
</x-layouts.app>
