<x-layouts.app title="Vis eiendel">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="assets" />
        <x-app-header current="assets" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <flux:badge variant="{{ $asset->status_badge_color }}">
                            {{ $asset->status_label }}
                        </flux:badge>
                        <flux:badge variant="{{ $asset->condition_badge_color }}">
                            {{ $asset->condition_label }}
                        </flux:badge>
                        @if(!$asset->is_active)
                            <flux:badge variant="outline">Inaktiv</flux:badge>
                        @endif
                    </div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        {{ $asset->title }}
                    </flux:heading>
                    <flux:text class="mt-2 text-base text-zinc-600 dark:text-zinc-400">
                        {{ $asset->asset_number }}
                    </flux:text>
                </div>
                <div class="flex gap-3">
                    <flux:button href="{{ route('assets.edit', $asset) }}" variant="primary">
                        <flux:icon.pencil class="w-5 h-5 mr-2" />
                        Rediger
                    </flux:button>
                    <flux:modal.trigger name="delete-asset">
                        <flux:button variant="danger">
                            <flux:icon.trash class="w-5 h-5 mr-2" />
                            Slett
                        </flux:button>
                    </flux:modal.trigger>
                </div>

                <flux:modal name="delete-asset" class="min-w-[22rem]">
                    <form method="POST" action="{{ route('assets.destroy', $asset) }}">
                        @csrf
                        @method('DELETE')
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">Slett eiendel?</flux:heading>
                                <flux:text class="mt-2">
                                    <p>Du er i ferd med å slette <strong>{{ $asset->title }}</strong>.</p>
                                    <p>Denne handlingen kan ikke angres.</p>
                                </flux:text>
                            </div>
                            <div class="flex gap-2">
                                <flux:spacer />
                                <flux:modal.close>
                                    <flux:button variant="ghost">Avbryt</flux:button>
                                </flux:modal.close>
                                <flux:button type="submit" variant="danger">Slett eiendel</flux:button>
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                        <div class="p-6">
                            <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-4">
                                Detaljer
                            </flux:heading>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Serienummer</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $asset->serial_number ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Modell</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $asset->asset_model ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Lokasjon</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $asset->location ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Avdeling</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $asset->department ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Gruppe</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $asset->group ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Ansvarlig</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                                        {{ $asset->responsibleUser?->name ?? '-' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </flux:card>

                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                        <div class="p-6">
                            <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-4">
                                Kjøpsinformasjon
                            </flux:heading>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Kjøpspris</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white font-semibold">
                                        {{ $asset->formatted_price }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Kjøpsdato</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                                        {{ $asset->purchase_date?->format('d.m.Y') ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Leverandør</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $asset->supplier ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Produsent</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $asset->manufacturer ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Fakturanummer</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $asset->invoice_number ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Fakturadato</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                                        {{ $asset->invoice_date?->format('d.m.Y') ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Forsikringsnummer</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $asset->insurance_number ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </flux:card>

                    @if($asset->attachments)
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                            <div class="p-6">
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-4">
                                    Vedlegg
                                </flux:heading>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach(json_decode($asset->attachments, true) as $attachment)
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

                    @if($asset->description)
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                            <div class="p-6">
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-4">
                                    Beskrivelse
                                </flux:heading>
                                <flux:text class="text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">
                                    {{ $asset->description }}
                                </flux:text>
                            </div>
                        </flux:card>
                    @endif

                    @if($asset->notes)
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                            <div class="p-6">
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-4">
                                    Notater
                                </flux:heading>
                                <div class="prose dark:prose-invert max-w-none">
                                    {!! $asset->notes !!}
                                </div>
                            </div>
                        </flux:card>
                    @endif
                </div>

                <div class="lg:col-span-1 space-y-6">
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                        <div class="p-6">
                            <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-4">
                                Garanti
                            </flux:heading>
                            @if($asset->warranty_until)
                                <div class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Fra</dt>
                                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                                            {{ $asset->warranty_from?->format('d.m.Y') ?? '-' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Til</dt>
                                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                                            {{ $asset->warranty_until->format('d.m.Y') }}
                                        </dd>
                                    </div>
                                    <div class="pt-3 border-t border-zinc-200 dark:border-zinc-700">
                                        @if($asset->warranty_status === 'active')
                                            <flux:badge variant="success">Aktiv garanti</flux:badge>
                                        @elseif($asset->warranty_status === 'expiring_soon')
                                            <flux:badge variant="warning">Utgår snart</flux:badge>
                                        @else
                                            <flux:badge variant="outline">Utgått</flux:badge>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <flux:text class="text-zinc-500 dark:text-zinc-400">
                                    Ingen garantiinformasjon registrert
                                </flux:text>
                            @endif
                        </div>
                    </flux:card>

                    <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                        <div class="p-6">
                            <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-4">
                                Metadata
                            </flux:heading>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Opprettet av</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                                        {{ $asset->creator->name }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Opprettet</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                                        {{ $asset->created_at->format('d.m.Y H:i') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Sist oppdatert</dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                                        {{ $asset->updated_at->format('d.m.Y H:i') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </flux:card>
                </div>
            </div>

            <div class="mt-6">
                <flux:button href="{{ route('assets.index') }}" variant="ghost">
                    <flux:icon.arrow-left class="w-5 h-5 mr-2" />
                    Tilbake til oversikt
                </flux:button>
            </div>
        </flux:main>

    </div>
</x-layouts.app>
