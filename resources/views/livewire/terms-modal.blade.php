<div>
    <flux:modal name="terms-modal" variant="flyout" class="w-full max-w-2xl">
        <div class="max-h-[80vh] overflow-y-auto">
            <div>
                <flux:heading size="lg">Vilkar for bruk</flux:heading>
                <flux:text class="mt-2">Betingelser for bruk av Konrad Office.</flux:text>
            </div>

            <flux:separator />

            <div class="space-y-4">
                {{-- 1. Aksept --}}
                <div>
                    <flux:heading size="sm" class="mb-2">1. Aksept av vilkar</flux:heading>
                    <flux:text size="sm">
                        Ved a registrere deg for eller bruke Konrad Office aksepterer du disse bruksvilkarene.
                        Dersom du ikke aksepterer vilkarene, ma du ikke bruke tjenesten.
                    </flux:text>
                </div>

                {{-- 2. Tjenesten --}}
                <div>
                    <flux:heading size="sm" class="mb-2">2. Beskrivelse av tjenesten</flux:heading>
                    <flux:text size="sm" class="mb-2">
                        Konrad Office er et komplett forretningssystem som hjelper bedrifter med:
                    </flux:text>
                    <ul class="text-sm text-zinc-600 dark:text-zinc-400 space-y-1">
                        <li class="flex items-center gap-2">
                            <flux:icon.check class="w-4 h-4 text-green-600" />
                            Salg, tilbud, ordrer og fakturaer
                        </li>
                        <li class="flex items-center gap-2">
                            <flux:icon.check class="w-4 h-4 text-green-600" />
                            Regnskap med norsk kontoplan
                        </li>
                        <li class="flex items-center gap-2">
                            <flux:icon.check class="w-4 h-4 text-green-600" />
                            Kontakter og prosjekter
                        </li>
                        <li class="flex items-center gap-2">
                            <flux:icon.check class="w-4 h-4 text-green-600" />
                            Kontrakter og eiendeler
                        </li>
                    </ul>
                </div>

                {{-- 3. Bruker --}}
                <div>
                    <flux:heading size="sm" class="mb-2">3. Brukerregistrering</flux:heading>
                    <flux:text size="sm">
                        Du ma oppgi korrekte opplysninger ved registrering og er ansvarlig for a beskytte
                        ditt brukernavn og passord. Du ma vare minst 18 ar for a bruke tjenesten.
                    </flux:text>
                </div>

                {{-- 4. Personvern --}}
                <div>
                    <flux:heading size="sm" class="mb-2">4. Personvern</flux:heading>
                    <flux:text size="sm">
                        Vi behandler personopplysninger i henhold til var personvernerklaring.
                        Vi deler ikke dine data med tredjeparter uten ditt samtykke.
                    </flux:text>
                </div>

                {{-- 5. Bruk --}}
                <div>
                    <flux:heading size="sm" class="mb-2">5. Tillatt og forbudt bruk</flux:heading>
                    <flux:text size="sm">
                        Tjenesten skal kun brukes til lovlige forretningsformaal. Det er forbudt a
                        bruke tjenesten til ulovlige aktiviteter, forsoke uautorisert tilgang,
                        eller distribuere skadelig programvare.
                    </flux:text>
                </div>

                {{-- 6. Ansvar --}}
                <div>
                    <flux:heading size="sm" class="mb-2">6. Ansvarsfraskrivelse</flux:heading>
                    <flux:text size="sm">
                        Vi garanterer ikke at tjenesten alltid vil vare tilgjengelig eller feilfri.
                        Vart ansvar er begrenset til det maksimale tillatt under norsk lov.
                    </flux:text>
                </div>

                {{-- 7. Gjeldende lov --}}
                <div>
                    <flux:heading size="sm" class="mb-2">7. Gjeldende lov</flux:heading>
                    <flux:text size="sm">
                        Disse vilkarene er underlagt norsk lov. Tvister behandles ved norske domstoler.
                    </flux:text>
                </div>

                {{-- Kontakt --}}
                <div class="bg-zinc-50 dark:bg-zinc-700/50 rounded-lg p-3">
                    <flux:text size="sm">
                        Sporsmal? Kontakt oss pa
                        <a href="mailto:post@konradoffice.no" class="text-indigo-600 dark:text-indigo-400 hover:underline">post@konradoffice.no</a>
                    </flux:text>
                </div>
            </div>

            <flux:separator />

            <div class="flex justify-between items-center">
                <flux:text size="sm" class="text-zinc-500">Sist oppdatert: {{ now()->format('d.m.Y') }}</flux:text>
                <flux:button variant="primary" x-on:click="$flux.modal('terms-modal').close()">Lukk</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
