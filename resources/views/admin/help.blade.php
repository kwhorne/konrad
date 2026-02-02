<x-layouts.app title="Admin - Dokumentasjon">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-admin-sidebar current="help" />
        <x-admin-header current="help" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.book-open class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Administratordokumentasjon
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Veiledning for systemadministratorer
                    </flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                {{-- Sidebar Navigation --}}
                <div class="lg:col-span-1">
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 sticky top-4">
                        <div class="p-4">
                            <flux:heading size="sm" class="text-zinc-900 dark:text-white mb-4">Innhold</flux:heading>
                            <nav class="space-y-1">
                                <a href="#brukere" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.users class="w-4 h-4" />
                                    Brukerhåndtering
                                </a>
                                <a href="#invitasjoner" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.envelope class="w-4 h-4" />
                                    Invitasjoner
                                </a>
                                <a href="#firma" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.building-office-2 class="w-4 h-4" />
                                    Firmainnstillinger
                                </a>
                                <a href="#moduler" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.squares-2x2 class="w-4 h-4" />
                                    Moduler
                                </a>
                                <a href="#lager" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.cube class="w-4 h-4" />
                                    Lager og innkjøp
                                </a>
                                <a href="#bankavstemming" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.scale class="w-4 h-4" />
                                    Bankavstemming
                                </a>
                                <a href="#stamdata" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.circle-stack class="w-4 h-4" />
                                    Stamdata
                                </a>
                                <a href="#kontoplan" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.table-cells class="w-4 h-4" />
                                    Kontoplan
                                </a>
                                <a href="#årsoppgjør" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.document-check class="w-4 h-4" />
                                    Årsoppgjør
                                </a>
                                <a href="#altinn-admin" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.paper-airplane class="w-4 h-4" />
                                    Altinn-oppsett
                                </a>
                                <a href="#system" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.cog-6-tooth class="w-4 h-4" />
                                    System
                                </a>
                                <a href="#koer" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.queue-list class="w-4 h-4" />
                                    Køer og jobber
                                </a>
                                <a href="#scheduler" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.clock class="w-4 h-4" />
                                    Planlagte oppgaver
                                </a>
                                <a href="#miljovariabler" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.variable class="w-4 h-4" />
                                    Miljøvariabler
                                </a>
                                <a href="#sikkerhet" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.shield-check class="w-4 h-4" />
                                    Sikkerhet
                                </a>
                            </nav>
                        </div>
                    </flux:card>
                </div>

                {{-- Main Content --}}
                <div class="lg:col-span-3 space-y-8">
                    {{-- Brukerhåndtering --}}
                    <flux:card id="brukere" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.users class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Brukerhåndtering</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Som administrator har du full kontroll over alle brukere i systemet.</p>

                                <h4>Brukertyper</h4>
                                <ul>
                                    <li><strong>Administrator</strong> - Full tilgang til alle funksjoner og innstillinger</li>
                                    <li><strong>Bruker</strong> - Tilgang til daglige funksjoner, men ikke admin-panelet</li>
                                </ul>

                                <h4>Opprette ny bruker</h4>
                                <p>Nye brukere opprettes via invitasjonssystemet:</p>
                                <ol>
                                    <li>Gå til <strong>Administrasjon > Brukere</strong></li>
                                    <li>Klikk <strong>Inviter bruker</strong></li>
                                    <li>Fyll inn e-postadresse</li>
                                    <li>Velg om brukeren skal være administrator</li>
                                    <li>Klikk <strong>Send invitasjon</strong></li>
                                </ol>

                                <h4>Administrere brukere</h4>
                                <ul>
                                    <li><strong>Deaktiver</strong> - Brukeren kan ikke logge inn, men data beholdes</li>
                                    <li><strong>Aktiver</strong> - Gjenopprett tilgang for deaktivert bruker</li>
                                    <li><strong>Gjør til admin</strong> - Gi administratorrettigheter</li>
                                    <li><strong>Fjern admin</strong> - Fjern administratorrettigheter</li>
                                </ul>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Viktig</flux:callout.heading>
                                    <flux:callout.text>Det må alltid være minst én aktiv administrator i systemet.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Invitasjoner --}}
                    <flux:card id="invitasjoner" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.envelope class="w-5 h-5 text-green-600 dark:text-green-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Invitasjoner</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Konrad Office bruker et invitasjonsbasert registreringssystem. Dette gir deg full kontroll over hvem som har tilgang.</p>

                                <h4>Slik fungerer det</h4>
                                <ol>
                                    <li>Administrator sender invitasjon via e-post</li>
                                    <li>Mottaker får en unik lenke som er gyldig i 7 dager</li>
                                    <li>Ved første besøk oppretter mottaker passord</li>
                                    <li>Brukeren er nå aktiv i systemet</li>
                                </ol>

                                <h4>Invitasjonsstatus</h4>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="yellow">Ventende</flux:badge>
                                            <span>Invitasjon sendt, ikke akseptert</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="green">Akseptert</flux:badge>
                                            <span>Bruker har opprettet konto</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="red">Utløpt</flux:badge>
                                            <span>Invitasjonen er ikke lenger gyldig</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>Send invitasjon på nytt</h4>
                                <p>Hvis en invitasjon har utløpt eller brukeren ikke mottok e-posten:</p>
                                <ol>
                                    <li>Finn invitasjonen i listen</li>
                                    <li>Klikk på menyknappen</li>
                                    <li>Velg <strong>Send på nytt</strong></li>
                                </ol>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Firmainnstillinger --}}
                    <flux:card id="firma" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.building-office-2 class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Firmainnstillinger</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Konfigurer informasjonen som vises på dokumenter og i systemet.</p>

                                <h4>Firmainformasjon</h4>
                                <ul>
                                    <li><strong>Firmanavn</strong> - Vises på alle dokumenter</li>
                                    <li><strong>Organisasjonsnummer</strong> - Vises i bunntekst</li>
                                    <li><strong>Adresse</strong> - Brukes som avsenderadresse</li>
                                    <li><strong>Telefon og e-post</strong> - Kontaktinformasjon</li>
                                </ul>

                                <h4>Bankopplysninger</h4>
                                <ul>
                                    <li><strong>Kontonummer</strong> - Vises på fakturaer for betaling</li>
                                    <li><strong>IBAN/SWIFT</strong> - For utenlandske betalinger</li>
                                </ul>

                                <h4>Logo</h4>
                                <p>Last opp firmalogo som vises på:</p>
                                <ul>
                                    <li>Tilbud og ordrebekreftelser</li>
                                    <li>Fakturaer og kreditnotaer</li>
                                    <li>Rapporter (valgfritt)</li>
                                </ul>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Tips</flux:callout.heading>
                                    <flux:callout.text>Bruk en logo med transparent bakgrunn (PNG) for best resultat på dokumenter.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Moduler --}}
                    <flux:card id="moduler" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.squares-2x2 class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Moduler</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Konrad Office er modulbasert. Du kan aktivere kun de modulene bedriften trenger.</p>

                                <h4>Tilgjengelige moduler</h4>
                                <div class="not-prose my-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="font-medium text-zinc-900 dark:text-white">Kontaktregister</div>
                                            <div class="text-sm text-zinc-500">Kunder og leverandører</div>
                                        </div>
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="font-medium text-zinc-900 dark:text-white">Vareregister</div>
                                            <div class="text-sm text-zinc-500">Produkter og tjenester</div>
                                        </div>
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="font-medium text-zinc-900 dark:text-white">Prosjekter</div>
                                            <div class="text-sm text-zinc-500">Prosjektstyring og budsjett</div>
                                        </div>
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="font-medium text-zinc-900 dark:text-white">Arbeidsordrer</div>
                                            <div class="text-sm text-zinc-500">Timer og materialbruk</div>
                                        </div>
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="font-medium text-zinc-900 dark:text-white">Salg</div>
                                            <div class="text-sm text-zinc-500">Tilbud, ordrer, fakturaer</div>
                                        </div>
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="font-medium text-zinc-900 dark:text-white">Kontrakter</div>
                                            <div class="text-sm text-zinc-500">Kontraktsregister</div>
                                        </div>
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="font-medium text-zinc-900 dark:text-white">Eiendeler</div>
                                            <div class="text-sm text-zinc-500">Eiendelsregister</div>
                                        </div>
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="font-medium text-zinc-900 dark:text-white">Lager</div>
                                            <div class="text-sm text-zinc-500">Lagerstyring og innkjøp</div>
                                        </div>
                                    </div>
                                </div>

                                <h4>Aktivere/deaktivere moduler</h4>
                                <p>Kontakt support for åendre hvilke moduler som er aktive for din bedrift.</p>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Lager og innkjøp --}}
                    <flux:card id="lager" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.cube class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Lager og innkjøp</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Lagermodulen gir full lagerstyring med transaksjonsbasert sporing av alle varebevegelser.</p>

                                <h4>Aktivere lagermodulen</h4>
                                <p>Sett <code>INVENTORY_ENABLED=true</code> i <code>.env</code>-filen for åaktivere modulen. Etter aktivering vises <strong>Lager</strong> i sidemenyen.</p>

                                <h4>Lageroversikt</h4>
                                <p>Dashbordet viser:</p>
                                <ul>
                                    <li><strong>Lagervarer</strong> - Antall produkter merket som lagerført</li>
                                    <li><strong>Total verdi</strong> - Samlet lagerverdi basert på vektet gjennomsnittskost</li>
                                    <li><strong>Åpne bestillinger</strong> - Innkjøpsordrer som ikke er fullstendig mottatt</li>
                                    <li><strong>Under bestillingspunkt</strong> - Varer som må bestilles</li>
                                </ul>

                                <h4>Lagerlokasjoner</h4>
                                <p>Opprett og administrer lagerlokasjoner med hierarkisk struktur:</p>
                                <ul>
                                    <li><strong>Lager</strong> - Hovedlokasjoner (f.eks. Hovedlager, Servicebil)</li>
                                    <li><strong>Sone</strong> - Områder innenfor et lager</li>
                                    <li><strong>Hylle</strong> - Spesifikke plasser</li>
                                </ul>

                                <h4>Lagerforte produkter</h4>
                                <p>For å aktivere lagerforing på et produkt:</p>
                                <ol>
                                    <li>Gå til produktet i vareregisteret</li>
                                    <li>Huk av for <strong>Lagerfort</strong></li>
                                    <li>Angi eventuelt bestillingspunkt og bestillingsantall</li>
                                </ol>

                                <h4>Innkjøpsordrer</h4>
                                <p>Opprett bestillinger til leverandører:</p>
                                <ol>
                                    <li>Gå til <strong>Lager > Innkjøpsordrer</strong></li>
                                    <li>Klikk <strong>Ny innkjøpsordre</strong></li>
                                    <li>Velg leverandør og mottakslager</li>
                                    <li>Legg til produkter og mengder</li>
                                    <li>Send ordren til leverandør</li>
                                </ol>

                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="font-medium text-zinc-900 dark:text-white mb-2">Ordrestatuser</div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="zinc">Utkast</flux:badge>
                                            <span>Ordren er under utarbeidelse</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="blue">Sendt</flux:badge>
                                            <span>Ordren er sendt til leverandør</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="amber">Delvis mottatt</flux:badge>
                                            <span>Noe av ordren er mottatt</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="green">Mottatt</flux:badge>
                                            <span>Hele ordren er mottatt</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>Varemottak</h4>
                                <p>Registrer mottatte varer:</p>
                                <ol>
                                    <li>Gå til <strong>Lager > Varemottak</strong></li>
                                    <li>Velg innkjøpsordre eller opprett frittstående mottak</li>
                                    <li>Angi mottatt antall for hver linje</li>
                                    <li>Klikk <strong>Bokfør</strong> for åoppdatere lagerbeholdningen</li>
                                </ol>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Vektet gjennomsnittskost</flux:callout.heading>
                                    <flux:callout.text>Systemet beregner automatisk vektet gjennomsnittskost når varer mottas. Denne kostnaden brukes ved varekostnadsberegning ved salg.</flux:callout.text>
                                </flux:callout>

                                <h4>Varetelling</h4>
                                <p>Årlig varetelling er påkrevd for ådokumentere lagerbeholdningen. Slik gjennomfører du en telling:</p>
                                <ol>
                                    <li>Gå til <strong>Lager > Varetelling</strong></li>
                                    <li>Klikk <strong>Ny varetelling</strong> og velg lokasjon</li>
                                    <li>Start tellingen - alle lagerførte produkter lastes inn med forventet beholdning</li>
                                    <li>Registrer talt antall for hvert produkt</li>
                                    <li>Fullfør tellingen når alle produkter er talt</li>
                                    <li>Bokfør for åopprette lagerjusteringer for alle avvik</li>
                                </ol>

                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="font-medium text-zinc-900 dark:text-white mb-2">Tellestatuser</div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="zinc">Utkast</flux:badge>
                                            <span>Tellingen er opprettet, men ikke startet</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="blue">Pågår</flux:badge>
                                            <span>Telling er i gang</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="amber">Fullført</flux:badge>
                                            <span>Alle produkter er talt, klar for bokføring</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="green">Bokført</flux:badge>
                                            <span>Lagerjusteringer er opprettet</span>
                                        </div>
                                    </div>
                                </div>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Lovkrav</flux:callout.heading>
                                    <flux:callout.text>Bokføringsloven krever årlig varetelling ved regnskapsårets slutt. Tellingen må dokumenteres og oppbevares.</flux:callout.text>
                                </flux:callout>

                                <h4>Lagerjusteringer</h4>
                                <p>For å justere beholdning manuelt (f.eks. ved opptelling eller svinn):</p>
                                <ol>
                                    <li>Gå til <strong>Lager > Justering</strong></li>
                                    <li>Velg produkt og lokasjon</li>
                                    <li>Angi ny beholdning eller differanse</li>
                                    <li>Oppgi årsak for justeringen</li>
                                </ol>

                                <h4>Automatisk lagerhåndtering</h4>
                                <p>Systemet håndterer lager automatisk i salgsflyt:</p>
                                <ul>
                                    <li><strong>Reservering</strong> - Varer reserveres når ordre bekreftes</li>
                                    <li><strong>Uttak</strong> - Beholdning reduseres når ordre faktureres</li>
                                    <li><strong>Varekostnad</strong> - COGS bokføres basert på vektet gjennomsnittskost</li>
                                </ul>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Negativ beholdning</flux:callout.heading>
                                    <flux:callout.text>Som standard tillater ikke systemet negativ lagerbeholdning. Dette kan endres i lagerinnstillingene hvis nødvendig.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Bankavstemming --}}
                    <flux:card id="bankavstemming" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.scale class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Bankavstemming</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Bankavstemmingsmodulen lar deg importere kontoutskrifter fra banken og matche transaksjoner mot bilag i regnskapet.</p>

                                <h4>Støttede banker</h4>
                                <div class="not-prose my-4">
                                    <div class="flex flex-wrap gap-2">
                                        <flux:badge color="blue">DNB</flux:badge>
                                        <flux:badge color="blue">Nordea</flux:badge>
                                        <flux:badge color="blue">SpareBank 1</flux:badge>
                                        <flux:badge color="blue">Sbanken</flux:badge>
                                    </div>
                                </div>

                                <h4>Slik gjør du en avstemming</h4>
                                <ol>
                                    <li>Gå til <strong>Økonomi → Bankavstemming</strong></li>
                                    <li>Velg bankkonto fra kontoplanen</li>
                                    <li>Last opp CSV-fil fra nettbanken</li>
                                    <li>Klikk <strong>Start auto-matching</strong></li>
                                    <li>Gjennomgå matchede og umatchede transaksjoner</li>
                                    <li>Fullfør avstemmingen</li>
                                </ol>

                                <h4>Automatisk matching</h4>
                                <p>Systemet forsøker å matche banktransaksjoner automatisk basert på:</p>
                                <ul>
                                    <li><strong>KID-referanse</strong> - Norske KID-nummer på innbetalinger</li>
                                    <li><strong>Beløp og dato</strong> - Matching mot åpne fakturaer og leverandørfakturaer</li>
                                    <li><strong>Beskrivelse</strong> - Gjenkjenning av kontaktinformasjon</li>
                                </ul>

                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="font-medium text-zinc-900 dark:text-white mb-2">Transaksjonsstatus</div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="zinc">Umatchet</flux:badge>
                                            <span>Ingen match funnet - krever manuell behandling</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="blue">Auto-matchet</flux:badge>
                                            <span>Systemet har funnet en sannsynlig match</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="green">Bekreftet</flux:badge>
                                            <span>Match er bekreftet av bruker</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="amber">Ignorert</flux:badge>
                                            <span>Transaksjonen krever ikke bilag</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>Manuell matching</h4>
                                <p>For umatchede transaksjoner kan du:</p>
                                <ul>
                                    <li><strong>Søk etter match</strong> - Finn faktura eller leverandørfaktura manuelt</li>
                                    <li><strong>Opprett kladd-bilag</strong> - Lag et nytt bilag for transaksjonen</li>
                                    <li><strong>Ignorer</strong> - Marker transaksjonen som håndtert uten bilag</li>
                                </ul>

                                <h4>Kladd-bilag</h4>
                                <p>Når du oppretter et kladd-bilag fyller systemet automatisk inn:</p>
                                <ul>
                                    <li>Beløp fra banktransaksjonen</li>
                                    <li>Dato fra transaksjonen</li>
                                    <li>Beskrivelse fra banken</li>
                                </ul>
                                <p>Du velger selv konto og eventuell kontakt.</p>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Tips</flux:callout.heading>
                                    <flux:callout.text>Eksporter CSV fra nettbanken din for perioden du vil avstemme. De fleste banker har en eksport-funksjon under kontoutskrift eller transaksjoner.</flux:callout.text>
                                </flux:callout>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Lovkrav</flux:callout.heading>
                                    <flux:callout.text>Bokføringsloven krever regelmessig avstemming av bankkonti. Alle transaksjoner på bankkontoen skal kunne dokumenteres med bilag.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Stamdata --}}
                    <flux:card id="stamdata" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.circle-stack class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Stamdata</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Stamdata er grunnleggende oppsett som brukes på tvers av systemet.</p>

                                <h4>Varegrupper og -typer</h4>
                                <ul>
                                    <li><strong>Varegrupper</strong> - Overordnet kategorisering av produkter</li>
                                    <li><strong>Varetyper</strong> - Underkategorier med standard MVA-sats</li>
                                </ul>

                                <h4>MVA-satser</h4>
                                <p>Standard norske MVA-satser er forhåndskonfigurert:</p>
                                <ul>
                                    <li>25% - Standard sats</li>
                                    <li>15% - Næringsmidler</li>
                                    <li>12% - Transport, kultur, kino</li>
                                    <li>0% - Fritatt/utenfor avgiftsområdet</li>
                                </ul>

                                <h4>Enheter</h4>
                                <p>Målenheter for produkter og tjenester:</p>
                                <ul>
                                    <li>stk (stykk)</li>
                                    <li>timer</li>
                                    <li>kg, liter, meter, m2, m3</li>
                                    <li>Egendefinerte enheter</li>
                                </ul>

                                <h4>Aktivitetstyper</h4>
                                <p>Typer for kontaktaktiviteter:</p>
                                <ul>
                                    <li>Telefonsamtale</li>
                                    <li>Møte</li>
                                    <li>E-post</li>
                                    <li>Notat</li>
                                </ul>

                                <h4>Prosjekttyper og -statuser</h4>
                                <p>Tilpass prosjektmodulen til din arbeidsflyt.</p>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Kontoplan --}}
                    <flux:card id="kontoplan" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.table-cells class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Kontoplan</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Kontoplanen er basert på norsk standard NS 4102 og er ferdig konfigurert.</p>

                                <h4>Kontoplanen er organisert i klasser</h4>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-medium">Klasse 1</span>
                                            <span>Eiendeler (1000-1999)</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-medium">Klasse 2</span>
                                            <span>Egenkapital og gjeld (2000-2999)</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-medium">Klasse 3</span>
                                            <span>Inntekter (3000-3999)</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-medium">Klasse 4</span>
                                            <span>Varekostnad (4000-4999)</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-medium">Klasse 5</span>
                                            <span>Lønn og personal (5000-5999)</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-medium">Klasse 6-7</span>
                                            <span>Driftskostnader (6000-7999)</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-medium">Klasse 8</span>
                                            <span>Finansposter (8000-8999)</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>Administrere kontoplan</h4>
                                <ul>
                                    <li><strong>Aktiver/deaktiver</strong> - Skjul konti som ikke brukes</li>
                                    <li><strong>Rediger</strong> - Endre kontonavn</li>
                                    <li><strong>Opprett ny</strong> - Legg til egne konti</li>
                                </ul>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>OBS</flux:callout.heading>
                                    <flux:callout.text>Ikke slett konti som har posteringer. Deaktiver dem i stedet.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Årsoppgjør --}}
                    <flux:card id="årsoppgjør" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.document-check class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Årsoppgjør</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Årsoppgjørsmodulen inneholder verktøy for åutarbeide obligatoriske rapporter til norske myndigheter. Her konfigurerer du oppsett og standardverdier.</p>

                                <h4>Moduloversikt</h4>
                                <div class="not-prose my-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                                            <div class="font-medium text-indigo-800 dark:text-indigo-200">Aksjonærregister</div>
                                            <div class="text-sm text-indigo-600 dark:text-indigo-400">Aksjonærer, transaksjoner, utbytte</div>
                                        </div>
                                        <div class="p-3 bg-rose-50 dark:bg-rose-900/20 rounded-lg border border-rose-200 dark:border-rose-800">
                                            <div class="font-medium text-rose-800 dark:text-rose-200">Skatt</div>
                                            <div class="text-sm text-rose-600 dark:text-rose-400">Justeringer, utsatt skatt, saldoavskrivning</div>
                                        </div>
                                        <div class="p-3 bg-sky-50 dark:bg-sky-900/20 rounded-lg border border-sky-200 dark:border-sky-800">
                                            <div class="font-medium text-sky-800 dark:text-sky-200">Årsregnskap</div>
                                            <div class="text-sm text-sky-600 dark:text-sky-400">Noter, kontantstrøm, XBRL</div>
                                        </div>
                                        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                            <div class="font-medium text-emerald-800 dark:text-emerald-200">Altinn-dashboard</div>
                                            <div class="text-sm text-emerald-600 dark:text-emerald-400">Innsendinger og frister</div>
                                        </div>
                                    </div>
                                </div>

                                <h4>Aksjonærregister - Administrasjon</h4>
                                <p>Konfigurasjon av aksjeklasser og standardverdier:</p>
                                <ul>
                                    <li><strong>Aksjeklasser</strong> - Opprett og administrer A-, B-aksjer etc.</li>
                                    <li><strong>Transaksjonstyper</strong> - Stiftelse, emisjon, overdragelse, splitt, innløsning</li>
                                    <li><strong>Ervervsmåter</strong> - Stiftelse, kjøp, arv, gave, fusjon</li>
                                    <li><strong>Standardverdier</strong> - Pålydende per aksje, valuta</li>
                                </ul>

                                <h4>Skatt - Administrasjon</h4>
                                <p>Oppsett av skattemessige parametere:</p>
                                <ul>
                                    <li><strong>Skattesats</strong> - Gjeldende sats (22%)</li>
                                    <li><strong>Saldogrupper</strong> - Avskrivningssatser per gruppe (a-j)</li>
                                    <li><strong>Justeringskategorier</strong> - Permanente og midlertidige forskjeller</li>
                                    <li><strong>Standardkonti</strong> - Konti for betalbar og utsatt skatt</li>
                                </ul>

                                <div class="not-prose my-4">
                                    <div class="space-y-1 text-sm">
                                        <div class="font-medium text-zinc-900 dark:text-white mb-2">Saldoavskrivningsgrupper</div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe a - Kontormaskiner</span>
                                            <span class="font-mono">30%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe b - Goodwill</span>
                                            <span class="font-mono">20%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe c - Varebiler</span>
                                            <span class="font-mono">24%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe d - Maskiner</span>
                                            <span class="font-mono">20%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe h - Bygg</span>
                                            <span class="font-mono">4%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe i - Forretningsbygg</span>
                                            <span class="font-mono">2%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe j - Tekniske inst.</span>
                                            <span class="font-mono">10%</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>Årsregnskap - Administrasjon</h4>
                                <p>Konfigurasjon av årsregnskap:</p>
                                <ul>
                                    <li><strong>Notemaler</strong> - Standardtekster for noter</li>
                                    <li><strong>Regnskapsprinsipper</strong> - Mal for regnskapsprinsipp-note</li>
                                    <li><strong>Størrelsesgrenser</strong> - Terskelverdier for små/mellomstore/store</li>
                                    <li><strong>Revisorinformasjon</strong> - Standardverdier for revisor</li>
                                </ul>

                                <h4>Størrelseskategorier</h4>
                                <p>Selskaper klassifiseres basert på to av tre kriterier:</p>
                                <div class="not-prose my-4">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead>
                                                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                                    <th class="text-left p-2">Kategori</th>
                                                    <th class="text-right p-2">Salgsinntekt</th>
                                                    <th class="text-right p-2">Balansesum</th>
                                                    <th class="text-right p-2">Ansatte</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                                    <td class="p-2 font-medium">Små</td>
                                                    <td class="p-2 text-right">< 70 MNOK</td>
                                                    <td class="p-2 text-right">< 35 MNOK</td>
                                                    <td class="p-2 text-right">< 50</td>
                                                </tr>
                                                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                                    <td class="p-2 font-medium">Mellomstore</td>
                                                    <td class="p-2 text-right">< 350 MNOK</td>
                                                    <td class="p-2 text-right">< 175 MNOK</td>
                                                    <td class="p-2 text-right">< 250</td>
                                                </tr>
                                                <tr>
                                                    <td class="p-2 font-medium">Store</td>
                                                    <td class="p-2 text-right">>= 350 MNOK</td>
                                                    <td class="p-2 text-right">>= 175 MNOK</td>
                                                    <td class="p-2 text-right">>= 250</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Automatisk klassifisering</flux:callout.heading>
                                    <flux:callout.text>Systemet beregner automatisk selskapsstørrelse basert på regnskapstall og justerer kravene til årsregnskapet deretter.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Altinn-oppsett --}}
                    <flux:card id="altinn-admin" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.paper-airplane class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Altinn-oppsett</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Konfigurer Altinn-integrasjonen for elektronisk innsending av årsoppgjørsdata til norske myndigheter.</p>

                                <h4>Obligatoriske innsendinger</h4>
                                <div class="not-prose my-4">
                                    <div class="space-y-3">
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <div class="font-medium text-zinc-900 dark:text-white">Aksjonærregisteroppgaven (RF-1086)</div>
                                                    <div class="text-sm text-zinc-500">Til Skatteetaten</div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="font-medium text-zinc-900 dark:text-white">31. januar</div>
                                                    <div class="text-sm text-zinc-500">Format: XML</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <div class="font-medium text-zinc-900 dark:text-white">Skattemelding (RF-1028)</div>
                                                    <div class="text-sm text-zinc-500">Til Skatteetaten</div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="font-medium text-zinc-900 dark:text-white">31. mai</div>
                                                    <div class="text-sm text-zinc-500">Format: XML</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <div class="font-medium text-zinc-900 dark:text-white">Årsregnskap</div>
                                                    <div class="text-sm text-zinc-500">Til Regnskapsregisteret</div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="font-medium text-zinc-900 dark:text-white">31. juli</div>
                                                    <div class="text-sm text-zinc-500">Format: XBRL</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h4>Maskinporten-autentisering</h4>
                                <p>For automatisk innsending til Altinn kreves Maskinporten-integrasjon:</p>
                                <ol>
                                    <li>Bestill virksomhetssertifikat fra godkjent utsteder</li>
                                    <li>Registrer klienten i Maskinporten</li>
                                    <li>Last opp sertifikat i Konrad</li>
                                    <li>Konfigurer scopes for de ulike tjenestene</li>
                                </ol>

                                <h4>Sertifikathåndtering</h4>
                                <p>Administrer virksomhetssertifikater:</p>
                                <ul>
                                    <li><strong>Last opp</strong> - Last opp nytt sertifikat (.p12 eller .pfx)</li>
                                    <li><strong>Gyldighet</strong> - Se utløpsdato og status</li>
                                    <li><strong>Forny</strong> - Erstatt utløpende sertifikat</li>
                                    <li><strong>Slett</strong> - Fjern gammelt sertifikat</li>
                                </ul>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Sertifikatgyldighet</flux:callout.heading>
                                    <flux:callout.text>Sertifikater utløper typisk etter 2-3 år. Systemet varsler 30 dager for utløp. Sørg for for ånye i god tid.</flux:callout.text>
                                </flux:callout>

                                <h4>Miljøer</h4>
                                <p>Altinn har to miljøer:</p>
                                <div class="not-prose my-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                            <div class="font-medium text-yellow-800 dark:text-yellow-200">Test (TT02)</div>
                                            <div class="text-sm text-yellow-600 dark:text-yellow-400">For utvikling og testing. Innsendinger behandles ikke.</div>
                                        </div>
                                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                            <div class="font-medium text-green-800 dark:text-green-200">Produksjon</div>
                                            <div class="text-sm text-green-600 dark:text-green-400">For faktiske innsendinger til myndigheter.</div>
                                        </div>
                                    </div>
                                </div>

                                <h4>Konfigurasjon</h4>
                                <p>Innstillinger som må konfigureres:</p>
                                <ul>
                                    <li><strong>Miljø</strong> - Velg test eller produksjon</li>
                                    <li><strong>Klient-ID</strong> - Fra Maskinporten-registrering</li>
                                    <li><strong>Sertifikat</strong> - Virksomhetssertifikat</li>
                                    <li><strong>Sertifikatpassord</strong> - Passord for sertifikatet (kryptert)</li>
                                </ul>

                                <h4>Fristpåminnelser</h4>
                                <p>Konfigurer e-postvarsler for kommende frister:</p>
                                <ul>
                                    <li><strong>30 dager for</strong> - Første påminnelse</li>
                                    <li><strong>14 dager for</strong> - Oppfølgingspåminnelse</li>
                                    <li><strong>7 dager for</strong> - Hastevarsel</li>
                                    <li><strong>1 dag for</strong> - Kritisk frist</li>
                                </ul>

                                <h4>Mottakere av varsler</h4>
                                <p>Velg hvem som skal motta fristpåminnelser:</p>
                                <ul>
                                    <li>Alle administratorer</li>
                                    <li>Spesifikke brukere</li>
                                    <li>Eksterne e-postadresser (f.eks. regnskapsfører)</li>
                                </ul>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Testmodus</flux:callout.heading>
                                    <flux:callout.text>Bruk alltid testmiljøet (TT02) for åverifisere at innsendinger fungerer før du bytter til produksjon. Testinnsendinger behandles ikke av myndighetene.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- System --}}
                    <flux:card id="system" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.cog-6-tooth class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">System</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Systemsiden gir deg oversikt over teknisk informasjon, versjoner og vedlikeholdsverktøy.</p>

                                <h4>Systeminformasjon</h4>
                                <ul>
                                    <li><strong>Konrad Office-versjon</strong> - Nåværende applikasjonsversjon</li>
                                    <li><strong>Laravel-versjon</strong> - Rammeverkets versjon</li>
                                    <li><strong>PHP-versjon</strong> - Serverens PHP-versjon</li>
                                    <li><strong>Miljø</strong> - production, staging eller development</li>
                                    <li><strong>Debug-modus</strong> - Viser detaljerte feilmeldinger (kun development)</li>
                                </ul>

                                <h4>Cache-håndtering</h4>
                                <p>Systemet bruker flere cache-lag for ytelse:</p>
                                <ul>
                                    <li><strong>Applikasjonscache</strong> - Mellomlagring av beregninger og spørringer</li>
                                    <li><strong>Konfigurasjonscache</strong> - Kompilerte innstillinger</li>
                                    <li><strong>Rutecache</strong> - Kompilerte URL-ruter</li>
                                    <li><strong>Visningscache</strong> - Kompilerte Blade-maler</li>
                                </ul>

                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="font-medium text-zinc-900 dark:text-white mb-2">Cache-kommandoer</div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan cache:clear
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan config:clear
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan route:clear
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan view:clear
                                        </div>
                                    </div>
                                </div>

                                <h4>Database</h4>
                                <ul>
                                    <li><strong>Tilkobling</strong> - MySQL/MariaDB eller PostgreSQL</li>
                                    <li><strong>Migrasjoner</strong> - Databaseskjema-versjoner</li>
                                    <li><strong>Seeders</strong> - Grunndata og testdata</li>
                                </ul>

                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="font-medium text-zinc-900 dark:text-white mb-2">Database-kommandoer</div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan migrate --force
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan db:seed --class=ProductionSeeder
                                        </div>
                                    </div>
                                </div>

                                <h4>Vedlikeholdsmodus</h4>
                                <p>Aktiver vedlikeholdsmodus når du skal gjøre større endringer:</p>
                                <ul>
                                    <li>Brukere ser en "under vedlikehold"-melding</li>
                                    <li>Administratorer kan fortsatt logge inn med secret-token</li>
                                    <li>Deaktiver når du er ferdig</li>
                                </ul>

                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="font-medium text-zinc-900 dark:text-white mb-2">Vedlikeholdskommandoer</div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan down --secret="token123"
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan up
                                        </div>
                                    </div>
                                </div>

                                <h4>Logging</h4>
                                <p>Logger finnes i <code>storage/logs/</code>:</p>
                                <ul>
                                    <li><strong>laravel.log</strong> - Hovedlogg for applikasjonsfeil</li>
                                    <li><strong>worker.log</strong> - Logger fra køprosesser</li>
                                    <li><strong>scheduler.log</strong> - Logger fra planlagte oppgaver</li>
                                </ul>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Loggrotasjon</flux:callout.heading>
                                    <flux:callout.text>Logger roteres automatisk daglig og beholdes i 14 dager. Gamle logger slettes automatisk for å spare diskplass.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Køer og jobber --}}
                    <flux:card id="koer" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.queue-list class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Køer og bakgrunnsjobber</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Køsystemet håndterer tidkrevende oppgaver i bakgrunnen uten å blokkere brukergrensesnittet.</p>

                                <h4>Hva prosesseres i køer?</h4>
                                <ul>
                                    <li><strong>E-post</strong> - Utsending av fakturaer, påminnelser og varsler</li>
                                    <li><strong>PDF-generering</strong> - Oppretting av fakturaer og rapporter</li>
                                    <li><strong>Dokumentparsing</strong> - OCR og AI-analyse av bilag</li>
                                    <li><strong>Altinn-innsending</strong> - Elektronisk innsending til myndigheter</li>
                                    <li><strong>Import/eksport</strong> - Store data-operasjoner</li>
                                </ul>

                                <h4>Køkonfigurasjon</h4>
                                <p>Køer konfigureres via miljøvariabler:</p>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            QUEUE_CONNECTION=database
                                        </div>
                                    </div>
                                </div>

                                <p>Tilgjengelige drivere:</p>
                                <ul>
                                    <li><strong>database</strong> - Bruker databasetabellen <code>jobs</code> (anbefalt)</li>
                                    <li><strong>redis</strong> - Bruker Redis for raskere prosessering</li>
                                    <li><strong>sync</strong> - Kjører jobber umiddelbart (kun for testing)</li>
                                </ul>

                                <h4>Starte køprosessor (worker)</h4>
                                <p>Køprosessoren må kjøre kontinuerlig for å behandle jobber:</p>

                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="font-medium text-zinc-900 dark:text-white mb-2">Manuell start (utvikling)</div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan queue:work --tries=3 --timeout=300
                                        </div>
                                    </div>
                                </div>

                                <h4>Supervisor-konfigurasjon (produksjon)</h4>
                                <p>I produksjon bør køprosessoren administreres av Supervisor:</p>

                                <div class="not-prose my-4">
                                    <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs overflow-x-auto">
