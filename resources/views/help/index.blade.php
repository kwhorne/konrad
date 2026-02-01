<x-layouts.app title="Brukerdokumentasjon">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="help" />
        <x-app-header current="help" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.book-open class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Brukerdokumentasjon
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Komplett veiledning for Konrad forretningssystem
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
                                <a href="#kom-i-gang" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.rocket-launch class="w-4 h-4" />
                                    Kom i gang
                                </a>
                                <a href="#dashboard" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.home class="w-4 h-4" />
                                    Dashboard
                                </a>
                                <a href="#mine-aktiviteter" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.clipboard-document-list class="w-4 h-4" />
                                    Mine aktiviteter
                                </a>
                                <a href="#kontakter" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.users class="w-4 h-4" />
                                    Kontaktregister
                                </a>
                                <a href="#produkter" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.cube class="w-4 h-4" />
                                    Vareregister
                                </a>
                                <a href="#prosjekter" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.folder class="w-4 h-4" />
                                    Prosjekter
                                </a>
                                <a href="#arbeidsordrer" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.clipboard-document-list class="w-4 h-4" />
                                    Arbeidsordrer
                                </a>
                                <a href="#timeregistrering" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.clock class="w-4 h-4" />
                                    Timeregistrering
                                </a>
                                <a href="#timerapporter" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 pl-7">
                                    <flux:icon.chart-bar class="w-4 h-4" />
                                    Timerapporter
                                </a>
                                <a href="#salg" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.shopping-cart class="w-4 h-4" />
                                    Salg
                                </a>
                                <a href="#okonomi" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.calculator class="w-4 h-4" />
                                    Okonomi
                                </a>
                                <a href="#selskapsanalyse" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.sparkles class="w-4 h-4" />
                                    Selskapsanalyse
                                </a>
                                <a href="#innboks" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.inbox-arrow-down class="w-4 h-4" />
                                    Innboks (AI-tolkning)
                                </a>
                                <a href="#rapporter" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.chart-bar class="w-4 h-4" />
                                    Rapporter
                                </a>
                                <a href="#mva" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.document-chart-bar class="w-4 h-4" />
                                    MVA-meldinger
                                </a>
                                <a href="#aksjonaerregister" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.user-group class="w-4 h-4" />
                                    Aksjonaerregister
                                </a>
                                <a href="#skatt" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.receipt-percent class="w-4 h-4" />
                                    Skatt
                                </a>
                                <a href="#arsregnskap" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.document-text class="w-4 h-4" />
                                    Arsregnskap
                                </a>
                                <a href="#altinn" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.paper-airplane class="w-4 h-4" />
                                    Altinn
                                </a>
                                <a href="#innstillinger" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.cog-6-tooth class="w-4 h-4" />
                                    Innstillinger
                                </a>
                                <a href="#avdelinger" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 pl-7">
                                    <flux:icon.building-library class="w-4 h-4" />
                                    Avdelinger
                                </a>
                                <a href="#regnskapsinnstillinger" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 pl-7">
                                    <flux:icon.calculator class="w-4 h-4" />
                                    Regnskapsinnstillinger
                                </a>
                                <a href="#kontoplan" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 pl-7">
                                    <flux:icon.table-cells class="w-4 h-4" />
                                    Kontoplan
                                </a>
                                <a href="#selskap" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon.building-office-2 class="w-4 h-4" />
                                    Selskap og brukere
                                </a>
                            </nav>
                        </div>
                    </flux:card>
                </div>

                {{-- Main Content --}}
                <div class="lg:col-span-3 space-y-8">
                    {{-- Kom i gang --}}
                    <flux:card id="kom-i-gang" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.rocket-launch class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Kom i gang</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Velkommen til Konrad - et komplett forretningssystem for norske bedrifter. Denne dokumentasjonen hjelper deg med a ta i bruk systemet effektivt.</p>

                                <h4>Forste gangs innlogging</h4>
                                <ol>
                                    <li>Apne nettleseren og ga til systemets adresse</li>
                                    <li>Logg inn med brukernavn og passord du har fatt tildelt</li>
                                    <li>Du kommer til dashboardet som gir deg oversikt over systemet</li>
                                </ol>

                                <h4>Navigasjon</h4>
                                <p>Konrad har to hovedpaneler med egne menyer:</p>

                                <h5>App-panel (hovedmeny)</h5>
                                <ul>
                                    <li><strong>Dashboard</strong> - Hovedoversikt over virksomheten</li>
                                    <li><strong>Mine aktiviteter</strong> - Personlige oppgaver, forslag og notater</li>
                                    <li><strong>CRM</strong> - Kontakter, Varer, Tilbud, Ordrer, Faktura</li>
                                    <li><strong>Prosjekt</strong> - Prosjekter, Arbeidsordrer</li>
                                    <li><strong>Kontrakter</strong> - Kontraktsregister</li>
                                    <li><strong>Eiendeler</strong> - Eiendelsregister</li>
                                    <li><strong>Okonomi</strong> - Link til okonomi-panelet</li>
                                    <li><strong>Administrasjon</strong> - Brukeradministrasjon (kun admin)</li>
                                </ul>

                                <h5>Okonomi-panel</h5>
                                <p>Eget panel for regnskap og okonomi (krever okonomi- eller admin-rolle):</p>
                                <ul>
                                    <li><strong>Dashboard</strong> - Okonomisk oversikt med grafer</li>
                                    <li><strong>Okonomi</strong> - Bilag, Innboks, Reskontro, Rapporter, MVA, Kontoplan</li>
                                    <li><strong>Arsoppgjor</strong> - Aksjonaerregister, Skattemelding, Arsregnskap, Altinn</li>
                                </ul>

                                <flux:callout variant="info" icon="information-circle" class="not-prose my-4">
                                    <flux:callout.heading>Brukerroller</flux:callout.heading>
                                    <flux:callout.text>
                                        <strong>Admin</strong> har full tilgang. <strong>Okonomi</strong> har tilgang til app og okonomi-panel. <strong>Bruker</strong> har kun tilgang til app-panelet.
                                    </flux:callout.text>
                                </flux:callout>

                                <h4>Hurtigtaster</h4>
                                <div class="not-prose">
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div class="flex items-center gap-2 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <kbd class="px-2 py-1 bg-white dark:bg-zinc-700 rounded text-xs">Ctrl</kbd> + <kbd class="px-2 py-1 bg-white dark:bg-zinc-700 rounded text-xs">K</kbd>
                                            <span>Sok</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <kbd class="px-2 py-1 bg-white dark:bg-zinc-700 rounded text-xs">Esc</kbd>
                                            <span>Lukk modal/meny</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Dashboard --}}
                    <flux:card id="dashboard" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.home class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Dashboard</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Dashboardet gir deg en personlig oversikt over det som er mest relevant for din rolle i bedriften.</p>

                                <h4>Rollebasert innhold</h4>
                                <p>Dashboardet tilpasser seg automatisk basert på din brukerrolle:</p>
                                <ul>
                                    <li><strong>Alle brukere</strong> - Ser egne timer denne uken og timeliste-status</li>
                                    <li><strong>Okonomi og admin</strong> - Ser okonomiske nøkkeltall, forfalte fakturaer og bilag i innboks</li>
                                    <li><strong>Salg</strong> - Ser aktive tilbud og åpne ordrer</li>
                                    <li><strong>Prosjekt</strong> - Ser aktive prosjekter og åpne arbeidsordrer</li>
                                </ul>

                                <h4>Mine timer denne uken</h4>
                                <p>Alle brukere ser et kort med timer registrert denne uken. Kortet viser:</p>
                                <ul>
                                    <li>Antall timer ført</li>
                                    <li>Status på timelisten (utkast, sendt, godkjent)</li>
                                    <li>Direktelink til timeregistrering</li>
                                </ul>

                                <h4>Timer til godkjenning</h4>
                                <p>Ledere og økonomibrukere ser timelister som venter på godkjenning, med direktelink til godkjenningssiden.</p>

                                <h4>Bilag i innboksen</h4>
                                <p>Økonomibrukere ser antall bilag som venter på behandling i innboksen.</p>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Tips</flux:callout.heading>
                                    <flux:callout.text>
                                        Bruk snarveiene nederst på dashbordet for rask tilgang til de vanligste funksjonene.
                                    </flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Mine aktiviteter --}}
                    <flux:card id="mine-aktiviteter" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.clipboard-document-list class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Mine aktiviteter</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Mine aktiviteter gir deg en personlig oversikt over hva du bør følge opp, med Intelligente forslag og personlige notater.</p>

                                <h4>Forslag til aktiviteter</h4>
                                <p>Klikk <strong>Generer forslag</strong> for å få Intelligente anbefalinger basert på:</p>
                                <ul>
                                    <li><strong>Aktiviteter</strong> - Ventende oppgaver tildelt deg</li>
                                    <li><strong>Tilbud</strong> - Utkast og sendte tilbud som venter på svar</li>
                                    <li><strong>Arbeidsordrer</strong> - Åpne arbeidsordrer tildelt deg</li>
                                    <li><strong>Prosjekter</strong> - Prosjekter der du er prosjektleder</li>
                                    <li><strong>Fakturaer</strong> - Ubetalte fakturaer du har opprettet</li>
                                </ul>

                                <h4>Arbeidsmengde-score</h4>
                                <p>En visuell indikator (0-100) som viser din totale arbeidsmengde. Høyere score betyr mer å gjøre:</p>
                                <ul>
                                    <li><strong>0-49</strong> - Rolig periode</li>
                                    <li><strong>50-79</strong> - Normal arbeidsmengde</li>
                                    <li><strong>80-100</strong> - Mye å gjøre</li>
                                </ul>

                                <h4>Raske gevinster og fokusområder</h4>
                                <p>AI-en identifiserer:</p>
                                <ul>
                                    <li><strong>Raske gevinster</strong> - Oppgaver som kan fullføres raskt</li>
                                    <li><strong>Fokusområder</strong> - Hvor du bør legge mest innsats</li>
                                </ul>

                                <h4>Mine notater</h4>
                                <p>Skriv personlige notater med rik tekst-formatering:</p>
                                <ul>
                                    <li>Opprett notater via <strong>Nytt notat</strong>-knappen</li>
                                    <li>Bruk overskrifter, lister, lenker og formatering</li>
                                    <li>Fest viktige notater så de alltid vises øverst</li>
                                    <li>Notatene følger deg på tvers av selskaper</li>
                                </ul>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Tips</flux:callout.heading>
                                    <flux:callout.text>
                                        Notatene dine er personlige og følger deg selv om du bytter mellom selskaper. Bruk dem til huskelister, møtenotater eller annet du vil ha lett tilgjengelig.
                                    </flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Kontaktregister --}}
                    <flux:card id="kontakter" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.users class="w-5 h-5 text-green-600 dark:text-green-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Kontaktregister</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Kontaktregisteret er hjertet i systemet. Her administrerer du alle kunder og leverandorer.</p>

                                <h4>Opprette ny kontakt</h4>
                                <ol>
                                    <li>Klikk <strong>Ny kontakt</strong>-knappen</li>
                                    <li>Velg kontakttype: <em>Kunde</em>, <em>Leverandor</em> eller <em>Begge</em></li>
                                    <li>Fyll inn firmanavn og organisasjonsnummer</li>
                                    <li>Legg til adresse og kontaktinformasjon</li>
                                    <li>Klikk <strong>Lagre</strong></li>
                                </ol>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Tips: Brreg-oppslag</flux:callout.heading>
                                    <flux:callout.text>Skriv inn organisasjonsnummer for a automatisk hente firmainformasjon fra Bronnoysundregistrene.</flux:callout.text>
                                </flux:callout>

                                <h4>Kontaktpersoner</h4>
                                <p>Hver kontakt kan ha flere kontaktpersoner med navn, e-post, telefon og rolle.</p>

                                <h4>Aktiviteter</h4>
                                <p>Loggfor aktiviteter som telefonsamtaler, moter og e-poster for a holde oversikt over kundekommunikasjon:</p>
                                <ul>
                                    <li>Klikk pa en kontakt for a apne detaljer</li>
                                    <li>Ga til <strong>Aktiviteter</strong>-fanen</li>
                                    <li>Klikk <strong>Ny aktivitet</strong></li>
                                    <li>Velg type, dato og beskriv aktiviteten</li>
                                </ul>

                                <h4>Dokumenter</h4>
                                <p>Last opp og organiser dokumenter knyttet til kontakten, som kontrakter, avtaler og korrespondanse.</p>

                                <h4>Tilbud, Ordrer og Fakturaer</h4>
                                <p>Fra kontaktkortet kan du se alle dokumenter knyttet til kontakten og opprette nye:</p>
                                <ol>
                                    <li>Apne kontakten og ga til <strong>Dokumenter</strong>-fanen</li>
                                    <li>Her ser du alle tilbud, ordrer og fakturaer for kontakten</li>
                                    <li>Klikk <strong>Nytt tilbud</strong>, <strong>Ny ordre</strong> eller <strong>Ny faktura</strong></li>
                                    <li>Dokumentet opprettes med kontakten forhåndsvalgt</li>
                                    <li>Legg til linjer med produkter fra vareregisteret</li>
                                </ol>

                                <flux:callout variant="success" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Tips: Opprett og legg til linjer</flux:callout.heading>
                                    <flux:callout.text>Nar du oppretter et nytt dokument, forblir modalen apen sa du kan legge til varelinjer med en gang.</flux:callout.text>
                                </flux:callout>

                                <h4>Sosiale medier</h4>
                                <p>Legg til lenker til kontaktens LinkedIn, Facebook og Twitter-profiler for rask tilgang.</p>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Vareregister --}}
                    <flux:card id="produkter" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.cube class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Vareregister</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Vareregisteret inneholder alle produkter og tjenester du selger.</p>

                                <h4>Produktstruktur</h4>
                                <ul>
                                    <li><strong>Varegrupper</strong> - Overordnet kategorisering (f.eks. "Tjenester", "Varer")</li>
                                    <li><strong>Varetyper</strong> - Underkategorier med standard MVA-sats</li>
                                    <li><strong>Produkter</strong> - Individuelle varer/tjenester</li>
                                </ul>

                                <h4>Opprette produkt</h4>
                                <ol>
                                    <li>Klikk <strong>Nytt produkt</strong></li>
                                    <li>Fyll inn produktnavn og eventuelt SKU (varenummer)</li>
                                    <li>Velg varegruppe og varetype</li>
                                    <li>Angi pris og eventuell kostpris</li>
                                    <li>Velg enhet (stk, timer, kg, etc.)</li>
                                    <li>MVA-sats arves fra varetypen</li>
                                </ol>

                                <h4>MVA-satser</h4>
                                <p>Systemet stotter alle norske MVA-satser:</p>
                                <div class="not-prose">
                                    <div class="grid grid-cols-3 gap-2 text-sm my-4">
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded text-center">
                                            <div class="font-bold text-lg">25%</div>
                                            <div class="text-zinc-500">Standard</div>
                                        </div>
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded text-center">
                                            <div class="font-bold text-lg">15%</div>
                                            <div class="text-zinc-500">Naringsmidler</div>
                                        </div>
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded text-center">
                                            <div class="font-bold text-lg">12%</div>
                                            <div class="text-zinc-500">Transport/kultur</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Prosjekter --}}
                    <flux:card id="prosjekter" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.folder class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Prosjekter</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Prosjektmodulen lar deg organisere arbeid knyttet til kunder med budsjett- og timesstyring.</p>

                                <h4>Opprette prosjekt</h4>
                                <ol>
                                    <li>Klikk <strong>Nytt prosjekt</strong></li>
                                    <li>Gi prosjektet et navn og velg kunde</li>
                                    <li>Velg <strong>prosjektleder</strong> - personen som har hovedansvaret</li>
                                    <li>Velg prosjekttype og status</li>
                                    <li>Angi budsjett og estimerte timer</li>
                                    <li>Sett start- og sluttdato</li>
                                </ol>

                                <h4>Prosjektleder</h4>
                                <p>Hver prosjekt kan ha en prosjektleder som er ansvarlig for gjennomforingen:</p>
                                <ul>
                                    <li>Prosjektleder vises i prosjektlisten for enkel oversikt</li>
                                    <li>Velg fra listen over aktive ansatte i firmaet</li>
                                    <li>Kan endres nar som helst ved a redigere prosjektet</li>
                                </ul>

                                <h4>Prosjektlinjer</h4>
                                <p>Legg til produkter og tjenester som skal leveres i prosjektet:</p>
                                <ul>
                                    <li>Velg produkt fra vareregisteret</li>
                                    <li>Angi antall og eventuell rabatt</li>
                                    <li>Systemet beregner totaler automatisk</li>
                                </ul>

                                <h4>Statuser</h4>
                                <p>Folg prosjektets livssyklus:</p>
                                <div class="not-prose flex flex-wrap gap-2 my-4">
                                    <flux:badge color="blue">Planlegging</flux:badge>
                                    <flux:badge color="yellow">Pagar</flux:badge>
                                    <flux:badge color="green">Fullfort</flux:badge>
                                    <flux:badge color="zinc">Pause</flux:badge>
                                    <flux:badge color="red">Kansellert</flux:badge>
                                </div>

                                <h4>Prosjekttyper</h4>
                                <p>Kategoriser prosjektene dine:</p>
                                <ul>
                                    <li><strong>Konsulentoppdrag</strong> - Radgivning og konsulentarbeid</li>
                                    <li><strong>Utviklingsprosjekt</strong> - Programvareutvikling og tekniske prosjekter</li>
                                    <li><strong>Supportavtale</strong> - Lopende support- og vedlikeholdsavtaler</li>
                                    <li><strong>Implementering</strong> - Utrulling av systemer</li>
                                    <li><strong>Opplæring</strong> - Kurs og opplæringsprosjekter</li>
                                </ul>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Tips</flux:callout.heading>
                                    <flux:callout.text>
                                        Prosjektleder vises i prosjektlisten, slik at alle enkelt kan se hvem som er ansvarlig for hvert prosjekt.
                                    </flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Arbeidsordrer --}}
                    <flux:card id="arbeidsordrer" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.clipboard-document-list class="w-5 h-5 text-red-600 dark:text-red-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Arbeidsordrer</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Arbeidsordrer brukes for a planlegge og registrere arbeid som skal utfores.</p>

                                <h4>Opprette arbeidsordre</h4>
                                <ol>
                                    <li>Klikk <strong>Ny arbeidsordre</strong></li>
                                    <li>Velg kunde og eventuelt prosjekt</li>
                                    <li>Beskriv arbeidet som skal utfores</li>
                                    <li>Velg type: Service, Reparasjon, Installasjon, Vedlikehold eller Konsultasjon</li>
                                    <li>Sett prioritet og planlagt dato</li>
                                    <li>Tildel ansvarlig person</li>
                                </ol>

                                <h4>Timeregistrering</h4>
                                <p>Registrer timer brukt pa arbeidsordren:</p>
                                <ul>
                                    <li>Apne arbeidsordren</li>
                                    <li>Ga til <strong>Timer</strong>-seksjonen</li>
                                    <li>Legg til medarbeider, dato og antall timer</li>
                                    <li>Beskriv arbeidet som ble utfort</li>
                                </ul>

                                <h4>Materialbruk</h4>
                                <p>Registrer produkter og materialer brukt:</p>
                                <ul>
                                    <li>Velg produkt fra vareregisteret</li>
                                    <li>Angi antall</li>
                                    <li>Pris hentes automatisk</li>
                                </ul>

                                <h4>Arbeidsflyt</h4>
                                <div class="not-prose my-4">
                                    <div class="flex flex-wrap items-center gap-2 text-sm">
                                        <flux:badge color="zinc">Ny</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="blue">Planlagt</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="yellow">Pagar</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="green">Fullfort</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="emerald">Godkjent</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="indigo">Fakturert</flux:badge>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Timeregistrering --}}
                    <flux:card id="timeregistrering" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.clock class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Timeregistrering</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Registrer arbeidstimer ukentlig og send til godkjenning. Timer kan knyttes til prosjekter, arbeidsordrer eller registreres som intern tid.</p>

                                <h4>Registrere timer</h4>
                                <p>Du kan registrere timer pa to mater:</p>
                                <ul>
                                    <li><strong>Inline i rutenett:</strong> Skriv timer direkte i cellen for den aktuelle dagen</li>
                                    <li><strong>Via modal:</strong> Dobbelklikk pa en celle eller bruk <strong>Registrer timer</strong>-knappen for a apne et skjema med flere felt</li>
                                </ul>

                                <h4>Koble til prosjekt eller arbeidsordre</h4>
                                <ol>
                                    <li>Klikk <strong>Legg til linje</strong> eller <strong>Registrer timer</strong></li>
                                    <li>Velg type: Prosjekt, Arbeidsordre eller Annet (intern tid)</li>
                                    <li>Velg prosjekt/arbeidsordre fra listen</li>
                                    <li>For intern tid, skriv en beskrivelse (f.eks. "Internt mote")</li>
                                </ol>

                                <h4>Notatfelt</h4>
                                <p>Nar du bruker modalvinduet kan du legge til et notat som beskriver hva du har jobbet med. Dette er nyttig for godkjenneren og for egen oversikt.</p>

                                <h4>Sende til godkjenning</h4>
                                <ol>
                                    <li>Nar uken er ferdig, klikk <strong>Send til godkjenning</strong></li>
                                    <li>Legg eventuelt til en kommentar</li>
                                    <li>Timeseddelen lases for redigering</li>
                                </ol>

                                <h4>Arbeidsflyt</h4>
                                <div class="not-prose my-4">
                                    <div class="flex flex-wrap items-center gap-2 text-sm">
                                        <flux:badge color="zinc">Utkast</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="amber">Innsendt</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="green">Godkjent</flux:badge>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-sm mt-2">
                                        <flux:badge color="amber">Innsendt</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="red">Avvist</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="zinc">Utkast</flux:badge>
                                        <span class="text-zinc-500">(kan redigeres og sendes pa nytt)</span>
                                    </div>
                                </div>

                                <h4>Mine timer (historikk)</h4>
                                <p>Under <strong>Mine timer</strong> finner du oversikt over alle dine timesedler:</p>
                                <ul>
                                    <li>Filtrer pa status (utkast, innsendt, godkjent, avvist)</li>
                                    <li>Filtrer pa ar</li>
                                    <li>Klikk pa en timeseddel for a se detaljer</li>
                                </ul>

                                <h4>Godkjenning (for ledere)</h4>
                                <p>Eiere og administratorer kan godkjenne andres timer:</p>
                                <ul>
                                    <li>Antall ventende timesedler vises som badge i menyen</li>
                                    <li>Se detaljer for hver timeseddel</li>
                                    <li>Godkjenn eller avvis med begrunnelse</li>
                                    <li>Du kan ikke godkjenne egne timer</li>
                                </ul>

                                <h4 id="timerapporter" class="scroll-mt-4">Timerapporter (for ledere)</h4>
                                <p>Under <strong>Timer → Rapporter</strong> finner ledere oversikt over timer pa tvers av hele firmaet:</p>

                                <p><strong>Rapporttyper:</strong></p>
                                <ul>
                                    <li><strong>Per prosjekt:</strong> Se totaltimer fordelt pa prosjekter. Klikk pa et prosjekt for a se hvilke ansatte som har jobbet pa det.</li>
                                    <li><strong>Per arbeidsordre:</strong> Se timer per arbeidsordre med tilhorende prosjekt.</li>
                                    <li><strong>Per ansatt:</strong> Oversikt over timer per medarbeider.</li>
                                    <li><strong>Per uke:</strong> Se ukentlige totaler over tid.</li>
                                </ul>

                                <p><strong>Filtrering:</strong></p>
                                <ul>
                                    <li>Velg tidsperiode med hurtigvalg (denne uken, forrige maned, dette kvartalet, osv.)</li>
                                    <li>Eller angi egendefinert fra- og til-dato</li>
                                    <li>Velg <strong>Hele perioden</strong> for a se alle registrerte timer</li>
                                </ul>

                                <p><strong>Oppsummering:</strong></p>
                                <p>Øverst pa rapportsiden vises alltid en oppsummering med:</p>
                                <ul>
                                    <li>Totalt antall timer i valgt periode</li>
                                    <li>Fakturerbare timer</li>
                                    <li>Ikke-fakturerbare timer</li>
                                    <li>Antall ansatte og prosjekter</li>
                                </ul>

                                <div class="not-prose mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <div class="flex gap-3">
                                        <flux:icon.light-bulb class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" />
                                        <div>
                                            <p class="font-medium text-blue-900 dark:text-blue-100">Tips</p>
                                            <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                                Dobbelklikk pa en timecelle for a apne registreringsskjemaet med prosjektet forhåndsvalgt.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Salg --}}
                    <flux:card id="salg" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.shopping-cart class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Salg</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Salgsmodulen dekker hele salgsprosessen fra tilbud til faktura.</p>

                                <h4>Tilbud</h4>
                                <p>Opprett profesjonelle tilbud til kunder:</p>
                                <ol>
                                    <li>Klikk <strong>Nytt tilbud</strong> fra Tilbud-siden eller fra kontaktkortet</li>
                                    <li>Velg kunde - adresseinformasjon fylles ut automatisk</li>
                                    <li>Klikk <strong>Opprett og legg til linjer</strong></li>
                                    <li>Legg til produkter og tjenester fra vareregisteret</li>
                                    <li>Angi rabatter om onskelig</li>
                                    <li>Sett gyldighetsdato</li>
                                    <li>Forhandsvis PDF og send pa e-post</li>
                                </ol>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Opprett fra kontaktkortet</flux:callout.heading>
                                    <flux:callout.text>Du kan opprette tilbud, ordrer og fakturaer direkte fra <strong>Dokumenter</strong>-fanen pa kontaktkortet. Kontakten velges automatisk.</flux:callout.text>
                                </flux:callout>

                                <h4>Ordrer</h4>
                                <p>Nar kunden aksepterer tilbudet:</p>
                                <ol>
                                    <li>Apne tilbudet</li>
                                    <li>Klikk <strong>Konverter til ordre</strong></li>
                                    <li>Ordren opprettes med alle linjer fra tilbudet</li>
                                    <li>Send ordrebekreftelse til kunden</li>
                                </ol>

                                <h4>Fakturaer</h4>
                                <p>Fakturer kunden nar arbeidet er levert:</p>
                                <ol>
                                    <li>Opprett faktura fra ordre eller manuelt</li>
                                    <li>Kontroller linjer og belop</li>
                                    <li>Sett forfallsdato (standard 14 dager)</li>
                                    <li>Send faktura pa e-post med PDF</li>
                                </ol>

                                <h4>Betalinger</h4>
                                <p>Registrer innbetalinger:</p>
                                <ol>
                                    <li>Apne fakturaen</li>
                                    <li>Klikk <strong>Registrer betaling</strong></li>
                                    <li>Angi belop, dato og betalingsmate</li>
                                    <li>Fakturaen oppdateres automatisk</li>
                                </ol>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Kreditnota</flux:callout.heading>
                                    <flux:callout.text>For a kreditere en faktura, apne fakturaen og klikk "Opprett kreditnota". Dette oppretter en negativ faktura som utligner den opprinnelige.</flux:callout.text>
                                </flux:callout>

                                <h4>Nummerserier</h4>
                                <p>Systemet bruker automatisk nummerering:</p>
                                <ul>
                                    <li>Tilbud: T-YYYY-NNNN</li>
                                    <li>Ordrer: O-YYYY-NNNN</li>
                                    <li>Fakturaer: F-YYYY-NNNN</li>
                                    <li>Kreditnotaer: K-YYYY-NNNN</li>
                                </ul>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Okonomi --}}
                    <flux:card id="okonomi" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.calculator class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Okonomi</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Okonomismodulen gir deg full kontroll over regnskapet.</p>

                                <flux:callout variant="info" icon="arrow-top-right-on-square" class="not-prose my-4">
                                    <flux:callout.heading>Eget okonomi-panel</flux:callout.heading>
                                    <flux:callout.text>Okonomifunksjonene er tilgjengelige via <strong>/economy</strong>-panelet. Dette krever <strong>okonomi</strong> eller <strong>admin</strong>-rolle. Klikk pa <strong>Okonomi</strong>-lenken i app-menyen for a ga til okonomi-panelet.</flux:callout.text>
                                </flux:callout>

                                <h4>Kontoplan</h4>
                                <p>Systemet bruker norsk standard kontoplan (NS 4102):</p>
                                <ul>
                                    <li><strong>Klasse 1</strong> - Eiendeler (1000-1999)</li>
                                    <li><strong>Klasse 2</strong> - Egenkapital og gjeld (2000-2999)</li>
                                    <li><strong>Klasse 3</strong> - Inntekter (3000-3999)</li>
                                    <li><strong>Klasse 4</strong> - Varekostnad (4000-4999)</li>
                                    <li><strong>Klasse 5</strong> - Lonn (5000-5999)</li>
                                    <li><strong>Klasse 6-7</strong> - Driftskostnader (6000-7999)</li>
                                    <li><strong>Klasse 8</strong> - Finansposter (8000-8999)</li>
                                </ul>

                                <h4>Bilagsregistrering</h4>
                                <p>Registrer manuelle bilag:</p>
                                <ol>
                                    <li>Ga til <strong>Okonomi-panelet > Bilagsregistrering</strong></li>
                                    <li>Klikk <strong>Nytt bilag</strong></li>
                                    <li>Sett bilagsdato og beskrivelse</li>
                                    <li>Legg til linjer med konto, debet og kredit</li>
                                    <li>Papass at debet = kredit (bilaget ma balansere)</li>
                                    <li>Lagre og bokfor bilaget</li>
                                </ol>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Automatisk bokforing</flux:callout.heading>
                                    <flux:callout.text>Fakturaer og betalinger bokfores automatisk. Du trenger kun registrere manuelle bilag for transaksjoner som ikke kommer fra salgsprosessen.</flux:callout.text>
                                </flux:callout>

                                <h4>Kundereskontro</h4>
                                <p>Oversikt over utestående kundefordringer:</p>
                                <ul>
                                    <li>Se alle ubetalte fakturaer per kunde</li>
                                    <li>Aldersfordeling: 0-30, 31-60, 61-90, 90+ dager</li>
                                    <li>Klikk pa kunde for detaljert oversikt</li>
                                </ul>

                                <h4>Leverandorreskontro</h4>
                                <p>Oversikt over leverandorgjeld:</p>
                                <ul>
                                    <li>Registrer leverandorfakturaer</li>
                                    <li>Folg opp forfallsdatoer</li>
                                    <li>Registrer betalinger</li>
                                </ul>

                                <h4>Leverandorfakturaer</h4>
                                <p>Det finnes to mater a registrere leverandorfakturaer:</p>

                                <p><strong>Manuell registrering:</strong></p>
                                <ol>
                                    <li>Klikk <strong>Ny leverandorfaktura</strong></li>
                                    <li>Velg leverandor</li>
                                    <li>Angi leverandorens fakturanummer og dato</li>
                                    <li>Legg til linjer med kostnadskonto</li>
                                    <li>Godkjenn for bokforing</li>
                                    <li>Registrer betaling nar den er utfort</li>
                                </ol>

                                <flux:callout variant="info" icon="sparkles" class="not-prose my-4">
                                    <flux:callout.heading>AI-tolkning i Innboks</flux:callout.heading>
                                    <flux:callout.text>Last opp leverandorfakturaer som PDF eller bilde i <a href="#innboks" class="text-indigo-600 dark:text-indigo-400 underline">Innboksen</a>. Systemet tolker automatisk leverandor, belop og datoer med AI.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Selskapsanalyse --}}
                    <flux:card id="selskapsanalyse" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.sparkles class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Selskapsanalyse</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Selskapsanalysen gir deg en komplett oversikt over selskapets okonomiske helse basert pa dine faktiske regnskapsdata.</p>

                                <h4>Kjore en analyse</h4>
                                <ol>
                                    <li>Ga til <strong>Okonomi → Analyse</strong></li>
                                    <li>Klikk <strong>Start analyse</strong></li>
                                    <li>Vent mens systemet analyserer dataene (10-30 sekunder)</li>
                                    <li>Se gjennom resultatene</li>
                                </ol>

                                <h4>Hva analysen inneholder</h4>
                                <p>Analysen gir deg innsikt pa flere omrader:</p>

                                <p><strong>Okonomisk helse:</strong></p>
                                <ul>
                                    <li>Score fra 0-100 som viser den generelle okonomiske tilstanden</li>
                                    <li>Statusindikator: Utmerket, God, Akseptabel, Bekymringsfull eller Kritisk</li>
                                </ul>

                                <p><strong>Nokkeltall:</strong></p>
                                <ul>
                                    <li><strong>Likviditet</strong> - Evne til a betale lopende utgifter</li>
                                    <li><strong>Lonnsomhet</strong> - Fortjeneste i forhold til omsetning</li>
                                    <li><strong>Vekst</strong> - Endring fra forrige ar</li>
                                    <li><strong>Kundefordringer</strong> - Status pa utestående krav</li>
                                </ul>

                                <p><strong>SWOT-analyse:</strong></p>
                                <ul>
                                    <li><strong>Styrker</strong> - Hva som fungerer bra i selskapet</li>
                                    <li><strong>Svakheter</strong> - Omrader som kan forbedres</li>
                                    <li><strong>Muligheter</strong> - Potensielle vekstomrader</li>
                                    <li><strong>Risikoer</strong> - Farer du bor vare oppmerksom pa</li>
                                </ul>

                                <p><strong>Anbefalinger:</strong></p>
                                <ul>
                                    <li>Konkrete tiltak med prioritering (hoy, medium, lav)</li>
                                    <li>Forventet effekt av hvert tiltak</li>
                                </ul>

                                <div class="not-prose mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <div class="flex gap-3">
                                        <flux:icon.light-bulb class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" />
                                        <div>
                                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Tips</p>
                                            <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">Kjor analysen jevnlig for a folge med pa utviklingen. Sammenlign resultatene over tid for a se om tiltakene dine gir effekt.</p>
                                        </div>
                                    </div>
                                </div>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Viktig</flux:callout.heading>
                                    <flux:callout.text>Analysen er et hjelpemiddel og bor verifiseres av en regnskapsforer for viktige beslutninger.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Innboks (AI-tolkning) --}}
                    <flux:card id="innboks" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.inbox-arrow-down class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Innboks - Inngaende bilag</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Innboksen bruker kunstig intelligens (AI) til a automatisk tolke leverandorfakturaer fra PDF-er og bilder. Systemet ekstraherer leverandor, fakturanummer, datoer, belop og MVA - og foreslår riktig kostnadskonto basert pa tidligere posteringer.</p>

                                <h4>Laste opp bilag</h4>
                                <ol>
                                    <li>Ga til <strong>Okonomi-panelet > Innkommende bilag</strong></li>
                                    <li>Klikk <strong>Last opp bilag</strong></li>
                                    <li>Velg en eller flere filer (PDF, JPG, PNG)</li>
                                    <li>Klikk <strong>Last opp</strong></li>
                                    <li>Bilagene sendes automatisk til AI-tolkning</li>
                                </ol>

                                <flux:callout variant="info" icon="sparkles" class="not-prose my-4">
                                    <flux:callout.heading>AI-tolkning</flux:callout.heading>
                                    <flux:callout.text>Systemet bruker ChatGPT (GPT-4o) til a lese og forsta innholdet i fakturaene. Tolkningen tar vanligvis 5-30 sekunder per bilag.</flux:callout.text>
                                </flux:callout>

                                <h4>Godkjenningsflyt</h4>
                                <p>Inngaende bilag gar gjennom en to-trinns godkjenningsprosess:</p>
                                <div class="not-prose my-4">
                                    <div class="flex flex-wrap items-center gap-2 text-sm">
                                        <flux:badge color="zinc">Venter</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="blue">Tolkes</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="purple">Tolket</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="yellow">Attestert</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="green">Godkjent</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="emerald">Bokfort</flux:badge>
                                    </div>
                                </div>

                                <h4>Attestere bilag</h4>
                                <ol>
                                    <li>Klikk pa et tolket bilag for a apne detaljer</li>
                                    <li>Kontroller at AI har tolket riktig:
                                        <ul>
                                            <li>Leverandor (kan sokes opp eller endres)</li>
                                            <li>Fakturanummer og datoer</li>
                                            <li>Belop og MVA</li>
                                            <li>Kostnadskonto</li>
                                        </ul>
                                    </li>
                                    <li>Gjor eventuelle korrigeringer</li>
                                    <li>Klikk <strong>Attester</strong></li>
                                </ol>

                                <h4>Godkjenne og bokfore</h4>
                                <ol>
                                    <li>Nar bilaget er attestert, klikk <strong>Godkjenn</strong></li>
                                    <li>Systemet oppretter automatisk:
                                        <ul>
                                            <li>Leverandorfaktura i leverandorreskontro</li>
                                            <li>Regnskapsbilag med korrekt kontering</li>
                                        </ul>
                                    </li>
                                    <li>Bilaget er na bokfort og klart for betaling</li>
                                </ol>

                                <h4>Smart kontoforslag</h4>
                                <p>Systemet larer av tidligere posteringer:</p>
                                <ul>
                                    <li>Nar du godkjenner et bilag, husker systemet hvilken konto du brukte</li>
                                    <li>Neste gang samme leverandor sender faktura, foreslås samme konto</li>
                                    <li>Systemet ser ogsa pa nokkelord i beskrivelsen for a gi bedre forslag</li>
                                </ul>

                                <h4>Avvise bilag</h4>
                                <p>Hvis et bilag ikke skal bokfores:</p>
                                <ol>
                                    <li>Apne bilaget</li>
                                    <li>Klikk <strong>Avvis</strong></li>
                                    <li>Oppgi en grunn for avvisningen</li>
                                    <li>Bilaget flyttes til avviste</li>
                                </ol>

                                <h4>Tolke pa nytt</h4>
                                <p>Hvis AI-tolkningen feilet eller ga darlig resultat:</p>
                                <ol>
                                    <li>Finn bilaget i listen</li>
                                    <li>Klikk pa menyknappen</li>
                                    <li>Velg <strong>Tolk pa nytt</strong></li>
                                    <li>Bilaget sendes til ny AI-tolkning</li>
                                </ol>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Tips</flux:callout.heading>
                                    <flux:callout.text>For best resultat, last opp tydelige PDF-er eller bilder. Skanninger med hoy opplosning gir bedre AI-tolkning enn uskarpe bilder.</flux:callout.text>
                                </flux:callout>

                                <h4>Statuser</h4>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="zinc">Venter</flux:badge>
                                            <span>Bilaget er lastet opp og venter pa tolkning</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="blue">Tolkes</flux:badge>
                                            <span>AI analyserer bilaget</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="purple">Tolket</flux:badge>
                                            <span>Klar for gjennomgang og attestering</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="yellow">Attestert</flux:badge>
                                            <span>Kontrollert, venter pa endelig godkjenning</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="green">Godkjent</flux:badge>
                                            <span>Godkjent og bokfort</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="red">Avvist</flux:badge>
                                            <span>Avvist med begrunnelse</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Rapporter --}}
                    <flux:card id="rapporter" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.chart-bar class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Rapporter</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Rapportmodulen gir deg innsikt i okonomien.</p>

                                <h4>Hovedbok</h4>
                                <p>Viser alle transaksjoner pa en eller flere konti i en periode:</p>
                                <ul>
                                    <li>Velg periode (fra/til-dato)</li>
                                    <li>Filtrer eventuelt pa spesifikk konto</li>
                                    <li>Se alle posteringer med bilagsreferanse</li>
                                </ul>

                                <h4>Bilagsjournal</h4>
                                <p>Kronologisk liste over alle bilag:</p>
                                <ul>
                                    <li>Velg periode</li>
                                    <li>Se bilagsnummer, dato, beskrivelse og belop</li>
                                    <li>Klikk pa bilag for detaljer</li>
                                </ul>

                                <h4>Saldobalanse</h4>
                                <p>Saldo for alle konti pa en gitt dato:</p>
                                <ul>
                                    <li>Velg balansedato</li>
                                    <li>Se inngaende balanse, bevegelse og utgaende balanse</li>
                                    <li>Verifiser at debet = kredit</li>
                                </ul>

                                <h4>Resultatregnskap</h4>
                                <p>Viser inntekter og kostnader for en periode:</p>
                                <ul>
                                    <li>Velg periode</li>
                                    <li>Se driftsinntekter (klasse 3)</li>
                                    <li>Se driftskostnader (klasse 4-7)</li>
                                    <li>Se finansposter (klasse 8)</li>
                                    <li>Resultat for ar beregnes automatisk</li>
                                </ul>

                                <h4>Balanse</h4>
                                <p>Viser eiendeler, gjeld og egenkapital:</p>
                                <ul>
                                    <li>Velg balansedato</li>
                                    <li>Se eiendeler (klasse 1)</li>
                                    <li>Se egenkapital og gjeld (klasse 2)</li>
                                    <li>Kontroller at balansen balanserer</li>
                                </ul>
                            </div>
                        </div>
                    </flux:card>

                    {{-- MVA-meldinger --}}
                    <flux:card id="mva" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.document-chart-bar class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">MVA-meldinger</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>MVA-modulen hjelper deg med a rapportere merverdiavgift til Skatteetaten via Altinn.</p>

                                <h4>Perioder</h4>
                                <p>Systemet stotter tomanedlig (terminvis) rapportering:</p>
                                <div class="not-prose my-4">
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded text-center">1. termin: Jan-Feb</div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded text-center">2. termin: Mar-Apr</div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded text-center">3. termin: Mai-Jun</div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded text-center">4. termin: Jul-Aug</div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded text-center">5. termin: Sep-Okt</div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded text-center">6. termin: Nov-Des</div>
                                    </div>
                                </div>

                                <h4>Opprette MVA-melding</h4>
                                <ol>
                                    <li>Ga til <strong>Okonomi-panelet > MVA-meldinger</strong></li>
                                    <li>Klikk <strong>Ny MVA-melding</strong></li>
                                    <li>Velg ar og periode</li>
                                    <li>Klikk <strong>Opprett</strong></li>
                                </ol>

                                <h4>Beregne MVA</h4>
                                <ol>
                                    <li>Apne MVA-meldingen</li>
                                    <li>Klikk <strong>Beregn MVA</strong></li>
                                    <li>Systemet henter data fra fakturaer og leverandorfakturaer</li>
                                    <li>Kontroller belopene</li>
                                </ol>

                                <h4>MVA-koder</h4>
                                <p>Systemet bruker standard norske MVA-koder for alminnelig naring:</p>

                                <div class="not-prose my-4">
                                    <div class="space-y-4">
                                        <div>
                                            <h5 class="font-medium text-zinc-900 dark:text-white mb-2">Utgaende MVA (salg)</h5>
                                            <div class="space-y-1 text-sm">
                                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                                    <span>Kode 3 - Hoy sats</span>
                                                    <span class="font-mono">25%</span>
                                                </div>
                                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                                    <span>Kode 31 - Middels sats</span>
                                                    <span class="font-mono">15%</span>
                                                </div>
                                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                                    <span>Kode 33 - Lav sats</span>
                                                    <span class="font-mono">12%</span>
                                                </div>
                                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                                    <span>Kode 5 - Fritatt</span>
                                                    <span class="font-mono">0%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <h5 class="font-medium text-zinc-900 dark:text-white mb-2">Inngaende MVA (kjop - fradrag)</h5>
                                            <div class="space-y-1 text-sm">
                                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                                    <span>Kode 1 - Hoy sats</span>
                                                    <span class="font-mono">25%</span>
                                                </div>
                                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                                    <span>Kode 11 - Middels sats</span>
                                                    <span class="font-mono">15%</span>
                                                </div>
                                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                                    <span>Kode 13 - Lav sats</span>
                                                    <span class="font-mono">12%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h4>Manuell justering</h4>
                                <p>Du kan overstyre beregnede belop:</p>
                                <ol>
                                    <li>Klikk pa blyant-ikonet ved linjen</li>
                                    <li>Endre grunnlag og/eller avgift</li>
                                    <li>Legg til merknad for a forklare avviket</li>
                                    <li>Lagre</li>
                                </ol>

                                <h4>Merknad og vedlegg</h4>
                                <ul>
                                    <li>Legg til merknad som sendes med meldingen</li>
                                    <li>Last opp vedlegg (dokumentasjon, beregninger, etc.)</li>
                                </ul>

                                <h4>Sende meldingen</h4>
                                <ol>
                                    <li>Kontroller at alle belop er korrekte</li>
                                    <li>Klikk <strong>Merk som sendt</strong></li>
                                    <li>Logg inn i Altinn og send meldingen der</li>
                                    <li>Legg inn Altinn-referansen du mottar</li>
                                    <li>Merk som godkjent nar du far bekreftelse</li>
                                </ol>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Viktig</flux:callout.heading>
                                    <flux:callout.text>Systemet genererer kun MVA-oppgaven. Du ma fortsatt logge inn i Altinn for a sende den offisielt til Skatteetaten.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Aksjonaerregister --}}
                    <flux:card id="aksjonaerregister" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.user-group class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Aksjonaerregister</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Aksjonaerregisteret holder oversikt over selskapets aksjonaerer, aksjeklasser, transaksjoner og utbytte. Data fra registeret brukes til a generere aksjonaerregisteroppgaven (RF-1086) som sendes til Skatteetaten.</p>

                                <h4>Aksjonaerer</h4>
                                <p>Registrer alle aksjonaerer i selskapet:</p>
                                <ol>
                                    <li>Ga til <strong>Arsoppgjor > Aksjonaerregister</strong></li>
                                    <li>Klikk <strong>Ny aksjonaer</strong></li>
                                    <li>Velg type: Person eller Selskap</li>
                                    <li>Fyll inn identifikasjon (fodselsnummer eller org.nr)</li>
                                    <li>Legg til navn og adresse</li>
                                </ol>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Kobling til kontakter</flux:callout.heading>
                                    <flux:callout.text>Aksjonaerer kan kobles til eksisterende kontakter i systemet for a gjenbruke adresseinformasjon.</flux:callout.text>
                                </flux:callout>

                                <h4>Aksjeklasser</h4>
                                <p>Definer selskapets aksjeklasser:</p>
                                <ul>
                                    <li><strong>Navn</strong> - F.eks. A-aksjer, B-aksjer</li>
                                    <li><strong>ISIN</strong> - Internasjonal verdipapiridentifikator</li>
                                    <li><strong>Palydende</strong> - Nominell verdi per aksje</li>
                                    <li><strong>Totalt antall</strong> - Antall aksjer i klassen</li>
                                    <li><strong>Stemmerett</strong> - Har aksjene stemmerett?</li>
                                    <li><strong>Utbytterett</strong> - Har aksjene rett til utbytte?</li>
                                </ul>

                                <h4>Aksjeinnehav</h4>
                                <p>Registrer eierandeler:</p>
                                <ul>
                                    <li>Velg aksjonaer og aksjeklasse</li>
                                    <li>Angi antall aksjer</li>
                                    <li>Registrer inngangsverdi (anskaffelseskost)</li>
                                    <li>Sett ervervsdato og -mate (stiftelse, kjop, arv, gave)</li>
                                </ul>

                                <h4>Aksjetransaksjoner</h4>
                                <p>Alle endringer i eierskap ma registreres:</p>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="blue">Stiftelse</flux:badge>
                                            <span>Tildeling ved selskapsstiftelse</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="green">Emisjon</flux:badge>
                                            <span>Kapitalforhoyelse med nye aksjer</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="yellow">Overdragelse</flux:badge>
                                            <span>Kjop/salg mellom aksjonaerer</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="purple">Splitt</flux:badge>
                                            <span>Oppsplittes i flere aksjer</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="red">Innlosning</flux:badge>
                                            <span>Selskapet kjoper tilbake aksjer</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>Utbytte</h4>
                                <p>Registrer utbyttevedtak:</p>
                                <ol>
                                    <li>Ga til <strong>Utbytte</strong>-fanen</li>
                                    <li>Klikk <strong>Nytt utbytte</strong></li>
                                    <li>Velg aksjeklasse</li>
                                    <li>Angi belop per aksje</li>
                                    <li>Sett vedtaksdato og utbetalingsdato</li>
                                </ol>
                                <p>Systemet beregner automatisk totalbelop og fordeling per aksjonaer.</p>

                                <h4>Arsrapport (RF-1086)</h4>
                                <p>Generer aksjonaerregisteroppgaven:</p>
                                <ol>
                                    <li>Ga til <strong>Rapporter</strong>-fanen</li>
                                    <li>Velg ar</li>
                                    <li>Klikk <strong>Opprett rapport</strong></li>
                                    <li>Systemet samler data fra registeret</li>
                                    <li>Generer XML for innsending via Altinn</li>
                                </ol>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Frist</flux:callout.heading>
                                    <flux:callout.text>Aksjonaerregisteroppgaven skal sendes til Skatteetaten innen 31. januar aret etter inntektsaret.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Skatt --}}
                    <flux:card id="skatt" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-rose-100 dark:bg-rose-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.receipt-percent class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Skatt</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Skattemodulen hjelper deg med a beregne skattepliktig inntekt, handtere permanente og midlertidige forskjeller, og generere skattemeldingen (RF-1028).</p>

                                <h4>Skattemessige justeringer</h4>
                                <p>Forskjeller mellom regnskapsmessig og skattemessig behandling:</p>

                                <div class="not-prose my-4">
                                    <div class="space-y-3">
                                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                            <div class="font-medium text-red-800 dark:text-red-200 mb-1">Permanente forskjeller</div>
                                            <div class="text-sm text-red-700 dark:text-red-300">Forskjeller som aldri reverseres skattemessig</div>
                                            <ul class="text-sm text-red-600 dark:text-red-400 mt-2 space-y-1">
                                                <li>Representasjonskostnader (ikke fradrag)</li>
                                                <li>Boter og gebyrer (ikke fradrag)</li>
                                                <li>Gaver over fradragsgrense</li>
                                                <li>Ikke-fradragsberettigede kostnader</li>
                                            </ul>
                                        </div>
                                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                            <div class="font-medium text-yellow-800 dark:text-yellow-200 mb-1">Midlertidige forskjeller</div>
                                            <div class="text-sm text-yellow-700 dark:text-yellow-300">Forskjeller som reverseres over tid</div>
                                            <ul class="text-sm text-yellow-600 dark:text-yellow-400 mt-2 space-y-1">
                                                <li>Avskrivningsforskjeller</li>
                                                <li>Urealiserte gevinster/tap</li>
                                                <li>Avsetninger (garantier, tap pa fordringer)</li>
                                                <li>Underskudd til fremforing</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <h4>Utsatt skatt</h4>
                                <p>Beregning av utsatt skattefordel og utsatt skatteforpliktelse:</p>
                                <ol>
                                    <li>Ga til <strong>Arsoppgjor > Skatt > Utsatt skatt</strong></li>
                                    <li>Registrer midlertidige forskjeller per kategori</li>
                                    <li>Systemet beregner utsatt skatt (22%)</li>
                                    <li>Se netto utsatt skattefordel eller -forpliktelse</li>
                                </ol>

                                <div class="not-prose my-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                            <div class="font-medium text-green-800 dark:text-green-200">Utsatt skattefordel</div>
                                            <div class="text-sm text-green-600 dark:text-green-400">Eiendel - fremtidig skattebesparelse</div>
                                        </div>
                                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                            <div class="font-medium text-red-800 dark:text-red-200">Utsatt skatteforpliktelse</div>
                                            <div class="text-sm text-red-600 dark:text-red-400">Gjeld - fremtidig skattebelastning</div>
                                        </div>
                                    </div>
                                </div>

                                <h4>Saldoavskrivning</h4>
                                <p>Skattemessige avskrivninger beregnes pa saldogrunnlag:</p>
                                <div class="not-prose my-4">
                                    <div class="space-y-1 text-sm">
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe a - Kontormaskiner</span>
                                            <span class="font-mono">30%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe b - Ervervet goodwill</span>
                                            <span class="font-mono">20%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe c - Varebiler, lastebiler</span>
                                            <span class="font-mono">24%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe d - Personbiler, maskiner</span>
                                            <span class="font-mono">20%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe e - Skip, fartoy</span>
                                            <span class="font-mono">14%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe f - Fly, helikopter</span>
                                            <span class="font-mono">12%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe h - Bygg og anlegg</span>
                                            <span class="font-mono">4%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe i - Forretningsbygg</span>
                                            <span class="font-mono">2%</span>
                                        </div>
                                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <span>Gruppe j - Tekniske installasjoner</span>
                                            <span class="font-mono">10%</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>Skattemelding (RF-1028)</h4>
                                <p>Generer skattemeldingen:</p>
                                <ol>
                                    <li>Ga til <strong>Arsoppgjor > Skatt > Skattemelding</strong></li>
                                    <li>Velg regnskapsar</li>
                                    <li>Klikk <strong>Opprett skattemelding</strong></li>
                                    <li>Systemet henter data fra regnskap og justeringer</li>
                                    <li>Kontroller beregningen</li>
                                    <li>Generer XML for innsending</li>
                                </ol>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Frist</flux:callout.heading>
                                    <flux:callout.text>Skattemeldingen for aksjeselskaper skal sendes til Skatteetaten innen 31. mai aret etter inntektsaret.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Arsregnskap --}}
                    <flux:card id="arsregnskap" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-sky-100 dark:bg-sky-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.document-text class="w-5 h-5 text-sky-600 dark:text-sky-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Arsregnskap</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Arsregnskapsmodulen hjelper deg med a utarbeide arsregnskapet som skal sendes til Regnskapsregisteret. Modulen stotter XBRL-format for elektronisk innsending.</p>

                                <h4>Opprette arsregnskap</h4>
                                <ol>
                                    <li>Ga til <strong>Arsoppgjor > Arsregnskap</strong></li>
                                    <li>Klikk <strong>Nytt arsregnskap</strong></li>
                                    <li>Velg regnskapsar</li>
                                    <li>Systemet henter automatisk nokkeltall fra regnskapet</li>
                                    <li>Standard noter opprettes automatisk</li>
                                </ol>

                                <h4>Selskapsstorrelse</h4>
                                <p>Kravene til arsregnskapet avhenger av selskapets storrelse:</p>
                                <div class="not-prose my-4">
                                    <div class="space-y-3">
                                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                            <div class="font-medium text-green-800 dark:text-green-200">Sma foretak</div>
                                            <div class="text-sm text-green-700 dark:text-green-300">Forenklede krav til noter og oppstillinger</div>
                                            <ul class="text-xs text-green-600 dark:text-green-400 mt-2 space-y-1">
                                                <li>Salgsinntekt < 70 MNOK</li>
                                                <li>Balansesum < 35 MNOK</li>
                                                <li>Ansatte < 50 arsverk</li>
                                            </ul>
                                        </div>
                                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                            <div class="font-medium text-yellow-800 dark:text-yellow-200">Mellomstore foretak</div>
                                            <div class="text-sm text-yellow-700 dark:text-yellow-300">Krever kontantstromoppstilling</div>
                                        </div>
                                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                            <div class="font-medium text-red-800 dark:text-red-200">Store foretak</div>
                                            <div class="text-sm text-red-700 dark:text-red-300">Fulle krav, revisjonsplikt</div>
                                        </div>
                                    </div>
                                </div>

                                <h4>Noter</h4>
                                <p>Arsregnskapet skal inneholde noter som forklarer tallene:</p>
                                <ul>
                                    <li><strong>Regnskapsprinsipper</strong> - Anvendte prinsipper (pakrevd)</li>
                                    <li><strong>Ansatte</strong> - Lonnkostnader og antall (pakrevd)</li>
                                    <li><strong>Varige driftsmidler</strong> - Avskrivninger og bevegelser</li>
                                    <li><strong>Aksjekapital</strong> - Eierstruktur</li>
                                    <li><strong>Egenkapital</strong> - Bevegelser i perioden</li>
                                    <li><strong>Gjeld</strong> - Langsiktig og kortsiktig</li>
                                    <li><strong>Skatt</strong> - Betalbar og utsatt skatt</li>
                                    <li><strong>Naerstaende parter</strong> - Transaksjoner</li>
                                    <li><strong>Hendelser etter balansedagen</strong> - Vesentlige forhold</li>
                                </ul>

                                <h4>Redigere noter</h4>
                                <ol>
                                    <li>Apne arsregnskapet</li>
                                    <li>Klikk pa <strong>Noter</strong>-knappen</li>
                                    <li>Velg noten du vil redigere</li>
                                    <li>Skriv eller rediger innholdet</li>
                                    <li>Lagre endringene</li>
                                </ol>

                                <h4>Kontantstromoppstilling</h4>
                                <p>For mellomstore og store foretak:</p>
                                <ul>
                                    <li><strong>Drift</strong> - Kontantstrom fra operasjonelle aktiviteter</li>
                                    <li><strong>Investering</strong> - Kjop/salg av anleggsmidler</li>
                                    <li><strong>Finansiering</strong> - Lan, utbytte, kapitalendringer</li>
                                </ul>

                                <h4>Arbeidsflyt</h4>
                                <div class="not-prose my-4">
                                    <div class="flex flex-wrap items-center gap-2 text-sm">
                                        <flux:badge color="zinc">Utkast</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="blue">Godkjent</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="purple">Sendt inn</flux:badge>
                                        <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                        <flux:badge color="green">Akseptert</flux:badge>
                                    </div>
                                </div>

                                <h4>XBRL-generering</h4>
                                <p>Arsregnskapet sendes til Regnskapsregisteret i XBRL-format:</p>
                                <ol>
                                    <li>Fullfør alle noter</li>
                                    <li>Klikk <strong>Valider</strong> for a sjekke at alt er komplett</li>
                                    <li>Klikk <strong>Godkjenn</strong> for styregodkjenning</li>
                                    <li>Klikk <strong>Send til Altinn</strong> for a generere XBRL</li>
                                </ol>

                                <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                                    <flux:callout.heading>Frist</flux:callout.heading>
                                    <flux:callout.text>Arsregnskapet skal sendes til Regnskapsregisteret innen 31. juli aret etter regnskapsaret (for selskaper med kalenderaret som regnskapsar).</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Altinn --}}
                    <flux:card id="altinn" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.paper-airplane class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Altinn-integrasjon</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Altinn-dashboardet gir deg oversikt over alle obligatoriske innsendinger og deres status. Herfra kan du folge med pa frister og sende inn elektronisk.</p>

                                <h4>Oversikt over innsendinger</h4>
                                <div class="not-prose my-4">
                                    <div class="space-y-3">
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="font-medium text-zinc-900 dark:text-white">Aksjonaerregisteroppgaven (RF-1086)</span>
                                                <span class="text-sm text-zinc-500">31. januar</span>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400">Rapport til Skatteetaten om aksjonaerforhold</div>
                                        </div>
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="font-medium text-zinc-900 dark:text-white">Skattemelding (RF-1028)</span>
                                                <span class="text-sm text-zinc-500">31. mai</span>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400">Naringoppgave og skattemelding til Skatteetaten</div>
                                        </div>
                                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="font-medium text-zinc-900 dark:text-white">Arsregnskap (XBRL)</span>
                                                <span class="text-sm text-zinc-500">31. juli</span>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400">Arsregnskap til Regnskapsregisteret</div>
                                        </div>
                                    </div>
                                </div>

                                <h4>Dashboardet viser</h4>
                                <ul>
                                    <li><strong>Kommende frister</strong> - Innsendinger som ikke er fullfort</li>
                                    <li><strong>Forfalte frister</strong> - Innsendinger som er pa overtid</li>
                                    <li><strong>Statistikk</strong> - Antall sendt, godkjent, avvist</li>
                                    <li><strong>Historikk</strong> - Tidligere innsendinger</li>
                                </ul>

                                <h4>Status pa innsending</h4>
                                <div class="not-prose my-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge variant="outline">Ikke startet</flux:badge>
                                            <span>Ingen data er klargjort</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="yellow">Under arbeid</flux:badge>
                                            <span>Data er under utarbeidelse</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="blue">Klar for innsending</flux:badge>
                                            <span>Godkjent og klar til a sendes</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="purple">Sendt inn</flux:badge>
                                            <span>Sendt til mottaker, venter pa svar</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="green">Akseptert</flux:badge>
                                            <span>Godkjent av mottaker</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                            <flux:badge color="red">Avvist</flux:badge>
                                            <span>Feil i innsendingen, ma korrigeres</span>
                                        </div>
                                    </div>
                                </div>

                                <h4>Fristpaminnelser</h4>
                                <p>Systemet varsler om kommende frister:</p>
                                <ul>
                                    <li><strong>30 dager for</strong> - Forste paminning</li>
                                    <li><strong>14 dager for</strong> - Oppfolging</li>
                                    <li><strong>7 dager for</strong> - Hastevarsel</li>
                                    <li><strong>1 dag for</strong> - Kritisk frist</li>
                                </ul>

                                <h4>Innsendingshistorikk</h4>
                                <p>Se alle tidligere innsendinger:</p>
                                <ul>
                                    <li>Dato og klokkeslett for innsending</li>
                                    <li>Status (akseptert/avvist)</li>
                                    <li>Altinn-referanse</li>
                                    <li>Eventuelle feilmeldinger</li>
                                </ul>

                                <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                                    <flux:callout.heading>Elektronisk signering</flux:callout.heading>
                                    <flux:callout.text>Innsending til Altinn krever virksomhetssertifikat eller annen godkjent autentiseringsmetode. Kontakt administrator for oppsett.</flux:callout.text>
                                </flux:callout>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Innstillinger --}}
                    <flux:card id="innstillinger" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.cog-6-tooth class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Innstillinger</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Innstillingssiden er organisert i faner for enkel navigering.</p>

                                <h4>Min konto</h4>
                                <p>Personlige innstillinger for din brukerkonto:</p>
                                <ul>
                                    <li><strong>Profilinformasjon</strong> - Se navn og e-postadresse</li>
                                    <li><strong>Sikkerhet</strong> - Endre passord</li>
                                    <li><strong>Utseende</strong> - Velg lyst, morkt eller automatisk tema</li>
                                    <li><strong>Varsler</strong> - Administrer e-post- og push-varsler</li>
                                </ul>

                                <h4>Selskap (kun for eiere/ledere)</h4>
                                <p>Hvis du er eier eller leder av selskapet, vil du se en ekstra fane for selskapsinnstillinger:</p>
                                <ul>
                                    <li>Rediger selskapsinformasjon (navn, organisasjonsnummer, adresse)</li>
                                    <li>Last opp firmalogo</li>
                                    <li>Konfigurer bankopplysninger</li>
                                    <li>Sett standardverdier for betalingsbetingelser</li>
                                    <li>Tilpass dokumentfooter og vilkar</li>
                                </ul>

                                <h4>Brukere (kun for eiere/ledere)</h4>
                                <p>Administrer brukerne i ditt selskap:</p>
                                <ul>
                                    <li>Inviter nye brukere via e-post</li>
                                    <li>Tildel roller (medlem eller leder)</li>
                                    <li>Fjern brukere fra selskapet</li>
                                </ul>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Avdelinger --}}
                    <flux:card id="avdelinger" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.building-library class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Avdelinger</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Avdelinger lar deg gruppere brukere og spore kostnader og inntekter per organisatorisk enhet. Avdelingsdimensjonen folger med pa alle bilag i hovedboken.</p>

                                <h4>Opprette avdelinger</h4>
                                <p>For a opprette avdelinger:</p>
                                <ol>
                                    <li>Ga til <strong>Innstillinger</strong> i sidemenyen</li>
                                    <li>Velg fanen <strong>Avdelinger</strong></li>
                                    <li>Klikk <strong>Ny avdeling</strong></li>
                                    <li>Fyll inn avdelingskode (f.eks. "ADM", "SAL") og navn</li>
                                </ol>

                                <h4>Tildele brukere til avdelinger</h4>
                                <p>Nar avdelinger er aktivert kan du tildele brukere til avdelinger:</p>
                                <ol>
                                    <li>Ga til <strong>Innstillinger → Brukere</strong></li>
                                    <li>Klikk pa en bruker for a redigere</li>
                                    <li>Velg avdeling fra nedtrekkslisten</li>
                                </ol>

                                <h4>Automatisk propagering</h4>
                                <p>Nar en bruker har en avdeling, vil avdelingen automatisk folge med pa:</p>
                                <ul>
                                    <li>Tilbud brukeren oppretter</li>
                                    <li>Ordrer konvertert fra tilbud</li>
                                    <li>Fakturaer konvertert fra ordrer</li>
                                    <li>Alle bilagslinjer som bokfores</li>
                                </ul>

                                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                    <p class="text-blue-800 dark:text-blue-200 text-sm mb-0">
                                        <strong>Tips:</strong> Du ma aktivere avdelingsfunksjonen under <strong>Innstillinger → Regnskap</strong> for at avdelinger skal vare tilgjengelig.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Regnskapsinnstillinger --}}
                    <flux:card id="regnskapsinnstillinger" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.calculator class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Regnskapsinnstillinger</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Regnskapsinnstillinger lar deg konfigurere regnskapsfunksjoner per selskap.</p>

                                <h4>Tilgjengelige innstillinger</h4>
                                <ul>
                                    <li><strong>Avdelinger aktivert</strong> - Sla pa for a bruke avdelinger som konteringsdimensjon</li>
                                    <li><strong>Krev avdeling pa bilag</strong> - Nar aktivert ma alle bilagslinjer ha en avdeling</li>
                                    <li><strong>Standardavdeling</strong> - Velg en avdeling som brukes automatisk for nye bilag</li>
                                </ul>

                                <h4>Aktivere avdelinger</h4>
                                <ol>
                                    <li>Ga til <strong>Innstillinger → Regnskap</strong></li>
                                    <li>Sla pa <strong>Avdelinger aktivert</strong></li>
                                    <li>Klikk <strong>Lagre innstillinger</strong></li>
                                </ol>
                                <p>Etter aktivering kan du opprette avdelinger under fanen <strong>Avdelinger</strong> og tildele brukere til avdelinger under <strong>Brukere</strong>.</p>

                                <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                    <p class="text-amber-800 dark:text-amber-200 text-sm mb-0">
                                        <strong>Merk:</strong> Hvis du deaktiverer avdelinger etter at data er registrert, vil eksisterende avdelingsinformasjon beholdes i databasen, men nye posteringer vil ikke fa avdeling.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Kontoplan --}}
                    <flux:card id="kontoplan" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.table-cells class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Kontoplan</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Kontoplanen er grunnlaget for regnskapet ditt. Konrad stotter NS 4102 - Norsk standard kontoplan for aksjeselskaper.</p>

                                <h4>Opprette NS 4102 kontoplan</h4>
                                <p>For nye selskaper anbefaler vi a bruke den norske standardkontoplanen:</p>
                                <ol>
                                    <li>Ga til <strong>Okonomi → Kontoplan</strong></li>
                                    <li>Klikk <strong>Opprett NS 4102 kontoplan</strong></li>
                                    <li>Bekreft at du vil opprette kontoplanen</li>
                                </ol>
                                <p>Dette oppretter over 200 forhåndsdefinerte kontoer som dekker de fleste behov for norske aksjeselskaper.</p>

                                <h4>Administrere kontoer</h4>
                                <p>Du kan ogsa opprette og redigere kontoer manuelt:</p>
                                <ul>
                                    <li><strong>Kontonummer</strong> - Firesifret nummer i henhold til NS 4102</li>
                                    <li><strong>Kontonavn</strong> - Beskrivende navn pa kontoen</li>
                                    <li><strong>Kontoklasse</strong> - F.eks. anleggsmidler, kortsiktig gjeld, driftsinntekter</li>
                                    <li><strong>Kontotype</strong> - Eiendel, gjeld, egenkapital, inntekt eller kostnad</li>
                                    <li><strong>MVA-kode</strong> - Koble kontoen til en MVA-kode for automatisk beregning</li>
                                </ul>

                                <h4>Systemkontoer</h4>
                                <p>Noen kontoer er markert som systemkontoer og kan ikke slettes eller endres. Disse brukes av systemet for automatiske posteringer, som:</p>
                                <ul>
                                    <li>Kundefordringer (1500)</li>
                                    <li>Leverandorgjeld (2400)</li>
                                    <li>Utgaende MVA (2700)</li>
                                    <li>Inngaende MVA (2710)</li>
                                </ul>

                                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                    <p class="text-blue-800 dark:text-blue-200 text-sm mb-0">
                                        <strong>Tips:</strong> Eksisterende kontoer med samme kontonummer vil ikke bli overskrevet nar du oppretter NS 4102 kontoplan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    {{-- Selskap og brukere --}}
                    <flux:card id="selskap" class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                    <flux:icon.building-office-2 class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Selskap og brukere</flux:heading>
                            </div>

                            <div class="prose prose-zinc dark:prose-invert max-w-none">
                                <p>Konrad Office stotter flere selskaper (multi-tenancy). Hver bruker kan tilhore ett eller flere selskaper med ulike roller.</p>

                                <h4>Brukerroller</h4>
                                <p>Det finnes tre roller innenfor et selskap:</p>
                                <ul>
                                    <li><strong>Eier</strong> - Full tilgang til selskapet, kan administrere alle innstillinger og brukere</li>
                                    <li><strong>Leder (Manager)</strong> - Kan administrere selskapsinnstillinger og invitere brukere</li>
                                    <li><strong>Medlem</strong> - Standard bruker med tilgang til selskapets data</li>
                                </ul>

                                <h4>Opprette et selskap</h4>
                                <p>Nar du registrerer deg for forste gang, blir du guidet gjennom opprettelse av ditt forste selskap. Du blir automatisk eier av dette selskapet.</p>

                                <h4>Invitere brukere</h4>
                                <p>Som eier eller leder kan du invitere nye brukere:</p>
                                <ol>
                                    <li>Ga til <strong>Innstillinger</strong> i sidemenyen</li>
                                    <li>Velg fanen <strong>Brukere</strong></li>
                                    <li>Klikk <strong>Inviter bruker</strong></li>
                                    <li>Skriv inn e-postadresse og velg rolle</li>
                                </ol>
                                <p>Hvis brukeren allerede har en konto i Konrad Office, blir de umiddelbart lagt til i selskapet. Nye brukere far en invitasjon pa e-post.</p>

                                <h4>Administrere brukere</h4>
                                <p>I brukerlisten kan du:</p>
                                <ul>
                                    <li>Se alle brukere og deres roller</li>
                                    <li>Endre rolle for eksisterende brukere (unntak: kan ikke endre eierens rolle)</li>
                                    <li>Fjerne brukere fra selskapet</li>
                                </ul>

                                <h4>Selskapsinnstillinger</h4>
                                <p>Under fanen <strong>Selskap</strong> i innstillinger kan du redigere:</p>
                                <ul>
                                    <li><strong>Grunnleggende informasjon</strong> - Selskapsnavn, organisasjonsnummer, MVA-nummer</li>
                                    <li><strong>Kontaktinformasjon</strong> - Adresse, telefon, e-post, nettside</li>
                                    <li><strong>Bankopplysninger</strong> - Banknavn, kontonummer, IBAN, SWIFT</li>
                                    <li><strong>Firmalogo</strong> - Last opp logo som vises pa dokumenter</li>
                                    <li><strong>Standardverdier</strong> - Betalingsfrist (dager), tilbudsgyldighet</li>
                                    <li><strong>Dokumentmaler</strong> - Faktura-/tilbudsvilkar, dokumentfooter</li>
                                </ul>

                                <h4>Bytte mellom selskaper</h4>
                                <p>Hvis du tilhorer flere selskaper, kan du bytte mellom dem via profilmenyen oppe til hoyre. All data du ser vil automatisk filtreres til det valgte selskapet.</p>

                                <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                    <p class="text-amber-800 dark:text-amber-200 text-sm mb-0">
                                        <strong>Merk:</strong> Systemadministratorer (is_admin) har tilgang til administrasjonspanelet hvor de kan se alle selskaper og brukere pa tvers av systemet.
                                    </p>
                                </div>
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
                                Finner du ikke svar pa det du lurer pa? Ta kontakt med oss for support.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <flux:button variant="filled" class="bg-white text-indigo-600 hover:bg-white/90">
                                    <flux:icon.envelope class="w-4 h-4 mr-2" />
                                    Send e-post
                                </flux:button>
                                <flux:button variant="ghost" class="text-white border-white/30 hover:bg-white/10">
                                    <flux:icon.phone class="w-4 h-4 mr-2" />
                                    Ring oss
                                </flux:button>
                            </div>
                        </div>
                    </flux:card>
                </div>
            </div>
        </flux:main>
    </div>
</x-layouts.app>
