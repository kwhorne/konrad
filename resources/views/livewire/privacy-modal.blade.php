<div>
    <flux:modal name="privacy-modal" variant="flyout" class="w-full max-w-2xl">
        <div class="flex flex-col h-full">

            <!-- Header -->
            <div class="flex items-start gap-4 pb-6 border-b border-zinc-200 dark:border-zinc-700">
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center shrink-0">
                    <flux:icon.shield-check class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Personvernerklæring</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Informasjon om hvordan Konrad Office behandler dine personopplysninger.</p>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto py-6 space-y-6">

                <div class="space-y-1.5">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Introduksjon</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Denne personvernerklæringen gir informasjon om hvordan og hvorfor Konrad Office AS samler inn og behandler personopplysninger. Konrad Office AS er behandlingsansvarlig for opplysninger som samles inn og behandles.
                    </p>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Datainnsamling og bruk</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed mb-3">
                        Vi samler inn og bruker dine personopplysninger for å levere tjenesten. Vi selger ikke dine data til tredjeparter.
                    </p>
                    <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-4 space-y-2">
                        @foreach(['For- og etternavn', 'E-postadresse', 'Bedriftsinformasjon', 'Bruksstatistikk'] as $item)
                        <div class="flex items-center gap-2.5 text-sm text-zinc-600 dark:text-zinc-400">
                            <flux:icon.check-circle class="w-4 h-4 text-emerald-500 shrink-0" />
                            {{ $item }}
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Dine rettigheter</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Du har rett til å be om innsyn, retting eller sletting av personopplysningene vi behandler om deg. Du kan også trekke tilbake ditt samtykke når som helst ved å kontakte oss.
                    </p>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Informasjonskapsler</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Konrad Office bruker informasjonskapsler for å huske dine innstillinger og forbedre brukeropplevelsen. Du kan velge å godta eller avvise disse i nettleserens innstillinger.
                    </p>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Databehandlere</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Vi deler ikke dine personopplysninger med tredjeparter uten ditt samtykke, med unntak av det som er nødvendig for å levere tjenesten.
                    </p>
                </div>

                <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/30 rounded-xl p-4">
                    <p class="text-sm text-indigo-800 dark:text-indigo-300">
                        Spørsmål om personvern? Ta kontakt på
                        <a href="mailto:post@konradoffice.no" class="font-medium hover:underline">post@konradoffice.no</a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="pt-5 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                <span class="text-xs text-zinc-400 dark:text-zinc-500">Sist oppdatert: {{ now()->format('d.m.Y') }}</span>
                <flux:button variant="primary" size="sm" x-on:click="$flux.modal('privacy-modal').close()">Lukk</flux:button>
            </div>

        </div>
    </flux:modal>
</div>
