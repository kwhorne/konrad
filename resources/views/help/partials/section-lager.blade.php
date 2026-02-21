{{-- Lager --}}
<flux:card id="lager" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.archive-box class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Lager</flux:heading>
            </div>
            <flux:badge color="cyan" size="sm">Lager & Innkjøp</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Lagermodulen gir deg full kontroll over beholdning, bevegelser og innkjøp — med nøkkelinfo for gode beslutninger.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Lageroversikt (dashboard)</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Oversiktssiden samler alt du trenger for å ta raske beslutninger:</p>
                        <ul>
                            <li><strong>KPI-strip</strong> øverst viser lagerverdi, antall produkter, totale enheter, reservert, varer under bestillingspunkt og nullbeholdning</li>
                            <li><strong>Bestillingspunkt-varsel</strong> vises automatisk i rødt når produkter har for lite på lager — med direkte lenke til ny innkjøpsordre</li>
                            <li><strong>Åpne innkjøpsordrer</strong> viser alle ventende bestillinger med leverandør, forventet leveringsdato og beløp. Forsinkede ordrer merkes i rødt</li>
                            <li><strong>Topp 5 etter verdi (ABC-analyse)</strong> viser hvilke produkter som binder mest kapital med prosentandel av total lagerverdi</li>
                            <li><strong>Siste bevegelser</strong> og <strong>siste varemottak</strong> gir umiddelbar oversikt over lageraktivitet</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Lagerbeholdning</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Under <strong>Beholdning</strong> ser du nøyaktig lagerstatus per produkt og lokasjon:</p>
                        <ul>
                            <li><strong>På lager</strong> — faktisk antall i beholdning</li>
                            <li><strong>Reservert</strong> — antall som er knyttet til åpne ordrer</li>
                            <li><strong>Tilgjengelig</strong> — på lager minus reservert (det som kan selges/brukes)</li>
                            <li><strong>Gjennomsnittskost</strong> — vektet gjennomsnittskostnad per enhet</li>
                            <li><strong>Verdi</strong> — beholdning × gjennomsnittskost</li>
                        </ul>
                        <p>Rader med gul bakgrunn er under bestillingspunktet. Bruk filteret <em>Kun under bestillingspunkt</em> for å se kun disse.</p>
                        <p>Sumblokken øverst viser totaler for gjeldende filter — nyttig for å se verdien av en bestemt lokasjon eller produktgruppe.</p>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Bestillingspunkt</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Bestillingspunktet er minimumsantallet du ønsker å ha tilgjengelig. Når tilgjengelig beholdning faller til eller under dette nivået, vises produktet som et varsel på lagerdashbordet.</p>
                        <p>Slik setter du bestillingspunkt på et produkt:</p>
                        <ol>
                            <li>Gå til <strong>Vareregister</strong> og åpne produktet</li>
                            <li>Fyll inn <strong>Bestillingspunkt</strong> under lagerinnstillinger</li>
                            <li>Lagre</li>
                        </ol>
                        <p>Systemet beregner manko automatisk: <em>bestillingspunkt − tilgjengelig = manko</em>.</p>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Lagerjusteringer</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Bruk lagerjustering når du må korrigere beholdningen manuelt — for eksempel etter svinn, tyveri eller feil telling.</p>
                        <ol>
                            <li>Gå til <strong>Lager → Justeringer</strong></li>
                            <li>Velg produkt og lokasjon</li>
                            <li>Angi antall — positivt for å legge til, negativt for å trekke fra</li>
                            <li>Angi enhetskost (valgfritt — brukes til å oppdatere gjennomsnittskostnad)</li>
                            <li>Skriv inn begrunnelse (påkrevd for sporbarhet)</li>
                            <li>Klikk <strong>Lagre justering</strong></li>
                        </ol>
                        <p>Alle justeringer loggføres som transaksjoner og er synlige i transaksjonshistorikken.</p>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Varetelling</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Varetelling brukes for å verifisere at den faktiske beholdningen stemmer med det som er registrert i systemet.</p>
                        <ol>
                            <li>Gå til <strong>Lager → Varetelling</strong> og klikk <strong>Ny varetelling</strong></li>
                            <li>Velg lokasjon(er) og produkter som skal telles</li>
                            <li>Tell fysisk og registrer opptalt antall</li>
                            <li>Systemet beregner avvik (forventet vs. opptalt)</li>
                            <li>Fullfør tellingen — systemet oppdaterer beholdningen og oppretter justeringstransaksjoner automatisk</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Transaksjonshistorikk</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Under <strong>Lager → Transaksjoner</strong> ser du alle lagerbevegelser med full sporbarhet. Du kan filtrere på:</p>
                        <ul>
                            <li>Søkeord (transaksjonsnummer eller produktnavn)</li>
                            <li>Lokasjon</li>
                            <li>Transaksjonstype (mottak, uttak, overføring, justering)</li>
                            <li>Datoperiode</li>
                        </ul>
                        <p>Sumblokken øverst viser netto antall og netto beløp for gjeldende filter — nyttig for å se netto bevegelse i en periode.</p>
                        <div class="not-prose">
                            <div class="grid grid-cols-3 gap-2 text-sm my-4">
                                <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded text-center">
                                    <div class="font-bold text-green-700 dark:text-green-400">Inn</div>
                                    <div class="text-zinc-500 text-xs">Mottak, justering+</div>
                                </div>
                                <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded text-center">
                                    <div class="font-bold text-red-700 dark:text-red-400">Ut</div>
                                    <div class="text-zinc-500 text-xs">Uttak, justering−</div>
                                </div>
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded text-center">
                                    <div class="font-bold text-blue-700 dark:text-blue-400">Overføring</div>
                                    <div class="text-zinc-500 text-xs">Mellom lokasjoner</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Lokasjoner</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Lokasjoner representerer fysiske lagersteder — for eksempel «Hovedlager Oslo», «Sekundærlager» eller «Bil 12».</p>
                        <ul>
                            <li>Opprett lokasjoner under <strong>Lager → Lokasjoner</strong></li>
                            <li>Hver lokasjon kan ha kapasitet og adresse</li>
                            <li>Beholdning spores separat per lokasjon</li>
                            <li>Du kan overføre varer mellom lokasjoner via lagerjustering eller innkjøpsmottak</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Tips for god lagerstyring</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ul>
                            <li><strong>ABC-analyse:</strong> Fokuser oppmerksomheten på A-produktene (høyest verdi). Disse binder mest kapital og bør ha tette bestillingspunkter og hyppigere telling</li>
                            <li><strong>Bestillingspunkt:</strong> Sett dette høyt nok til å dekke forbruk i leveringstiden fra leverandør. Regn: <em>daglig forbruk × leveringstid i dager</em></li>
                            <li><strong>Varetelling:</strong> Tell A-produkter oftere (månedlig), B-produkter kvartalsvis, C-produkter halvårlig</li>
                            <li><strong>Gjennomsnittskost:</strong> Systemet bruker vektet gjennomsnittskost (WAC) — husk å angi enhetskost ved mottak for korrekt lagerverdisetting</li>
                            <li><strong>Reservert beholdning:</strong> Hold øye med høy reservert andel — det kan bety at du snart går tom selv om «på lager»-tallet ser greit ut</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

        </flux:accordion>
    </div>
</flux:card>
