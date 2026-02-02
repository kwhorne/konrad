{{-- Økonomi --}}
<flux:card id="okonomi" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.calculator class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Økonomi</flux:heading>
            </div>
            <flux:badge color="violet" size="sm">Økonomi</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Økonomimodulen gir deg full kontroll over regnskapet.
        </flux:text>

        <flux:callout variant="info" icon="arrow-top-right-on-square" class="not-prose mb-6">
            <flux:callout.heading>Eget økonomi-panel</flux:callout.heading>
            <flux:callout.text>Økonomifunksjonene er tilgjengelige via <strong>/economy</strong>-panelet. Dette krever <strong>økonomi</strong> eller <strong>admin</strong>-rolle.</flux:callout.text>
        </flux:callout>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Kontoplan</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Systemet bruker norsk standard kontoplan (NS 4102):</p>
                        <ul>
                            <li><strong>Klasse 1</strong> - Eiendeler (1000-1999)</li>
                            <li><strong>Klasse 2</strong> - Egenkapital og gjeld (2000-2999)</li>
                            <li><strong>Klasse 3</strong> - Inntekter (3000-3999)</li>
                            <li><strong>Klasse 4</strong> - Varekostnad (4000-4999)</li>
                            <li><strong>Klasse 5</strong> - Lønn (5000-5999)</li>
                            <li><strong>Klasse 6-7</strong> - Driftskostnader (6000-7999)</li>
                            <li><strong>Klasse 8</strong> - Finansposter (8000-8999)</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Bilagsregistrering</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Registrer manuelle bilag:</p>
                        <ol>
                            <li>Gå til <strong>Økonomi-panelet > Bilagsregistrering</strong></li>
                            <li>Klikk <strong>Nytt bilag</strong></li>
                            <li>Sett bilagsdato og beskrivelse</li>
                            <li>Legg til linjer med konto, debet og kredit</li>
                            <li>Påse at debet = kredit (bilaget må balansere)</li>
                            <li>Lagre og bokfør bilaget</li>
                        </ol>

                        <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                            <flux:callout.heading>Automatisk bokforing</flux:callout.heading>
                            <flux:callout.text>Fakturaer og betalinger bokfores automatisk. Du trenger kun registrere manuelle bilag for transaksjoner som ikke kommer fra salgsprosessen.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Reskontro</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p><strong>Kundereskontro:</strong></p>
                        <ul>
                            <li>Se alle ubetalte fakturaer per kunde</li>
                            <li>Aldersfordeling: 0-30, 31-60, 61-90, 90+ dager</li>
                            <li>Klikk på kunde for detaljert oversikt</li>
                        </ul>

                        <p><strong>Leverandørreskontro:</strong></p>
                        <ul>
                            <li>Registrer leverandørfakturaer</li>
                            <li>Følg opp forfallsdatoer</li>
                            <li>Registrer betalinger</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Leverandorfakturaer</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p><strong>Manuell registrering:</strong></p>
                        <ol>
                            <li>Klikk <strong>Ny leverandørfaktura</strong></li>
                            <li>Velg leverandør</li>
                            <li>Angi leverandørens fakturanummer og dato</li>
                            <li>Legg til linjer med kostnadskonto</li>
                            <li>Godkjenn for bokføring</li>
                            <li>Registrer betaling når den er utført</li>
                        </ol>

                        <flux:callout variant="info" icon="sparkles" class="not-prose my-4">
                            <flux:callout.heading>AI-tolkning i Innboks</flux:callout.heading>
                            <flux:callout.text>Last opp leverandørfakturaer som PDF eller bilde i <a href="#innboks" class="text-indigo-600 dark:text-indigo-400 underline">Innboksen</a>. Systemet tolker automatisk leverandør, beløp og datoer med AI.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
