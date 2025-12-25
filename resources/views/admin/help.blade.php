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
                                <a href="#stamdata" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.circle-stack class="w-4 h-4" />
                                    Stamdata
                                </a>
                                <a href="#kontoplan" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.table-cells class="w-4 h-4" />
                                    Kontoplan
                                </a>
                                <a href="#system" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.cog-6-tooth class="w-4 h-4" />
                                    System
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
                                    <li>Ga til <strong>Administrasjon > Brukere</strong></li>
                                    <li>Klikk <strong>Inviter bruker</strong></li>
                                    <li>Fyll inn e-postadresse</li>
                                    <li>Velg om brukeren skal være administrator</li>
                                    <li>Klikk <strong>Send invitasjon</strong></li>
                                </ol>

                                <h4>Administrere brukere</h4>
                                <ul>
                                    <li><strong>Deaktiver</strong> - Brukeren kan ikke logge inn, men data beholdes</li>
                                    <li><strong>Aktiver</strong> - Gjenopprett tilgang for deaktivert bruker</li>
                                    <li><strong>Gjor til admin</strong> - Gi administratorrettigheter</li>
                                    <li><strong>Fjern admin</strong> - Fjern administratorrettigheter</li>
                                </ul>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Viktig</flux:callout.heading>
                                    <flux:callout.text>Det ma alltid være minst én aktiv administrator i systemet.</flux:callout.text>
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
                                    <li>Mottaker far en unik lenke som er gyldig i 7 dager</li>
                                    <li>Ved forste besok oppretter mottaker passord</li>
                                    <li>Brukeren er na aktiv i systemet</li>
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
                                            <flux:badge color="red">Utlopt</flux:badge>
                                            <span>Invitasjonen er ikke lenger gyldig</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>Send invitasjon pa nytt</h4>
                                <p>Hvis en invitasjon har utlopt eller brukeren ikke mottok e-posten:</p>
                                <ol>
                                    <li>Finn invitasjonen i listen</li>
                                    <li>Klikk pa menyknappen</li>
                                    <li>Velg <strong>Send pa nytt</strong></li>
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
                                <p>Konfigurer informasjonen som vises pa dokumenter og i systemet.</p>

                                <h4>Firmainformasjon</h4>
                                <ul>
                                    <li><strong>Firmanavn</strong> - Vises pa alle dokumenter</li>
                                    <li><strong>Organisasjonsnummer</strong> - Vises i bunntekst</li>
                                    <li><strong>Adresse</strong> - Brukes som avsenderadresse</li>
                                    <li><strong>Telefon og e-post</strong> - Kontaktinformasjon</li>
                                </ul>

                                <h4>Bankopplysninger</h4>
                                <ul>
                                    <li><strong>Kontonummer</strong> - Vises pa fakturaer for betaling</li>
                                    <li><strong>IBAN/SWIFT</strong> - For utenlandske betalinger</li>
                                </ul>

                                <h4>Logo</h4>
                                <p>Last opp firmalogo som vises pa:</p>
                                <ul>
                                    <li>Tilbud og ordrebekreftelser</li>
                                    <li>Fakturaer og kreditnotaer</li>
                                    <li>Rapporter (valgfritt)</li>
                                </ul>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Tips</flux:callout.heading>
                                    <flux:callout.text>Bruk en logo med transparent bakgrunn (PNG) for best resultat pa dokumenter.</flux:callout.text>
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
                                            <div class="text-sm text-zinc-500">Kunder og leverandorer</div>
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
                                    </div>
                                </div>

                                <h4>Aktivere/deaktivere moduler</h4>
                                <p>Kontakt support for a endre hvilke moduler som er aktive for din bedrift.</p>
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
                                <p>Stamdata er grunnleggende oppsett som brukes pa tvers av systemet.</p>

                                <h4>Varegrupper og -typer</h4>
                                <ul>
                                    <li><strong>Varegrupper</strong> - Overordnet kategorisering av produkter</li>
                                    <li><strong>Varetyper</strong> - Underkategorier med standard MVA-sats</li>
                                </ul>

                                <h4>MVA-satser</h4>
                                <p>Standard norske MVA-satser er forhåndskonfigurert:</p>
                                <ul>
                                    <li>25% - Standard sats</li>
                                    <li>15% - Naringsmidler</li>
                                    <li>12% - Transport, kultur, kino</li>
                                    <li>0% - Fritatt/utenfor avgiftsomradet</li>
                                </ul>

                                <h4>Enheter</h4>
                                <p>Malenheter for produkter og tjenester:</p>
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
                                    <li>Mote</li>
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
                                <p>Kontoplanen er basert pa norsk standard NS 4102 og er ferdig konfigurert.</p>

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
                                            <span>Lonn og personal (5000-5999)</span>
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
                                <p>Systemsiden gir deg oversikt over teknisk informasjon og vedlikeholdsverktoy.</p>

                                <h4>Systeminformasjon</h4>
                                <ul>
                                    <li>Laravel- og PHP-versjon</li>
                                    <li>Miljo (production/development)</li>
                                    <li>Debug-modus status</li>
                                </ul>

                                <h4>Vedlikeholdsverktoy</h4>
                                <ul>
                                    <li><strong>Tom cache</strong> - Fjern midlertidige filer</li>
                                    <li><strong>Sikkerhetskopi</strong> - Ta backup av database</li>
                                    <li><strong>Se logger</strong> - Sjekk feilmeldinger</li>
                                </ul>

                                <h4>Vedlikeholdsmodus</h4>
                                <p>Aktiver vedlikeholdsmodus nar du skal gjore storre endringer:</p>
                                <ul>
                                    <li>Brukere ser en "under vedlikehold"-melding</li>
                                    <li>Administratorer kan fortsatt logge inn</li>
                                    <li>Deaktiver nar du er ferdig</li>
                                </ul>
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
                                <p>Viktige sikkerhetstiltak for a beskytte bedriftens data.</p>

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
                                    <li>Deaktiver berort bruker umiddelbart</li>
                                    <li>Sjekk aktivitetsloggen</li>
                                    <li>Bytt passord pa admin-kontoer</li>
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
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</x-layouts.app>
