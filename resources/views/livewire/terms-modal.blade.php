<div>
    <flux:modal name="terms-modal" variant="flyout" class="w-full max-w-2xl">
        <div class="flex flex-col h-full">

            <!-- Header -->
            <div class="flex items-start gap-4 pb-6 border-b border-zinc-200 dark:border-zinc-700">
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center shrink-0">
                    <flux:icon.document-text class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Vilkår for bruk</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Betingelser for bruk av Konrad Office.</p>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto py-6 space-y-6">

                <div class="space-y-1.5">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">1. Aksept av vilkår</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Ved å registrere deg for eller bruke Konrad Office aksepterer du disse bruksvilkårene.
                        Dersom du ikke aksepterer vilkårene, må du ikke bruke tjenesten.
                    </p>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">2. Beskrivelse av tjenesten</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed mb-3">
                        Konrad Office er et komplett forretningssystem som hjelper bedrifter med:
                    </p>
                    <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-4 space-y-2">
                        @foreach(['Salg, tilbud, ordrer og fakturaer', 'Regnskap med norsk kontoplan', 'Kontakter og prosjekter', 'Kontrakter og eiendeler'] as $item)
                        <div class="flex items-center gap-2.5 text-sm text-zinc-600 dark:text-zinc-400">
                            <flux:icon.check-circle class="w-4 h-4 text-emerald-500 shrink-0" />
                            {{ $item }}
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">3. Brukerregistrering</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Du må oppgi korrekte opplysninger ved registrering og er ansvarlig for å beskytte
                        ditt brukernavn og passord. Du må være minst 18 år for å bruke tjenesten.
                    </p>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">4. Personvern</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Vi behandler personopplysninger i henhold til vår personvernerklæring.
                        Vi deler ikke dine data med tredjeparter uten ditt samtykke.
                    </p>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">5. Tillatt og forbudt bruk</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Tjenesten skal kun brukes til lovlige forretningsformål. Det er forbudt å
                        bruke tjenesten til ulovlige aktiviteter, forsøke uautorisert tilgang,
                        eller distribuere skadelig programvare.
                    </p>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">6. Ansvarsfraskrivelse</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Vi garanterer ikke at tjenesten alltid vil være tilgjengelig eller feilfri.
                        Vårt ansvar er begrenset til det maksimale tillatt under norsk lov.
                    </p>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">7. Gjeldende lov</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Disse vilkårene er underlagt norsk lov. Tvister behandles ved norske domstoler.
                    </p>
                </div>

                <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/30 rounded-xl p-4">
                    <p class="text-sm text-indigo-800 dark:text-indigo-300">
                        Spørsmål om vilkårene? Ta kontakt på
                        <a href="mailto:post@konradoffice.no" class="font-medium hover:underline">post@konradoffice.no</a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="pt-5 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                <span class="text-xs text-zinc-400 dark:text-zinc-500">Sist oppdatert: {{ now()->format('d.m.Y') }}</span>
                <flux:button variant="primary" size="sm" x-on:click="$flux.modal('terms-modal').close()">Lukk</flux:button>
            </div>

        </div>
    </flux:modal>
</div>
