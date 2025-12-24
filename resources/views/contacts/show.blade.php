<x-layouts.app title="{{ $contact->company_name }}">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="contacts" />
        <x-app-header current="contacts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            {{-- Hero Header --}}
            <div class="bg-white dark:bg-zinc-900 -mx-6 -mt-6 px-6 pt-6 pb-8 mb-8 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex items-start justify-between mb-6">
                    <flux:button href="{{ route('contacts.index') }}" variant="ghost" size="sm">
                        <flux:icon.arrow-left class="w-4 h-4 mr-1" />
                        Kontakter
                    </flux:button>
                    <div class="flex gap-2">
                        <flux:button href="{{ route('contacts.edit', $contact) }}" variant="filled">
                            <flux:icon.pencil class="w-4 h-4 mr-2" />
                            Rediger
                        </flux:button>
                        <flux:modal.trigger name="delete-contact">
                            <flux:button variant="danger">
                                <flux:icon.trash class="w-4 h-4" />
                            </flux:button>
                        </flux:modal.trigger>
                    </div>
                </div>

                <div class="flex items-start gap-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                        <flux:icon.building-office-2 class="w-10 h-10 text-white" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <flux:badge color="{{ $contact->type === 'customer' ? 'blue' : ($contact->type === 'supplier' ? 'amber' : ($contact->type === 'partner' ? 'green' : ($contact->type === 'prospect' ? 'purple' : 'zinc'))) }}" size="sm">
                                {{ $contact->getTypeLabel() }}
                            </flux:badge>
                            <flux:badge color="{{ $contact->status === 'active' ? 'green' : ($contact->status === 'inactive' ? 'red' : ($contact->status === 'prospect' ? 'purple' : 'zinc')) }}" size="sm">
                                {{ $contact->getStatusLabel() }}
                            </flux:badge>
                            @if($contact->customer_category)
                                <flux:badge color="purple" size="sm">{{ strtoupper($contact->customer_category) }}-kunde</flux:badge>
                            @endif
                            @if(!$contact->is_active)
                                <flux:badge color="red" size="sm">Deaktivert</flux:badge>
                            @endif
                        </div>
                        <flux:heading size="2xl" level="1" class="text-zinc-900 dark:text-white mb-1">
                            {{ $contact->company_name }}
                        </flux:heading>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="font-mono">{{ $contact->contact_number }}</span>
                            @if($contact->organization_number)
                                <span>Org.nr: {{ $contact->organization_number }}</span>
                            @endif
                            @if($contact->industry)
                                <span>{{ $contact->industry }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Quick Contact Info --}}
                <div class="flex flex-wrap gap-6 mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <a href="{{ $contact->email ? 'mailto:' . $contact->email : '#' }}" class="flex items-center gap-2 text-sm {{ $contact->email ? 'text-zinc-600 dark:text-zinc-400 hover:text-blue-600 dark:hover:text-blue-400' : 'text-zinc-400 dark:text-zinc-600' }}">
                        <flux:icon.envelope class="w-4 h-4" />
                        {{ $contact->email ?: 'Ingen e-post' }}
                    </a>
                    <a href="{{ $contact->phone ? 'tel:' . $contact->phone : '#' }}" class="flex items-center gap-2 text-sm {{ $contact->phone ? 'text-zinc-600 dark:text-zinc-400 hover:text-blue-600 dark:hover:text-blue-400' : 'text-zinc-400 dark:text-zinc-600' }}">
                        <flux:icon.phone class="w-4 h-4" />
                        {{ $contact->phone ?: 'Ingen telefon' }}
                    </a>
                    <span class="flex items-center gap-2 text-sm {{ $contact->mobile ? 'text-zinc-600 dark:text-zinc-400' : 'text-zinc-400 dark:text-zinc-600' }}">
                        <flux:icon.device-phone-mobile class="w-4 h-4" />
                        {{ $contact->mobile ?: 'Ingen mobil' }}
                    </span>
                    @if($contact->website)
                        <a href="{{ $contact->website }}" target="_blank" class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400 hover:text-blue-600 dark:hover:text-blue-400">
                            <flux:icon.globe-alt class="w-4 h-4" />
                            {{ str_replace(['https://', 'http://'], '', $contact->website) }}
                        </a>
                    @endif
                    @if($contact->fax)
                        <span class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <flux:icon.printer class="w-4 h-4" />
                            Fax: {{ $contact->fax }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Stats Row --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                            <flux:icon.banknotes class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Kredittgrense</div>
                            <div class="text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ $contact->credit_limit ? number_format($contact->credit_limit, 0, ',', ' ') . ' kr' : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <flux:icon.clock class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Betalingsfrist</div>
                            <div class="text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ $contact->payment_terms_days ?? '-' }} {{ $contact->payment_terms_days ? 'dager' : '' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <flux:icon.calendar class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Kunde siden</div>
                            <div class="text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ $contact->customer_since ? $contact->customer_since->format('d.m.Y') : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                            <flux:icon.chat-bubble-left-right class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Siste kontakt</div>
                            <div class="text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ $contact->last_contact_date ? $contact->last_contact_date->format('d.m.Y') : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Company Information --}}
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-3">
                                <flux:icon.building-office class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                    Bedriftsinformasjon
                                </flux:heading>
                            </div>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Bedriftsnavn</dt>
                                    <dd class="mt-1 text-zinc-900 dark:text-white">{{ $contact->company_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Organisasjonsnummer</dt>
                                    <dd class="mt-1 text-zinc-900 dark:text-white font-mono">{{ $contact->organization_number ?: '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Bransje</dt>
                                    <dd class="mt-1 text-zinc-900 dark:text-white">{{ $contact->industry ?: '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Nettside</dt>
                                    <dd class="mt-1">
                                        @if($contact->website)
                                            <a href="{{ $contact->website }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $contact->website }}
                                            </a>
                                        @else
                                            <span class="text-zinc-900 dark:text-white">-</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Beskrivelse</dt>
                                    <dd class="mt-1 text-zinc-900 dark:text-white whitespace-pre-wrap">{{ $contact->description ?: '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </flux:card>

                    {{-- Contact Persons --}}
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <flux:icon.users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                        Kontaktpersoner
                                    </flux:heading>
                                </div>
                                <flux:badge size="sm">{{ $contact->contactPersons->count() }}</flux:badge>
                            </div>
                        </div>
                        <div class="p-6">
                            @if($contact->contactPersons->count() > 0)
                                <div class="grid gap-4">
                                    @foreach($contact->contactPersons as $person)
                                        <div class="flex items-start gap-4 p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">
                                                <span class="text-white font-semibold text-sm">{{ $person->getInitials() }}</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-semibold text-zinc-900 dark:text-white">{{ $person->name }}</span>
                                                    @if($person->is_primary)
                                                        <flux:badge color="green" size="sm">Primær</flux:badge>
                                                    @endif
                                                </div>
                                                @if($person->title || $person->department)
                                                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                                                        {{ $person->title }}@if($person->title && $person->department) · @endif{{ $person->department }}
                                                    </div>
                                                @endif
                                                <div class="flex flex-wrap gap-4 mt-2">
                                                    @if($person->email)
                                                        <a href="mailto:{{ $person->email }}" class="flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                                            <flux:icon.envelope class="w-4 h-4" />
                                                            {{ $person->email }}
                                                        </a>
                                                    @endif
                                                    @if($person->phone)
                                                        <a href="tel:{{ $person->phone }}" class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white">
                                                            <flux:icon.phone class="w-4 h-4" />
                                                            {{ $person->phone }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                                    <flux:icon.users class="w-12 h-12 mx-auto mb-3 opacity-50" />
                                    <p>Ingen kontaktpersoner registrert</p>
                                    <flux:button href="{{ route('contacts.edit', $contact) }}" variant="ghost" size="sm" class="mt-3">
                                        Legg til kontaktperson
                                    </flux:button>
                                </div>
                            @endif
                        </div>
                    </flux:card>

                    {{-- Addresses --}}
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-3">
                                <flux:icon.map-pin class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                    Adresser
                                </flux:heading>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Besøksadresse</div>
                                    @if($contact->address)
                                        <div class="text-zinc-900 dark:text-white">
                                            {{ $contact->address }}<br>
                                            {{ $contact->postal_code }} {{ $contact->city }}<br>
                                            {{ $contact->country }}
                                        </div>
                                    @else
                                        <div class="text-zinc-400 dark:text-zinc-600">Ikke registrert</div>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Fakturaadresse</div>
                                    @if($contact->billing_address)
                                        <div class="text-zinc-900 dark:text-white">
                                            {{ $contact->billing_address }}<br>
                                            {{ $contact->billing_postal_code }} {{ $contact->billing_city }}<br>
                                            {{ $contact->billing_country }}
                                        </div>
                                    @else
                                        <div class="text-zinc-400 dark:text-zinc-600">Samme som besøksadresse</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Business Details --}}
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-3">
                                <flux:icon.banknote class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                    Forretningsdetaljer
                                </flux:heading>
                            </div>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Kundekategori</dt>
                                    <dd class="mt-1 text-zinc-900 dark:text-white">
                                        @if($contact->customer_category)
                                            <flux:badge color="purple">{{ strtoupper($contact->customer_category) }}-kunde</flux:badge>
                                        @else
                                            -
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Kredittgrense</dt>
                                    <dd class="mt-1 text-zinc-900 dark:text-white">
                                        {{ $contact->credit_limit ? number_format($contact->credit_limit, 2, ',', ' ') . ' NOK' : '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Betalingsbetingelser</dt>
                                    <dd class="mt-1 text-zinc-900 dark:text-white">
                                        {{ $contact->payment_terms_days ? $contact->payment_terms_days . ' dager' : '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Betalingsmåte</dt>
                                    <dd class="mt-1 text-zinc-900 dark:text-white">{{ $contact->payment_method ?: '-' }}</dd>
                                </div>
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Bankkontonummer</dt>
                                    <dd class="mt-1 text-zinc-900 dark:text-white font-mono">{{ $contact->bank_account ?: '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </flux:card>

                    {{-- Notes --}}
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-3">
                                <flux:icon.document-text class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                    Notater
                                </flux:heading>
                            </div>
                        </div>
                        <div class="p-6">
                            @if($contact->notes)
                                <div class="prose dark:prose-invert max-w-none prose-zinc prose-sm">
                                    {!! $contact->notes !!}
                                </div>
                            @else
                                <div class="text-zinc-400 dark:text-zinc-600">Ingen notater</div>
                            @endif
                        </div>
                    </flux:card>

                    {{-- Attachments --}}
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <flux:icon.paper-clip class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                        Vedlegg
                                    </flux:heading>
                                </div>
                                @if($contact->attachments && count($contact->attachments) > 0)
                                    <flux:badge size="sm">{{ count($contact->attachments) }}</flux:badge>
                                @endif
                            </div>
                        </div>
                        <div class="p-6">
                            @if($contact->attachments && count($contact->attachments) > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($contact->attachments as $attachment)
                                        <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="flex items-center gap-3 p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors group">
                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 {{ str_contains($attachment['mime_type'] ?? '', 'pdf') ? 'bg-red-100 dark:bg-red-900/30' : (str_contains($attachment['mime_type'] ?? '', 'image') ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-zinc-200 dark:bg-zinc-700') }}">
                                                @if(str_contains($attachment['mime_type'] ?? '', 'pdf'))
                                                    <flux:icon.document class="w-5 h-5 text-red-600 dark:text-red-400" />
                                                @elseif(str_contains($attachment['mime_type'] ?? '', 'image'))
                                                    <flux:icon.photo class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                                @else
                                                    <flux:icon.document-text class="w-5 h-5 text-zinc-600 dark:text-zinc-400" />
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-medium text-zinc-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ $attachment['name'] }}</div>
                                                <div class="text-xs text-zinc-500">{{ number_format(($attachment['size'] ?? 0) / 1024, 1) }} KB</div>
                                            </div>
                                            <flux:icon.arrow-down-tray class="w-5 h-5 text-zinc-400 group-hover:text-blue-600 dark:group-hover:text-blue-400" />
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                                    <flux:icon.paper-clip class="w-12 h-12 mx-auto mb-3 opacity-50" />
                                    <p>Ingen vedlegg</p>
                                </div>
                            @endif
                        </div>
                    </flux:card>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Type & Status --}}
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                        <div class="p-5">
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-3">Type & Status</div>
                            <dl class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">Type</dt>
                                    <dd>
                                        <flux:badge color="{{ $contact->type === 'customer' ? 'blue' : ($contact->type === 'supplier' ? 'amber' : ($contact->type === 'partner' ? 'green' : ($contact->type === 'prospect' ? 'purple' : 'zinc'))) }}" size="sm">
                                            {{ $contact->getTypeLabel() }}
                                        </flux:badge>
                                    </dd>
                                </div>
                                <div class="flex justify-between items-center">
                                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">Status</dt>
                                    <dd>
                                        <flux:badge color="{{ $contact->status === 'active' ? 'green' : ($contact->status === 'inactive' ? 'red' : ($contact->status === 'prospect' ? 'purple' : 'zinc')) }}" size="sm">
                                            {{ $contact->getStatusLabel() }}
                                        </flux:badge>
                                    </dd>
                                </div>
                                <div class="flex justify-between items-center">
                                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">Aktiv</dt>
                                    <dd>
                                        @if($contact->is_active)
                                            <flux:badge color="green" size="sm">Ja</flux:badge>
                                        @else
                                            <flux:badge color="red" size="sm">Nei</flux:badge>
                                        @endif
                                    </dd>
                                </div>
                                @if($contact->customer_category)
                                    <div class="flex justify-between items-center">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">Kategori</dt>
                                        <dd>
                                            <flux:badge color="purple" size="sm">{{ strtoupper($contact->customer_category) }}-kunde</flux:badge>
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </flux:card>

                    {{-- Account Manager --}}
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                        <div class="p-5">
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-3">Kundeansvarlig</div>
                            @if($contact->accountManager)
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                                        <span class="text-white font-semibold text-sm">{{ strtoupper(substr($contact->accountManager->name, 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $contact->accountManager->name }}</div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $contact->accountManager->email }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="text-zinc-400 dark:text-zinc-600">Ingen tildelt</div>
                            @endif
                        </div>
                    </flux:card>

                    {{-- Dates --}}
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                        <div class="p-5">
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-3">Datoer</div>
                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Kunde siden</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ $contact->customer_since ? $contact->customer_since->format('d.m.Y') : '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Siste kontakt</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ $contact->last_contact_date ? $contact->last_contact_date->format('d.m.Y') : '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Opprettet</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ $contact->created_at->format('d.m.Y H:i') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Oppdatert</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ $contact->updated_at->format('d.m.Y H:i') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </flux:card>

                    {{-- Social Media --}}
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                        <div class="p-5">
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-3">Sosiale medier</div>
                            @if($contact->linkedin || $contact->facebook || $contact->twitter)
                                <div class="flex gap-2">
                                    @if($contact->linkedin)
                                        <a href="{{ $contact->linkedin }}" target="_blank" class="w-10 h-10 bg-[#0A66C2] rounded-lg flex items-center justify-center hover:opacity-80 transition-opacity" title="LinkedIn">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                        </a>
                                    @endif
                                    @if($contact->facebook)
                                        <a href="{{ $contact->facebook }}" target="_blank" class="w-10 h-10 bg-[#1877F2] rounded-lg flex items-center justify-center hover:opacity-80 transition-opacity" title="Facebook">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        </a>
                                    @endif
                                    @if($contact->twitter)
                                        <a href="{{ $contact->twitter }}" target="_blank" class="w-10 h-10 bg-[#000000] rounded-lg flex items-center justify-center hover:opacity-80 transition-opacity" title="X (Twitter)">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="text-zinc-400 dark:text-zinc-600">Ingen sosiale medier registrert</div>
                            @endif
                        </div>
                    </flux:card>

                    {{-- Tags --}}
                    @if($contact->tags && count($contact->tags) > 0)
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                            <div class="p-5">
                                <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-3">Tags</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($contact->tags as $tag)
                                        <flux:badge color="zinc" size="sm">{{ $tag }}</flux:badge>
                                    @endforeach
                                </div>
                            </div>
                        </flux:card>
                    @endif

                    {{-- Metadata --}}
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                        <div class="p-5">
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-3">Systeminfo</div>
                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Kontaktnummer</dt>
                                    <dd class="text-zinc-900 dark:text-white font-mono text-xs">{{ $contact->contact_number }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Opprettet av</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ $contact->creator->name ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">ID</dt>
                                    <dd class="text-zinc-900 dark:text-white font-mono text-xs">{{ $contact->id }}</dd>
                                </div>
                            </dl>
                        </div>
                    </flux:card>
                </div>
            </div>
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
