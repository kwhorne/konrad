<x-layouts.app title="Kontraktdetaljer">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="contracts" />
        <x-app-header current="contracts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        {{ $contract->title }}
                    </flux:heading>
                    <flux:text class="mt-2 text-base text-zinc-600 dark:text-zinc-400">
                        {{ $contract->contract_number }}
                    </flux:text>
                </div>
                <div class="flex gap-3">
                    <flux:button href="{{ route('contracts.edit', $contract) }}" variant="ghost">
                        <flux:icon.pencil class="w-5 h-5 mr-2" />
                        Rediger
                    </flux:button>
                    <flux:modal.trigger name="delete-contract">
                        <flux:button variant="danger">
                            <flux:icon.trash class="w-5 h-5 mr-2" />
                            Slett
                        </flux:button>
                    </flux:modal.trigger>
                </div>

                <flux:modal name="delete-contract" class="min-w-[22rem]">
                    <form method="POST" action="{{ route('contracts.destroy', $contract) }}">
                        @csrf
                        @method('DELETE')
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">Slett kontrakt?</flux:heading>
                                <flux:text class="mt-2">
                                    <p>Du er i ferd med å slette <strong>{{ $contract->title }}</strong>.</p>
                                    <p>Denne handlingen kan ikke angres.</p>
                                </flux:text>
                            </div>
                            <div class="flex gap-2">
                                <flux:spacer />
                                <flux:modal.close>
                                    <flux:button variant="ghost">Avbryt</flux:button>
                                </flux:modal.close>
                                <flux:button type="submit" variant="danger">Slett kontrakt</flux:button>
                            </div>
                        </div>
                    </form>
                </flux:modal>
            </div>

            @if(session('success'))
                <flux:card class="mb-6 bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
                    <div class="p-4 flex items-center">
                        <flux:icon.check-circle class="h-5 w-5 text-green-600 dark:text-green-400 mr-3" />
                        <flux:text class="text-green-800 dark:text-green-200 font-medium">
                            {{ session('success') }}
                        </flux:text>
                    </div>
                </flux:card>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                    <div class="p-6">
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">
                            Status
                        </flux:text>
                        <flux:badge variant="{{ $contract->status_badge_color }}" class="text-base">
                            {{ $contract->status_label }}
                        </flux:badge>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                    <div class="p-6">
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">
                            Type
                        </flux:text>
                        <flux:text class="text-lg font-medium text-zinc-900 dark:text-white">
                            {{ $contract->type_label }}
                        </flux:text>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                    <div class="p-6">
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">
                            Verdi
                        </flux:text>
                        <flux:text class="text-lg font-medium text-zinc-900 dark:text-white">
                            {{ $contract->formatted_value }}
                        </flux:text>
                    </div>
                </flux:card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                    <div class="p-6">
                        <div class="flex items-center mb-6">
                            <flux:icon.calendar-days class="h-6 w-6 text-indigo-600 mr-3" />
                            <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                Datoer og varighet
                            </flux:heading>
                        </div>

                        <div class="space-y-4">
                            <div class="flex justify-between items-start">
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    Etablert dato
                                </flux:text>
                                <flux:text class="font-medium text-zinc-900 dark:text-white">
                                    {{ $contract->established_date->format('d.m.Y') }}
                                </flux:text>
                            </div>

                            <div class="flex justify-between items-start">
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    Startdato
                                </flux:text>
                                <flux:text class="font-medium text-zinc-900 dark:text-white">
                                    {{ $contract->start_date->format('d.m.Y') }}
                                </flux:text>
                            </div>

                            <div class="flex justify-between items-start">
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    Sluttdato
                                </flux:text>
                                <div class="text-right">
                                    <flux:text class="font-medium text-zinc-900 dark:text-white block">
                                        {{ $contract->end_date->format('d.m.Y') }}
                                    </flux:text>
                                    @if($contract->is_expiring_soon)
                                        <flux:text class="text-sm text-amber-600 dark:text-amber-400">
                                            Utgår om {{ $contract->days_until_expiry }} dager
                                        </flux:text>
                                    @elseif($contract->is_expired)
                                        <flux:text class="text-sm text-red-600 dark:text-red-400">
                                            Utgått for {{ abs($contract->days_until_expiry) }} dager siden
                                        </flux:text>
                                    @else
                                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $contract->days_until_expiry }} dager igjen
                                        </flux:text>
                                    @endif
                                </div>
                            </div>

                            <div class="flex justify-between items-start">
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    Oppsigelsestid
                                </flux:text>
                                <flux:text class="font-medium text-zinc-900 dark:text-white">
                                    {{ $contract->notice_period_days }} dager
                                </flux:text>
                            </div>

                            @if($contract->auto_renewal)
                                <div class="flex justify-between items-start">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                                        Automatisk fornyelse
                                    </flux:text>
                                    <div class="text-right">
                                        <flux:badge variant="success">Aktivert</flux:badge>
                                        @if($contract->renewal_period_months)
                                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 block mt-1">
                                                {{ $contract->renewal_period_months }} måneder
                                            </flux:text>
                                        @endif
                                    </div>
                                </div>
                            @endif
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

                        <div class="space-y-4">
                            <div class="flex justify-between items-start">
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    Bedrift
                                </flux:text>
                                <flux:text class="font-medium text-zinc-900 dark:text-white">
                                    {{ $contract->company_name }}
                                </flux:text>
                            </div>

                            @if($contract->company_contact)
                                <div class="flex justify-between items-start">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                                        Kontaktperson
                                    </flux:text>
                                    <flux:text class="font-medium text-zinc-900 dark:text-white">
                                        {{ $contract->company_contact }}
                                    </flux:text>
                                </div>
                            @endif

                            @if($contract->company_email)
                                <div class="flex justify-between items-start">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                                        E-post
                                    </flux:text>
                                    <a href="mailto:{{ $contract->company_email }}" class="font-medium text-indigo-600 hover:text-indigo-700">
                                        {{ $contract->company_email }}
                                    </a>
                                </div>
                            @endif

                            @if($contract->company_phone)
                                <div class="flex justify-between items-start">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                                        Telefon
                                    </flux:text>
                                    <a href="tel:{{ $contract->company_phone }}" class="font-medium text-indigo-600 hover:text-indigo-700">
                                        {{ $contract->company_phone }}
                                    </a>
                                </div>
                            @endif

                            @if($contract->department)
                                <div class="flex justify-between items-start">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                                        Avdeling
                                    </flux:text>
                                    <flux:text class="font-medium text-zinc-900 dark:text-white">
                                        {{ $contract->department }}
                                    </flux:text>
                                </div>
                            @endif

                            @if($contract->group)
                                <div class="flex justify-between items-start">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                                        Gruppe
                                    </flux:text>
                                    <flux:text class="font-medium text-zinc-900 dark:text-white">
                                        {{ $contract->group }}
                                    </flux:text>
                                </div>
                            @endif

                            @if($contract->asset_reference)
                                <div class="flex justify-between items-start">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                                        Eiendel
                                    </flux:text>
                                    <flux:text class="font-medium text-zinc-900 dark:text-white">
                                        {{ $contract->asset_reference }}
                                    </flux:text>
                                </div>
                            @endif
                        </div>
                    </div>
                </flux:card>

                @if($contract->value || $contract->payment_frequency)
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <flux:icon.banknote class="h-6 w-6 text-indigo-600 mr-3" />
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                    Økonomi
                                </flux:heading>
                            </div>

                            <div class="space-y-4">
                                @if($contract->value)
                                    <div class="flex justify-between items-start">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            Verdi
                                        </flux:text>
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                                            {{ $contract->formatted_value }}
                                        </flux:text>
                                    </div>
                                @endif

                                @if($contract->payment_frequency)
                                    <div class="flex justify-between items-start">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            Betalingsfrekvens
                                        </flux:text>
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">
                                            @switch($contract->payment_frequency)
                                                @case('monthly') Månedlig @break
                                                @case('quarterly') Kvartalsvis @break
                                                @case('yearly') Årlig @break
                                                @case('one_time') Engangsbeløp @break
                                            @endswitch
                                        </flux:text>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </flux:card>
                @endif

                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                    <div class="p-6">
                        <div class="flex items-center mb-6">
                            <flux:icon.users class="h-6 w-6 text-indigo-600 mr-3" />
                            <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">
                                Ansvarlig og opprettet
                            </flux:heading>
                        </div>

                        <div class="space-y-4">
                            @if($contract->responsibleUser)
                                <div class="flex justify-between items-start">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                                        Ansvarlig
                                    </flux:text>
                                    <flux:text class="font-medium text-zinc-900 dark:text-white">
                                        {{ $contract->responsibleUser->name }}
                                    </flux:text>
                                </div>
                            @endif

                            <div class="flex justify-between items-start">
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    Opprettet av
                                </flux:text>
                                <flux:text class="font-medium text-zinc-900 dark:text-white">
                                    {{ $contract->creator->name }}
                                </flux:text>
                            </div>

                            <div class="flex justify-between items-start">
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    Opprettet dato
                                </flux:text>
                                <flux:text class="font-medium text-zinc-900 dark:text-white">
                                    {{ $contract->created_at->format('d.m.Y H:i') }}
                                </flux:text>
                            </div>

                            @if($contract->updated_at != $contract->created_at)
                                <div class="flex justify-between items-start">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                                        Sist oppdatert
                                    </flux:text>
                                    <flux:text class="font-medium text-zinc-900 dark:text-white">
                                        {{ $contract->updated_at->format('d.m.Y H:i') }}
                                    </flux:text>
                                </div>
                            @endif
                        </div>
                    </div>
                </flux:card>
            </div>

            @if($contract->description)
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm mt-6">
                    <div class="p-6">
                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-4">
                            Beskrivelse
                        </flux:heading>
                        <flux:text class="text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">
                            {{ $contract->description }}
                        </flux:text>
                    </div>
                </flux:card>
            @endif

            @if($contract->attachments)
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm mt-6">
                    <div class="p-6">
                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-4">
                            Vedlegg
                        </flux:heading>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach(json_decode($contract->attachments, true) as $attachment)
                                <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        @if(str_contains($attachment['mime_type'], 'image'))
                                            <flux:icon.photo class="w-8 h-8 text-indigo-600 flex-shrink-0" />
                                        @elseif(str_contains($attachment['mime_type'], 'pdf'))
                                            <flux:icon.document class="w-8 h-8 text-red-600 flex-shrink-0" />
                                        @else
                                            <flux:icon.document-text class="w-8 h-8 text-zinc-600 flex-shrink-0" />
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <flux:text class="font-medium text-zinc-900 dark:text-white truncate block">
                                                {{ $attachment['name'] }}
                                            </flux:text>
                                            <flux:text class="text-xs text-zinc-500">
                                                {{ number_format($attachment['size'] / 1024, 2) }} KB
                                            </flux:text>
                                        </div>
                                    </div>
                                    <a href="{{ Storage::url($attachment['path']) }}" target="_blank" download class="ml-3 flex-shrink-0 p-2 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors">
                                        <flux:icon.arrow-down-tray class="w-5 h-5" />
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </flux:card>
            @endif

            @if($contract->notes)
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm mt-6">
                    <div class="p-6">
                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-4">
                            Notater
                        </flux:heading>
                        <div class="prose dark:prose-invert max-w-none">
                            {!! $contract->notes !!}
                        </div>
                    </div>
                </flux:card>
            @endif

            <div class="mt-6">
                <flux:button href="{{ route('contracts.index') }}" variant="ghost">
                    <flux:icon.arrow-left class="w-5 h-5 mr-2" />
                    Tilbake til oversikt
                </flux:button>
            </div>
        </flux:main>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</x-layouts.app>