<pre>[program:konrad-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/konrad/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/konrad/storage/logs/worker.log</pre>
                                    </div>
                                </div>

                                <h4>Supervisor-kommandoer</h4>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            sudo supervisorctl reread
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            sudo supervisorctl update
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            sudo supervisorctl start konrad-worker:*
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            sudo supervisorctl status
                                        </div>
                                    </div>
                                </div>

                                <h4>Feilede jobber</h4>
                                <p>Jobber som feiler etter flere forsøk lagres i <code>failed_jobs</code>-tabellen:</p>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan queue:failed
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan queue:retry all
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan queue:flush
                                        </div>
                                    </div>
                                </div>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Viktig</flux:callout.heading>
                                    <flux:callout.text>Etter kodeendringer må køprosessoren restartes for å laste ny kode: <code>php artisan queue:restart</code></flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Planlagte oppgaver --}}
                    <flux:card id="scheduler" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.clock class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Planlagte oppgaver (Scheduler)</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Scheduler kjører automatiske oppgaver på definerte tidspunkter.</p>

                                <h4>Planlagte oppgaver i Konrad Office</h4>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Purring på ubetalte fakturaer</span>
                                            <span class="font-mono text-zinc-500">Daglig 08:00</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Varsel om forfalte leverandørfakturaer</span>
                                            <span class="font-mono text-zinc-500">Daglig 07:00</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Opprydding av utløpte sesjoner</span>
                                            <span class="font-mono text-zinc-500">Daglig 03:00</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Sletting av gamle midlertidige filer</span>
                                            <span class="font-mono text-zinc-500">Daglig 04:00</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Oppdatering av valutakurser</span>
                                            <span class="font-mono text-zinc-500">Daglig 06:00</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Database-backup</span>
                                            <span class="font-mono text-zinc-500">Daglig 02:00</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Sjekk av bestillingspunkt (lager)</span>
                                            <span class="font-mono text-zinc-500">Daglig 09:00</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Altinn-fristpåminnelser</span>
                                            <span class="font-mono text-zinc-500">Ukentlig mandag</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>Cron-konfigurasjon</h4>
                                <p>Scheduler krever ett cron-oppføring som kjører hvert minutt:</p>

                                <div class="not-prose my-4">
                                    <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs overflow-x-auto">
                                        * * * * * cd /var/www/konrad && php artisan schedule:run >> /dev/null 2>&1
                                    </div>
                                </div>

                                <h4>Verifisere scheduler</h4>
                                <p>Se hvilke oppgaver som er planlagt:</p>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan schedule:list
                                        </div>
                                    </div>
                                </div>

                                <h4>Manuell kjøring</h4>
                                <p>Kjør en planlagt oppgave manuelt for testing:</p>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan schedule:run
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan schedule:test
                                        </div>
                                    </div>
                                </div>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Lokal utvikling</flux:callout.heading>
                                    <flux:callout.text>Under utvikling kan du bruke <code>php artisan schedule:work</code> i stedet for cron. Denne kommandoen kjører scheduler hvert minutt i terminalen.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Miljøvariabler --}}
                    <flux:card id="miljovariabler" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-sky-100 dark:bg-sky-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.variable class="w-5 h-5 text-sky-600 dark:text-sky-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Miljøvariabler</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Miljøvariabler konfigureres i <code>.env</code>-filen i applikasjonens rotmappe.</p>

                                <h4>Grunnleggende innstillinger</h4>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">APP_NAME</span>
                                            <span class="text-zinc-500 ml-2">Applikasjonsnavn</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">APP_ENV</span>
                                            <span class="text-zinc-500 ml-2">production, staging, local</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">APP_DEBUG</span>
                                            <span class="text-zinc-500 ml-2">true/false - vis detaljerte feil</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">APP_URL</span>
                                            <span class="text-zinc-500 ml-2">https://konrad.example.com</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>Database</h4>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">DB_CONNECTION</span>
                                            <span class="text-zinc-500 ml-2">mysql, pgsql</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">DB_HOST</span>
                                            <span class="text-zinc-500 ml-2">Databaseserver</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">DB_DATABASE</span>
                                            <span class="text-zinc-500 ml-2">Databasenavn</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">DB_USERNAME</span>
                                            <span class="text-zinc-500 ml-2">Brukernavn</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">DB_PASSWORD</span>
                                            <span class="text-zinc-500 ml-2">Passord</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>E-post</h4>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">MAIL_MAILER</span>
                                            <span class="text-zinc-500 ml-2">smtp, mailgun, ses</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">MAIL_HOST</span>
                                            <span class="text-zinc-500 ml-2">SMTP-server</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">MAIL_FROM_ADDRESS</span>
                                            <span class="text-zinc-500 ml-2">Avsenderadresse</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>Fillagring</h4>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">FILESYSTEM_DISK</span>
                                            <span class="text-zinc-500 ml-2">local, s3</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">AWS_ACCESS_KEY_ID</span>
                                            <span class="text-zinc-500 ml-2">For S3-lagring</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">AWS_BUCKET</span>
                                            <span class="text-zinc-500 ml-2">S3-bøttenavn</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>Modulspesifikke innstillinger</h4>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">INVENTORY_ENABLED</span>
                                            <span class="text-zinc-500 ml-2">true/false - Aktiver lagermodul</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">OPENAI_API_KEY</span>
                                            <span class="text-zinc-500 ml-2">For AI-basert bilagsparsing</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span class="font-mono text-sky-600 dark:text-sky-400">ALTINN_ENVIRONMENT</span>
                                            <span class="text-zinc-500 ml-2">test, production</span>
                                        </div>
                                    </div>
                                </div>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Sikkerhet</flux:callout.heading>
                                    <flux:callout.text>Aldri commit .env-filen til versjonskontroll. Den inneholder sensitive data som passord og API-nøkler. Bruk .env.example som mal.</flux:callout.text>
                                </flux:callout>

                                <h4>Etter endringer</h4>
                                <p>Etter endringer i .env må du tømme konfigurasjonscache:</p>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded font-mono text-xs">
                                            php artisan config:clear
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Sikkerhet --}}
                    <flux:card id="sikkerhet" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.shield-check class="w-5 h-5 text-red-600 dark:text-red-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Sikkerhet</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Viktige sikkerhetstiltak for åbeskytte bedriftens data.</p>

                                <h4>Anbefalinger</h4>
                                <ul>
                                    <li><strong>Sterke passord</strong> - Minst 8 tegn, blanding av bokstaver, tall og spesialtegn</li>
                                    <li><strong>Begrens admin-tilgang</strong> - Kun gi admin-rettigheter til de som trenger det</li>
                                    <li><strong>Deaktiver brukere</strong> - Deaktiver brukere som slutter umiddelbart</li>
                                    <li><strong>Regelmessig backup</strong> - Ta sikkerhetskopi av data jevnlig</li>
                                </ul>

                                <h4>Aktivitetslogging</h4>
                                <p>Systemet logger viktige hendelser:</p>
                                <ul>
                                    <li>Innlogginger (vellykkede og mislykkede)</li>
                                    <li>Brukerendringer</li>
                                    <li>Sletting av data</li>
                                    <li>Eksport av rapporter</li>
                                </ul>

                                <h4>Ved sikkerhetshendelse</h4>
                                <ol>
                                    <li>Deaktiver berørt bruker umiddelbart</li>
                                    <li>Sjekk aktivitetsloggen</li>
                                    <li>Bytt passord på admin-kontoer</li>
                                    <li>Kontakt support ved behov</li>
                                </ol>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Support --}}
                    <flux:card class="bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg border-0">
                        <div class="p-6 text-white">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                    <flux:icon.question-mark-circle class="w-5 h-5 text-white" />
                                </div>
                                <flux:heading size="lg" class="text-white">Trenger du hjelp?</flux:heading>
                            </div>
                            <p class="text-white/90 mb-4">
                                Ta kontakt med support for assistanse med administrasjon og oppsett.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <flux:button variant="filled" class="bg-white text-indigo-600 hover:bg-white/90">
                                    <flux:icon.envelope class="w-4 h-4 mr-2" />
                                    support@konradoffice.no
                                </flux:button>
                                <flux:button variant="ghost" class="text-white border-white/30 hover:bg-white/10">
                                    <flux:icon.phone class="w-4 h-4 mr-2" />
                                    +47 55 61 20 50
                                </flux:button>
                            </div>
                        </div>
                    </flux:card>
                </div>
            </div>
        </flux:main>

        <!-- Hidden logout form -->
    </div>
</x-layouts.app>
