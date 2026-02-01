<div>
    <flux:modal name="privacy-modal" variant="flyout" class="w-full max-w-2xl">
        <div class="max-h-[80vh] overflow-y-auto">
            <div>
                <flux:heading size="lg">Personvern</flux:heading>
                <flux:text class="mt-2">Informasjon om hvordan Konrad Office behandler dine personopplysninger.</flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                {{-- Introduksjon --}}
                <div>
                    <flux:heading size="sm" class="mb-2">Introduksjon</flux:heading>
                    <flux:text size="sm">
                        Denne personvernerklaringen gir informasjon om hvordan og hvorfor Konrad Office
                        samler inn og behandler personopplysninger. Konrad Office er behandlingsansvarlig
                        for opplysninger som samles inn og behandles.
                    </flux:text>
                </div>

                {{-- Datainnsamling --}}
                <div>
                    <flux:heading size="sm" class="mb-2">Datainnsamling og bruk</flux:heading>
                    <flux:text size="sm" class="mb-2">
                        Vi samler inn og bruker dine personopplysninger for a levere tjenesten.
                        Vi selger ikke dine data til tredjeparter.
                    </flux:text>
                    <ul class="text-sm text-zinc-600 dark:text-zinc-400 space-y-1">
                        <li class="flex items-center gap-2">
                            <flux:icon.check class="w-4 h-4 text-green-600" />
                            For- og etternavn
                        </li>
                        <li class="flex items-center gap-2">
                            <flux:icon.check class="w-4 h-4 text-green-600" />
                            E-postadresse
                        </li>
                        <li class="flex items-center gap-2">
                            <flux:icon.check class="w-4 h-4 text-green-600" />
                            Bedriftsinformasjon
                        </li>
                        <li class="flex items-center gap-2">
                            <flux:icon.check class="w-4 h-4 text-green-600" />
                            Bruksstatistikk
                        </li>
                    </ul>
                </div>

                {{-- Dine rettigheter --}}
                <div>
                    <flux:heading size="sm" class="mb-2">Dine rettigheter</flux:heading>
                    <flux:text size="sm">
                        Du har rett til a be om innsyn, retting eller sletting av personopplysningene
                        vi behandler om deg. Du kan ogsa trekke tilbake ditt samtykke.
                    </flux:text>
                </div>

                {{-- Informasjonskapsler --}}
                <div>
                    <flux:heading size="sm" class="mb-2">Informasjonskapsler</flux:heading>
                    <flux:text size="sm">
                        Konrad Office bruker informasjonskapsler for a huske dine innstillinger
                        og forbedre brukeropplevelsen. Du kan velge a godta eller avvise disse
                        i nettleserens innstillinger.
                    </flux:text>
                </div>

                {{-- Databehandlere --}}
                <div>
                    <flux:heading size="sm" class="mb-2">Databehandlere</flux:heading>
                    <flux:text size="sm">
                        Vi deler ikke dine personopplysninger med tredjeparter uten ditt samtykke.
                    </flux:text>
                </div>

                {{-- Kontakt --}}
                <div class="bg-zinc-50 dark:bg-zinc-700/50 rounded-lg p-3">
                    <flux:text size="sm">
                        Spørsmål? Kontakt oss på
                        <a href="mailto:post@konradoffice.no" class="text-indigo-600 dark:text-indigo-400 hover:underline">post@konradoffice.no</a>
                    </flux:text>
                </div>
            </div>

            <flux:separator />

            <div class="flex justify-between items-center">
                <flux:text size="sm" class="text-zinc-500">Sist oppdatert: {{ now()->format('d.m.Y') }}</flux:text>
                <flux:button variant="primary" x-on:click="$flux.modal('privacy-modal').close()">Lukk</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
