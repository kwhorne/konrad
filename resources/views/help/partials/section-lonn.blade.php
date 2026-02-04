{{-- Lønn --}}
<flux:card id="lonn" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.banknotes class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Lønn</flux:heading>
            </div>
            <flux:badge color="emerald" size="sm">Lønn</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Komplett lønnssystem for norske bedrifter med støtte for alle skatte- og avgiftstyper.
        </flux:text>

        <flux:callout variant="info" icon="arrow-top-right-on-square" class="not-prose mb-6">
            <flux:callout.heading>Eget lønnspanel</flux:callout.heading>
            <flux:callout.text>Lønnsfunksjonene er tilgjengelige via <strong>Lønn</strong> i sidemenyen. Dette krever <strong>lønn</strong> eller <strong>admin</strong>-rolle.</flux:callout.text>
        </flux:callout>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Kom i gang</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>For å komme i gang med lønnsmodulen:</p>
                        <ol>
                            <li><strong>Sett opp ansatte</strong> - Gå til Ansatte og legg inn lønnsopplysninger for hver ansatt</li>
                            <li><strong>Definer lønnsarter</strong> - Konfigurer hvilke lønnsarter bedriften bruker</li>
                            <li><strong>Sett AGA-sone</strong> - Velg riktig arbeidsgiveravgift-sone i innstillinger</li>
                            <li><strong>Opprett lønnskjøring</strong> - Opprett månedlig lønnskjøring og beregn lønn</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Ansatte</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Registrer lønnsopplysninger for hver ansatt:</p>
                        <ul>
                            <li><strong>Lønnstype</strong> - Fastlønn (månedslønn) eller timelønn</li>
                            <li><strong>Stillingsprosent</strong> - Andel av full stilling</li>
                            <li><strong>Skattekort</strong> - Tabelltrekk, prosenttrekk, kildeskatt eller frikort</li>
                            <li><strong>Skattetabell</strong> - Tabellnummer for tabelltrekk (f.eks. 7100)</li>
                            <li><strong>Feriepenger</strong> - Prosentsats og eventuelt 5 uker eller over 60 år</li>
                            <li><strong>OTP</strong> - Obligatorisk tjenestepensjon prosentsats</li>
                            <li><strong>Kontonummer</strong> - For lønnsutbetaling</li>
                        </ul>

                        <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                            <flux:callout.heading>Skattetabeller</flux:callout.heading>
                            <flux:callout.text>Tabellnummeret finner du på skattekortet. Første siffer (6, 7 eller 8) angir klasseforskjell, resten er tabellnummer.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Lønnsarter</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Lønnsarter definerer hvordan ulike lønnskomponenter behandles:</p>
                        <ul>
                            <li><strong>Fastlønn</strong> - Grunnlønn per måned</li>
                            <li><strong>Timelønn</strong> - Lønn basert på timer</li>
                            <li><strong>Overtid</strong> - Overtidstillegg (50% eller 100%)</li>
                            <li><strong>Bonus</strong> - Bonuser og provisjon</li>
                            <li><strong>Tillegg</strong> - Andre tillegg</li>
                            <li><strong>Trekk</strong> - Lønnstrekk</li>
                        </ul>

                        <p>For hver lønnsart kan du angi:</p>
                        <ul>
                            <li>Om den er skattepliktig</li>
                            <li>Om den inngår i AGA-grunnlaget</li>
                            <li>Om den inngår i feriepengegrunnlaget</li>
                            <li>Om den inngår i OTP-grunnlaget</li>
                            <li>A-meldingskode for rapportering</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Lønnskjøring</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Slik kjører du lønn:</p>
                        <ol>
                            <li>Gå til <strong>Lønnskjøring</strong></li>
                            <li>Klikk <strong>Ny lønnskjøring</strong></li>
                            <li>Velg år, måned og utbetalingsdato</li>
                            <li>Klikk <strong>Beregn</strong> for å beregne lønn for alle ansatte</li>
                            <li>Gjennomgå beregningene</li>
                            <li>Godkjenn lønnskjøringen</li>
                            <li>Marker som utbetalt når lønnen er overført</li>
                        </ol>

                        <p><strong>Beregningsflyt:</strong></p>
                        <ul>
                            <li>Grunnlønn beregnes fra ansattoppsett</li>
                            <li>Timer hentes fra godkjente timesedler</li>
                            <li>Forskuddstrekk beregnes basert på skattekort</li>
                            <li>Feriepenger avsetning beregnes</li>
                            <li>Arbeidsgiveravgift beregnes</li>
                            <li>OTP-bidrag beregnes</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Skattetyper</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Systemet støtter alle norske skattetyper:</p>

                        <p><strong>Tabelltrekk:</strong></p>
                        <ul>
                            <li>Standardmetoden for de fleste ansatte</li>
                            <li>Skatt beregnes fra skattetabeller</li>
                            <li>Tabellnummer fra skattekort (f.eks. 7100, 7101)</li>
                        </ul>

                        <p><strong>Prosenttrekk:</strong></p>
                        <ul>
                            <li>Fast prosentsats på all inntekt</li>
                            <li>Brukes ofte for biinntekt</li>
                        </ul>

                        <p><strong>Kildeskatt:</strong></p>
                        <ul>
                            <li>25% flat skatt</li>
                            <li>For utenlandske arbeidstakere på kortidsopphold</li>
                        </ul>

                        <p><strong>Frikort:</strong></p>
                        <ul>
                            <li>Skattefritt opp til frikortbeløpet</li>
                            <li>50% skatt på overskytende</li>
                            <li>Systemet sporer brukt beløp automatisk</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Feriepenger</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Feriepenger beregnes automatisk:</p>
                        <ul>
                            <li><strong>10,2%</strong> - Standard sats (4 uker + 1 dag ferie)</li>
                            <li><strong>12,0%</strong> - Tariffestet 5 ukers ferie</li>
                            <li><strong>12,5%</strong> - Over 60 år med 4 uker ferie</li>
                            <li><strong>14,3%</strong> - Over 60 år med 5 ukers ferie</li>
                        </ul>

                        <p>Feriepenger avsetning:</p>
                        <ul>
                            <li>Beregnes av feriepengegrunnlaget hver måned</li>
                            <li>Akkumuleres gjennom året</li>
                            <li>Utbetales normalt i juni året etter opptjening</li>
                        </ul>

                        <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                            <flux:callout.heading>Feriepengegrunnlag</flux:callout.heading>
                            <flux:callout.text>Ikke alle lønnsarter inngår i feriepengegrunnlaget. Naturalytelser og utgiftsgodtgjørelser er typisk unntatt.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Arbeidsgiveravgift (AGA)</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>AGA beregnes basert på bedriftens sone:</p>

                        <div class="not-prose my-4">
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                    <span>Sone 1</span>
                                    <span class="font-mono">14,1%</span>
                                </div>
                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                    <span>Sone 1a (fribeløp 500 000 kr)</span>
                                    <span class="font-mono">10,6%</span>
                                </div>
                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                    <span>Sone 2</span>
                                    <span class="font-mono">10,6%</span>
                                </div>
                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                    <span>Sone 3</span>
                                    <span class="font-mono">6,4%</span>
                                </div>
                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                    <span>Sone 4</span>
                                    <span class="font-mono">5,1%</span>
                                </div>
                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                    <span>Sone 4a</span>
                                    <span class="font-mono">7,9%</span>
                                </div>
                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                    <span>Sone 5 (Finnmark/Nord-Troms)</span>
                                    <span class="font-mono">0%</span>
                                </div>
                            </div>
                        </div>

                        <p>AGA beregnes av bruttølønn pluss feriepenger-avsetning.</p>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">OTP (Obligatorisk tjenestepensjon)</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>OTP-bidrag beregnes automatisk:</p>
                        <ul>
                            <li><strong>Minimum 2%</strong> - Lovpålagt minimum</li>
                            <li><strong>Maksimum 7%</strong> - Høyeste tillatte sats</li>
                            <li><strong>Tak på 12G</strong> - OTP beregnes kun av lønn opp til 12G (ca. 1,5 MNOK)</li>
                        </ul>

                        <p>G (Grunnbeløpet) oppdateres årlig. Per mai 2025 er G = 130 160 kr.</p>

                        <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                            <flux:callout.heading>Lovkrav</flux:callout.heading>
                            <flux:callout.text>Alle bedrifter med ansatte må ha OTP-ordning. Minimum 2% av lønn mellom 1G og 12G skal innbetales til pensjonsleverandør.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">A-melding</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>A-meldingen rapporterer lønns- og ansettelsesforhold til myndighetene:</p>
                        <ul>
                            <li><strong>Frist</strong> - 5. i måneden etter lønnsperioden</li>
                            <li><strong>Innhold</strong> - Lønn, skatt, AGA og arbeidsforhold</li>
                            <li><strong>Mottaker</strong> - Skatteetaten, NAV og SSB</li>
                        </ul>

                        <p>Systemet forbereder data for A-melding. Full integrasjon med Altinn kommer snart.</p>

                        <flux:callout variant="info" icon="calendar" class="not-prose my-4">
                            <flux:callout.heading>Viktig frist</flux:callout.heading>
                            <flux:callout.text>A-meldingen må sendes innen den 5. i måneden etter lønnskjøringen. For januar-lønn er fristen 5. februar.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
