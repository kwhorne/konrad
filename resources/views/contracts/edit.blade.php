<x-layouts.app title="Rediger kontrakt">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="contracts" />
        <x-app-header current="contracts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-8">
                <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                    Rediger kontrakt
                </flux:heading>
                <flux:text class="mt-2 text-base text-zinc-600 dark:text-zinc-400">
                    {{ $contract->contract_number }} - {{ $contract->title }}
                </flux:text>
            </div>

            <form method="POST" action="{{ route('contracts.update', $contract) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <flux:icon.file-text class="h-6 w-6 text-indigo-600 mr-3" />
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                    Grunnleggende informasjon
                                </flux:heading>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <flux:field>
                                        <flux:label for="title">Tittel *</flux:label>
                                        <flux:input 
                                            id="title" 
                                            name="title" 
                                            type="text" 
                                            value="{{ old('title', $contract->title) }}"
                                            required
                                        />
                                        @error('title')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>
                                </div>

                                <div class="md:col-span-2">
                                    <flux:field>
                                        <flux:label for="description">Beskrivelse</flux:label>
                                        <flux:textarea 
                                            id="description" 
                                            name="description" 
                                            rows="3"
                                        >{{ old('description', $contract->description) }}</flux:textarea>
                                        @error('description')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>
                                </div>

                                <flux:field>
                                    <flux:label for="type">Type *</flux:label>
                                    <flux:select id="type" name="type" required>
                                        <option value="">Velg type</option>
                                        <option value="service" @selected(old('type', $contract->type) == 'service')>Tjeneste</option>
                                        <option value="lease" @selected(old('type', $contract->type) == 'lease')>Leie</option>
                                        <option value="maintenance" @selected(old('type', $contract->type) == 'maintenance')>Vedlikehold</option>
                                        <option value="software" @selected(old('type', $contract->type) == 'software')>Programvare</option>
                                        <option value="insurance" @selected(old('type', $contract->type) == 'insurance')>Forsikring</option>
                                        <option value="employment" @selected(old('type', $contract->type) == 'employment')>Ansettelse</option>
                                        <option value="supplier" @selected(old('type', $contract->type) == 'supplier')>Leverandør</option>
                                        <option value="other" @selected(old('type', $contract->type) == 'other')>Annet</option>
                                    </flux:select>
                                    @error('type')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="status">Status *</flux:label>
                                    <flux:select id="status" name="status" required>
                                        <option value="draft" @selected(old('status', $contract->status) == 'draft')>Utkast</option>
                                        <option value="active" @selected(old('status', $contract->status) == 'active')>Aktiv</option>
                                        <option value="expiring_soon" @selected(old('status', $contract->status) == 'expiring_soon')>Utgår snart</option>
                                        <option value="expired" @selected(old('status', $contract->status) == 'expired')>Utgått</option>
                                        <option value="terminated" @selected(old('status', $contract->status) == 'terminated')>Avsluttet</option>
                                        <option value="renewed" @selected(old('status', $contract->status) == 'renewed')>Fornyet</option>
                                    </flux:select>
                                    @error('status')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>
                        </div>
                    </flux:card>

                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <flux:icon.calendar-days class="h-6 w-6 text-indigo-600 mr-3" />
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                    Datoer og varighet
                                </flux:heading>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <flux:field>
                                    <flux:label for="established_date">Etablert dato *</flux:label>
                                    <flux:input 
                                        id="established_date" 
                                        name="established_date" 
                                        type="date" 
                                        value="{{ old('established_date', $contract->established_date->format('Y-m-d')) }}"
                                        required
                                    />
                                    @error('established_date')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="start_date">Startdato *</flux:label>
                                    <flux:input 
                                        id="start_date" 
                                        name="start_date" 
                                        type="date" 
                                        value="{{ old('start_date', $contract->start_date->format('Y-m-d')) }}"
                                        required
                                    />
                                    @error('start_date')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="end_date">Sluttdato *</flux:label>
                                    <flux:input 
                                        id="end_date" 
                                        name="end_date" 
                                        type="date" 
                                        value="{{ old('end_date', $contract->end_date->format('Y-m-d')) }}"
                                        required
                                    />
                                    @error('end_date')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="notice_period_days">Oppsigelsestid (dager) *</flux:label>
                                    <flux:input 
                                        id="notice_period_days" 
                                        name="notice_period_days" 
                                        type="number" 
                                        value="{{ old('notice_period_days', $contract->notice_period_days) }}"
                                        required
                                    />
                                    @error('notice_period_days')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <div class="md:col-span-2">
                                    <flux:checkbox id="auto_renewal" name="auto_renewal" value="1" :checked="old('auto_renewal', $contract->auto_renewal)">
                                        Automatisk fornyelse
                                    </flux:checkbox>
                                </div>

                                <flux:field>
                                    <flux:label for="renewal_period_months">Fornyelsesperiode (måneder)</flux:label>
                                    <flux:input 
                                        id="renewal_period_months" 
                                        name="renewal_period_months" 
                                        type="number" 
                                        value="{{ old('renewal_period_months', $contract->renewal_period_months) }}"
                                    />
                                    @error('renewal_period_months')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>
                        </div>
                    </flux:card>

                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <flux:icon.building class="h-6 w-6 text-indigo-600 mr-3" />
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                    Bedriftsinformasjon
                                </flux:heading>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <flux:field>
                                    <flux:label for="company_name">Bedriftsnavn *</flux:label>
                                    <flux:input 
                                        id="company_name" 
                                        name="company_name" 
                                        type="text" 
                                        value="{{ old('company_name', $contract->company_name) }}"
                                        required
                                    />
                                    @error('company_name')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="company_contact">Kontaktperson</flux:label>
                                    <flux:input 
                                        id="company_contact" 
                                        name="company_contact" 
                                        type="text" 
                                        value="{{ old('company_contact', $contract->company_contact) }}"
                                    />
                                    @error('company_contact')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="company_email">E-post</flux:label>
                                    <flux:input 
                                        id="company_email" 
                                        name="company_email" 
                                        type="email" 
                                        value="{{ old('company_email', $contract->company_email) }}"
                                    />
                                    @error('company_email')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="company_phone">Telefon</flux:label>
                                    <flux:input 
                                        id="company_phone" 
                                        name="company_phone" 
                                        type="tel" 
                                        value="{{ old('company_phone', $contract->company_phone) }}"
                                    />
                                    @error('company_phone')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="department">Avdeling</flux:label>
                                    <flux:input 
                                        id="department" 
                                        name="department" 
                                        type="text" 
                                        value="{{ old('department', $contract->department) }}"
                                    />
                                    @error('department')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="group">Gruppe</flux:label>
                                    <flux:input 
                                        id="group" 
                                        name="group" 
                                        type="text" 
                                        value="{{ old('group', $contract->group) }}"
                                    />
                                    @error('group')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="asset_reference">Eiendel-referanse</flux:label>
                                    <flux:input 
                                        id="asset_reference" 
                                        name="asset_reference" 
                                        type="text" 
                                        value="{{ old('asset_reference', $contract->asset_reference) }}"
                                    />
                                    @error('asset_reference')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>
                        </div>
                    </flux:card>

                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <flux:icon.banknote class="h-6 w-6 text-indigo-600 mr-3" />
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                    Økonomi
                                </flux:heading>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <flux:field>
                                    <flux:label for="value">Verdi</flux:label>
                                    <flux:input 
                                        id="value" 
                                        name="value" 
                                        type="number" 
                                        step="0.01"
                                        value="{{ old('value', $contract->value) }}"
                                    />
                                    @error('value')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="currency">Valuta</flux:label>
                                    <flux:select id="currency" name="currency">
                                        <option value="NOK" @selected(old('currency', $contract->currency) == 'NOK')>NOK</option>
                                        <option value="EUR" @selected(old('currency', $contract->currency) == 'EUR')>EUR</option>
                                        <option value="USD" @selected(old('currency', $contract->currency) == 'USD')>USD</option>
                                        <option value="SEK" @selected(old('currency', $contract->currency) == 'SEK')>SEK</option>
                                        <option value="DKK" @selected(old('currency', $contract->currency) == 'DKK')>DKK</option>
                                    </flux:select>
                                    @error('currency')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="payment_frequency">Betalingsfrekvens</flux:label>
                                    <flux:select id="payment_frequency" name="payment_frequency">
                                        <option value="">Velg frekvens</option>
                                        <option value="monthly" @selected(old('payment_frequency', $contract->payment_frequency) == 'monthly')>Månedlig</option>
                                        <option value="quarterly" @selected(old('payment_frequency', $contract->payment_frequency) == 'quarterly')>Kvartalsvis</option>
                                        <option value="yearly" @selected(old('payment_frequency', $contract->payment_frequency) == 'yearly')>Årlig</option>
                                        <option value="one_time" @selected(old('payment_frequency', $contract->payment_frequency) == 'one_time')>Engangsbeløp</option>
                                    </flux:select>
                                    @error('payment_frequency')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>
                        </div>
                    </flux:card>

                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <flux:icon.users class="h-6 w-6 text-indigo-600 mr-3" />
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                    Ansvarlig og notater
                                </flux:heading>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                <flux:field>
                                    <flux:label for="responsible_user_id">Ansvarlig person</flux:label>
                                    <flux:select id="responsible_user_id" name="responsible_user_id">
                                        <option value="">Velg ansvarlig</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @selected(old('responsible_user_id', $contract->responsible_user_id) == $user->id)>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </flux:select>
                                    @error('responsible_user_id')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                @if($contract->attachments)
                                    <div class="mb-6">
                                        <flux:label>Eksisterende vedlegg</flux:label>
                                        <div class="mt-2 space-y-2">
                                            @foreach($contract->attachments as $index => $attachment)
                                                <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                                    <div class="flex items-center gap-3">
                                                        <flux:icon.document-text class="w-5 h-5 text-zinc-500" />
                                                        <div>
                                                            <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                                {{ $attachment['name'] }}
                                                            </flux:text>
                                                            <flux:text class="text-xs text-zinc-500">
                                                                {{ number_format($attachment['size'] / 1024, 2) }} KB
                                                            </flux:text>
                                                        </div>
                                                    </div>
                                                    <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="text-indigo-600 hover:text-indigo-700">
                                                        <flux:icon.arrow-down-tray class="w-5 h-5" />
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <flux:field>
                                    <flux:label for="attachments">Legg til flere vedlegg</flux:label>
                                    <flux:input 
                                        id="attachments" 
                                        name="attachments[]" 
                                        type="file" 
                                        multiple
                                        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                                    />
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                        PDF, Word, Excel, bilder (maks 10MB per fil)
                                    </flux:text>
                                    @error('attachments')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label for="notes">Notater</flux:label>
                                    <flux:editor 
                                        name="notes" 
                                        toolbar="heading | bold italic underline | bullet ordered | link"
                                    >{{ old('notes', $contract->notes) }}</flux:editor>
                                    @error('notes')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>
                        </div>
                    </flux:card>

                    <div class="flex items-center justify-between">
                        <flux:button href="{{ route('contracts.show', $contract) }}" variant="ghost">
                            Avbryt
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            <flux:icon.check class="w-5 h-5 mr-2" />
                            Lagre endringer
                        </flux:button>
                    </div>
                </div>
            </form>
        </flux:main>
    </div>
</x-layouts.app>
