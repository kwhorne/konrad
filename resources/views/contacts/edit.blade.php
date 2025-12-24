<x-layouts.app title="{{ $contact->company_name }}">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="contacts" />
        <x-app-header current="contacts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            {{-- Success Message --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center gap-3">
                    <flux:icon.check-circle class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                    <span class="text-emerald-800 dark:text-emerald-200">{{ session('success') }}</span>
                </div>
            @endif

            {{-- CRM Header --}}
            <div class="bg-white dark:bg-zinc-900 -mx-6 -mt-6 px-6 pt-6 pb-6 mb-8 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex items-start justify-between mb-4">
                    <flux:button href="{{ route('contacts.index') }}" variant="ghost" size="sm">
                        <flux:icon.arrow-left class="w-4 h-4 mr-1" />
                        Kontakter
                    </flux:button>
                    <div class="flex gap-2">
                        <flux:modal.trigger name="delete-contact">
                            <flux:button variant="danger" size="sm">
                                <flux:icon.trash class="w-4 h-4 mr-1" />
                                Slett
                            </flux:button>
                        </flux:modal.trigger>
                    </div>
                </div>

                <div class="flex items-start gap-5">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                        <flux:icon.building-office-2 class="w-8 h-8 text-white" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="text-xs font-mono text-zinc-500 dark:text-zinc-400">{{ $contact->contact_number }}</span>
                            <flux:badge color="{{ $contact->type === 'customer' ? 'blue' : ($contact->type === 'supplier' ? 'amber' : ($contact->type === 'partner' ? 'green' : 'zinc')) }}" size="sm">
                                {{ $contact->getTypeLabel() }}
                            </flux:badge>
                            <flux:badge color="{{ $contact->status === 'active' ? 'green' : ($contact->status === 'inactive' ? 'red' : 'zinc') }}" size="sm">
                                {{ $contact->getStatusLabel() }}
                            </flux:badge>
                        </div>
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            {{ $contact->company_name }}
                        </flux:heading>
                        @if($contact->organization_number || $contact->industry)
                            <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                @if($contact->organization_number)Org.nr: {{ $contact->organization_number }}@endif
                                @if($contact->organization_number && $contact->industry) · @endif
                                {{ $contact->industry }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Quick Contact Bar --}}
                <div class="flex flex-wrap items-center gap-4 mt-5 pt-5 border-t border-zinc-200 dark:border-zinc-700">
                    @if($contact->email)
                        <a href="mailto:{{ $contact->email }}" class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400 hover:text-blue-600">
                            <flux:icon.envelope class="w-4 h-4" />
                            {{ $contact->email }}
                        </a>
                    @endif
                    @if($contact->phone)
                        <a href="tel:{{ $contact->phone }}" class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400 hover:text-blue-600">
                            <flux:icon.phone class="w-4 h-4" />
                            {{ $contact->phone }}
                        </a>
                    @endif
                    @if($contact->mobile)
                        <a href="tel:{{ $contact->mobile }}" class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400 hover:text-blue-600">
                            <flux:icon.device-phone-mobile class="w-4 h-4" />
                            {{ $contact->mobile }}
                        </a>
                    @endif
                    @if($contact->website)
                        <a href="{{ $contact->website }}" target="_blank" class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400 hover:text-blue-600">
                            <flux:icon.globe-alt class="w-4 h-4" />
                            {{ str_replace(['https://', 'http://'], '', $contact->website) }}
                        </a>
                    @endif
                    @if($contact->accountManager)
                        <span class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <flux:icon.user class="w-4 h-4" />
                            {{ $contact->accountManager->name }}
                        </span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('contacts.update', $contact) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        {{-- Company Info with Address Tabs --}}
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="p-6">
                                <flux:tab.group>
                                    <flux:tabs variant="segmented">
                                        <flux:tab name="company" icon="building-office">Bedrift</flux:tab>
                                        <flux:tab name="visit" icon="map-pin">Besøksadresse</flux:tab>
                                        <flux:tab name="billing" icon="document-text">Faktura</flux:tab>
                                        <flux:tab name="business" icon="banknotes">Forretning</flux:tab>
                                        @if(in_array($contact->type, ['customer', 'prospect']))
                                            <flux:tab name="documents" icon="document-duplicate">Dokumenter</flux:tab>
                                        @endif
                                    </flux:tabs>

                                    <flux:tab.panel name="company" class="pt-6">
                                        <div class="space-y-4">
                                            <flux:field>
                                                <flux:label for="company_name">Bedriftsnavn</flux:label>
                                                <flux:input id="company_name" name="company_name" type="text" value="{{ old('company_name', $contact->company_name) }}" required />
                                                @error('company_name')<flux:error>{{ $message }}</flux:error>@enderror
                                            </flux:field>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <flux:field>
                                                    <flux:label for="organization_number">Organisasjonsnummer</flux:label>
                                                    <flux:input id="organization_number" name="organization_number" type="text" value="{{ old('organization_number', $contact->organization_number) }}" />
                                                    @error('organization_number')<flux:error>{{ $message }}</flux:error>@enderror
                                                </flux:field>
                                                <flux:field>
                                                    <flux:label for="industry">Bransje</flux:label>
                                                    <flux:select id="industry" name="industry">
                                                        <option value="">Velg bransje</option>
                                                        <option value="Advokater" @selected(old('industry', $contact->industry) == 'Advokater')>Advokater</option>
                                                        <option value="Bank/Finans" @selected(old('industry', $contact->industry) == 'Bank/Finans')>Bank/Finans</option>
                                                        <option value="Forsikring" @selected(old('industry', $contact->industry) == 'Forsikring')>Forsikring</option>
                                                        <option value="IT/EDB" @selected(old('industry', $contact->industry) == 'IT/EDB')>IT/EDB</option>
                                                        <option value="Grafisk" @selected(old('industry', $contact->industry) == 'Grafisk')>Grafisk</option>
                                                        <option value="Handel" @selected(old('industry', $contact->industry) == 'Handel')>Handel</option>
                                                        <option value="Hotel/Restaurant" @selected(old('industry', $contact->industry) == 'Hotel/Restaurant')>Hotel/Restaurant</option>
                                                        <option value="Industri" @selected(old('industry', $contact->industry) == 'Industri')>Industri</option>
                                                        <option value="Grossister" @selected(old('industry', $contact->industry) == 'Grossister')>Grossister</option>
                                                        <option value="Offentlig" @selected(old('industry', $contact->industry) == 'Offentlig')>Offentlig</option>
                                                        <option value="Service" @selected(old('industry', $contact->industry) == 'Service')>Service</option>
                                                        <option value="Transport" @selected(old('industry', $contact->industry) == 'Transport')>Transport</option>
                                                        <option value="Eiendom" @selected(old('industry', $contact->industry) == 'Eiendom')>Eiendom</option>
                                                        <option value="Klubb" @selected(old('industry', $contact->industry) == 'Klubb')>Klubb</option>
                                                        <option value="Annen" @selected(old('industry', $contact->industry) == 'Annen')>Annen</option>
                                                    </flux:select>
                                                    @error('industry')<flux:error>{{ $message }}</flux:error>@enderror
                                                </flux:field>
                                            </div>
                                            <flux:field>
                                                <flux:label for="website">Nettside</flux:label>
                                                <div class="flex gap-2">
                                                    <flux:input id="website" name="website" type="url" value="{{ old('website', $contact->website) }}" placeholder="https://" class="flex-1" />
                                                    @if($contact->website)
                                                        <flux:button href="{{ $contact->website }}" target="_blank" variant="ghost" size="sm" title="Åpne nettside">
                                                            <flux:icon.arrow-top-right-on-square class="w-4 h-4" />
                                                        </flux:button>
                                                    @endif
                                                </div>
                                                @error('website')<flux:error>{{ $message }}</flux:error>@enderror
                                            </flux:field>
                                            <flux:field>
                                                <flux:label for="description">Beskrivelse</flux:label>
                                                <flux:textarea id="description" name="description" rows="2">{{ old('description', $contact->description) }}</flux:textarea>
                                                @error('description')<flux:error>{{ $message }}</flux:error>@enderror
                                            </flux:field>
                                        </div>
                                    </flux:tab.panel>

                                    <flux:tab.panel name="visit" class="pt-6">
                                        <div class="space-y-4">
                                            <flux:field>
                                                <flux:label for="address">Adresse</flux:label>
                                                <flux:input id="address" name="address" type="text" value="{{ old('address', $contact->address) }}" />
                                                @error('address')<flux:error>{{ $message }}</flux:error>@enderror
                                            </flux:field>
                                            <div class="grid grid-cols-3 gap-4">
                                                <flux:field>
                                                    <flux:label for="postal_code">Postnr.</flux:label>
                                                    <flux:input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code', $contact->postal_code) }}" />
                                                </flux:field>
                                                <flux:field>
                                                    <flux:label for="city">Sted</flux:label>
                                                    <flux:input id="city" name="city" type="text" value="{{ old('city', $contact->city) }}" />
                                                </flux:field>
                                                <flux:field>
                                                    <flux:label for="country">Land</flux:label>
                                                    <flux:input id="country" name="country" type="text" value="{{ old('country', $contact->country) }}" />
                                                </flux:field>
                                            </div>
                                        </div>
                                    </flux:tab.panel>

                                    <flux:tab.panel name="billing" class="pt-6">
                                        <div class="space-y-4">
                                            <flux:field>
                                                <flux:label for="billing_address">Adresse</flux:label>
                                                <flux:input id="billing_address" name="billing_address" type="text" value="{{ old('billing_address', $contact->billing_address) }}" />
                                            </flux:field>
                                            <div class="grid grid-cols-3 gap-4">
                                                <flux:field>
                                                    <flux:label for="billing_postal_code">Postnr.</flux:label>
                                                    <flux:input id="billing_postal_code" name="billing_postal_code" type="text" value="{{ old('billing_postal_code', $contact->billing_postal_code) }}" />
                                                </flux:field>
                                                <flux:field>
                                                    <flux:label for="billing_city">Sted</flux:label>
                                                    <flux:input id="billing_city" name="billing_city" type="text" value="{{ old('billing_city', $contact->billing_city) }}" />
                                                </flux:field>
                                                <flux:field>
                                                    <flux:label for="billing_country">Land</flux:label>
                                                    <flux:input id="billing_country" name="billing_country" type="text" value="{{ old('billing_country', $contact->billing_country) }}" />
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
                                                    <option value="a" @selected(old('customer_category', $contact->customer_category) == 'a')>A-kunde</option>
                                                    <option value="b" @selected(old('customer_category', $contact->customer_category) == 'b')>B-kunde</option>
                                                    <option value="c" @selected(old('customer_category', $contact->customer_category) == 'c')>C-kunde</option>
                                                </flux:select>
                                            </flux:field>
                                            <flux:field>
                                                <flux:label for="credit_limit">Kredittgrense (NOK)</flux:label>
                                                <flux:input id="credit_limit" name="credit_limit" type="number" step="0.01" value="{{ old('credit_limit', $contact->credit_limit) }}" />
                                            </flux:field>
                                            <flux:field>
                                                <flux:label for="payment_terms_days">Betalingsfrist (dager)</flux:label>
                                                <flux:input id="payment_terms_days" name="payment_terms_days" type="number" value="{{ old('payment_terms_days', $contact->payment_terms_days) }}" />
                                            </flux:field>
                                            <flux:field>
                                                <flux:label for="payment_method">Betalingsmåte</flux:label>
                                                <flux:input id="payment_method" name="payment_method" type="text" value="{{ old('payment_method', $contact->payment_method) }}" />
                                            </flux:field>
                                            <flux:field class="md:col-span-2">
                                                <flux:label for="bank_account">Bankkontonummer</flux:label>
                                                <flux:input id="bank_account" name="bank_account" type="text" value="{{ old('bank_account', $contact->bank_account) }}" />
                                            </flux:field>
                                        </div>
                                    </flux:tab.panel>

                                    @if(in_array($contact->type, ['customer', 'prospect']))
                                        <flux:tab.panel name="documents" class="pt-6">
                                            @livewire('contact-documents-manager', ['contactId' => $contact->id])
                                        </flux:tab.panel>
                                    @endif
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
                                        <div class="flex gap-2">
                                            <flux:input id="email" name="email" type="email" value="{{ old('email', $contact->email) }}" class="flex-1" />
                                            @if($contact->email)
                                                <flux:button href="mailto:{{ $contact->email }}" variant="ghost" size="sm" title="Send e-post">
                                                    <flux:icon.envelope class="w-4 h-4" />
                                                </flux:button>
                                            @endif
                                        </div>
                                        @error('email')<flux:error>{{ $message }}</flux:error>@enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label for="phone">Telefon</flux:label>
                                        <div class="flex gap-2">
                                            <flux:input id="phone" name="phone" type="tel" value="{{ old('phone', $contact->phone) }}" class="flex-1" />
                                            @if($contact->phone)
                                                <flux:button href="tel:{{ $contact->phone }}" variant="ghost" size="sm" title="Ring">
                                                    <flux:icon.phone class="w-4 h-4" />
                                                </flux:button>
                                            @endif
                                        </div>
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
                                @livewire('contact-person-manager', ['contactId' => $contact->id])
                            </div>
                        </flux:card>

                        {{-- Activities --}}
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-950/20 dark:to-indigo-950/20 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <flux:icon.clipboard-document-list class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                            Aktiviteter
                                        </flux:heading>
                                    </div>
                                    <flux:button href="{{ route('activity-types.index') }}" variant="ghost" size="sm">
                                        <flux:icon.cog-6-tooth class="w-4 h-4 mr-1" />
                                        Aktivitetstyper
                                    </flux:button>
                                </div>
                            </div>
                            <div class="p-6">
                                @livewire('activity-manager', ['contactId' => $contact->id])
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
                                        <div class="flex gap-2">
                                            <flux:input id="linkedin" name="linkedin" type="url" value="{{ old('linkedin', $contact->linkedin) }}" placeholder="https://linkedin.com/..." class="flex-1" />
                                            @if($contact->linkedin)
                                                <flux:button href="{{ $contact->linkedin }}" target="_blank" variant="ghost" size="sm" title="Åpne LinkedIn">
                                                    <flux:icon.arrow-top-right-on-square class="w-4 h-4" />
                                                </flux:button>
                                            @endif
                                        </div>
                                    </flux:field>
                                    <flux:field>
                                        <flux:label for="facebook">Facebook</flux:label>
                                        <div class="flex gap-2">
                                            <flux:input id="facebook" name="facebook" type="url" value="{{ old('facebook', $contact->facebook) }}" placeholder="https://facebook.com/..." class="flex-1" />
                                            @if($contact->facebook)
                                                <flux:button href="{{ $contact->facebook }}" target="_blank" variant="ghost" size="sm" title="Åpne Facebook">
                                                    <flux:icon.arrow-top-right-on-square class="w-4 h-4" />
                                                </flux:button>
                                            @endif
                                        </div>
                                    </flux:field>
                                    <flux:field>
                                        <flux:label for="twitter">X (Twitter)</flux:label>
                                        <div class="flex gap-2">
                                            <flux:input id="twitter" name="twitter" type="url" value="{{ old('twitter', $contact->twitter) }}" placeholder="https://x.com/..." class="flex-1" />
                                            @if($contact->twitter)
                                                <flux:button href="{{ $contact->twitter }}" target="_blank" variant="ghost" size="sm" title="Åpne X">
                                                    <flux:icon.arrow-top-right-on-square class="w-4 h-4" />
                                                </flux:button>
                                            @endif
                                        </div>
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
                                @if($contact->attachments && count($contact->attachments) > 0)
                                    <div>
                                        <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Eksisterende vedlegg</div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($contact->attachments as $attachment)
                                                <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-zinc-100 dark:bg-zinc-800 rounded-lg text-sm hover:bg-zinc-200 dark:hover:bg-zinc-700">
                                                    <flux:icon.document class="w-4 h-4" />
                                                    {{ $attachment['name'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @livewire('contract-file-upload')

                                <flux:field>
                                    <flux:label for="notes">Notater</flux:label>
                                    <flux:editor
                                        name="notes"
                                        toolbar="heading | bold italic underline | bullet ordered | link"
                                    >{{ old('notes', $contact->notes) }}</flux:editor>
                                    @error('notes')<flux:error>{{ $message }}</flux:error>@enderror
                                </flux:field>
                            </div>
                        </flux:card>
                    </div>

                    {{-- Sidebar --}}
                    <div class="lg:col-span-1 space-y-6">
                        {{-- Save Button (Sticky) --}}
                        <div class="sticky top-6 space-y-6">
                            <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                                <div class="p-4">
                                    <flux:button type="submit" variant="primary" class="w-full">
                                        <flux:icon.check class="w-5 h-5 mr-2" />
                                        Lagre endringer
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
                                            <option value="customer" @selected(old('type', $contact->type) == 'customer')>Kunde</option>
                                            <option value="supplier" @selected(old('type', $contact->type) == 'supplier')>Leverandør</option>
                                            <option value="partner" @selected(old('type', $contact->type) == 'partner')>Partner</option>
                                            <option value="prospect" @selected(old('type', $contact->type) == 'prospect')>Prospekt</option>
                                            <option value="competitor" @selected(old('type', $contact->type) == 'competitor')>Konkurrent</option>
                                            <option value="other" @selected(old('type', $contact->type) == 'other')>Annet</option>
                                        </flux:select>
                                    </flux:field>
                                    <flux:field>
                                        <flux:label for="status">Status</flux:label>
                                        <flux:select id="status" name="status" required>
                                            <option value="active" @selected(old('status', $contact->status) == 'active')>Aktiv</option>
                                            <option value="inactive" @selected(old('status', $contact->status) == 'inactive')>Inaktiv</option>
                                            <option value="prospect" @selected(old('status', $contact->status) == 'prospect')>Prospekt</option>
                                            <option value="archived" @selected(old('status', $contact->status) == 'archived')>Arkivert</option>
                                        </flux:select>
                                    </flux:field>
                                    <flux:checkbox id="is_active" name="is_active" value="1" :checked="old('is_active', $contact->is_active)">
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
                                        <flux:input id="customer_since" name="customer_since" type="date" value="{{ old('customer_since', $contact->customer_since?->format('Y-m-d')) }}" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label for="last_contact_date">Siste kontakt</flux:label>
                                        <flux:input id="last_contact_date" name="last_contact_date" type="date" value="{{ old('last_contact_date', $contact->last_contact_date?->format('Y-m-d')) }}" />
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
                                                <option value="{{ $user->id }}" @selected(old('account_manager_id', $contact->account_manager_id) == $user->id)>{{ $user->name }}</option>
                                            @endforeach
                                        </flux:select>
                                    </flux:field>
                                </div>
                            </flux:card>

                            {{-- System Info --}}
                            <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                                <div class="p-5">
                                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-3">Systeminfo</div>
                                    <dl class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <dt class="text-zinc-500">ID</dt>
                                            <dd class="text-zinc-900 dark:text-white font-mono">{{ $contact->id }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-zinc-500">Opprettet av</dt>
                                            <dd class="text-zinc-900 dark:text-white">{{ $contact->creator->name ?? '-' }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-zinc-500">Opprettet</dt>
                                            <dd class="text-zinc-900 dark:text-white">{{ $contact->created_at->format('d.m.Y H:i') }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-zinc-500">Oppdatert</dt>
                                            <dd class="text-zinc-900 dark:text-white">{{ $contact->updated_at->format('d.m.Y H:i') }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </flux:card>
                        </div>
                    </div>
                </div>
            </form>
        </flux:main>

        {{-- Delete Modal --}}
        <flux:modal name="delete-contact" class="min-w-[22rem]">
            <form method="POST" action="{{ route('contacts.destroy', $contact) }}">
                @csrf
                @method('DELETE')
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Slett kontakt?</flux:heading>
                        <flux:text class="mt-2">
                            <p>Du er i ferd med å slette <strong>{{ $contact->company_name }}</strong>.</p>
                            <p class="mt-1">Denne handlingen kan ikke angres.</p>
                        </flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="ghost">Avbryt</flux:button>
                        </flux:modal.close>
                        <flux:button type="submit" variant="danger">Slett</flux:button>
                    </div>
                </div>
            </form>
        </flux:modal>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</x-layouts.app>
