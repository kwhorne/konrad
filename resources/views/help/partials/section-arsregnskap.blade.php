{{-- Årsregnskap --}}
<flux:card id="arsregnskap" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-sky-100 dark:bg-sky-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.document-text class="w-5 h-5 text-sky-600 dark:text-sky-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Årsregnskap</flux:heading>
            </div>
            <flux:badge color="indigo" size="sm">Årsoppgjør</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Årsregnskapsmodulen hjelper deg med å utarbeide årsregnskapet som skal sendes til Regnskapsregisteret.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Opprette årsregnskap</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ol>
                            <li>Gå til <strong>Årsoppgjør > Årsregnskap</strong></li>
                            <li>Klikk <strong>Nytt årsregnskap</strong></li>
                            <li>Velg regnskapsår</li>
                            <li>Systemet henter automatisk nøkkeltall fra regnskapet</li>
                            <li>Standard noter opprettes automatisk</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Selskapsstørrelse</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="not-prose space-y-3">
                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                            <div class="font-medium text-green-800 dark:text-green-200 text-sm">Små foretak</div>
                            <div class="text-xs text-green-700 dark:text-green-300">Forenklede krav til noter og oppstillinger</div>
                            <ul class="text-xs text-green-600 dark:text-green-400 mt-2 space-y-1">
                                <li>Salgsinntekt < 70 MNOK</li>
                                <li>Balansesum < 35 MNOK</li>
                                <li>Ansatte < 50 årsverk</li>
                            </ul>
                        </div>
                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                            <div class="font-medium text-yellow-800 dark:text-yellow-200 text-sm">Mellomstore foretak</div>
                            <div class="text-xs text-yellow-700 dark:text-yellow-300">Krever kontantstrømoppstilling</div>
                        </div>
                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                            <div class="font-medium text-red-800 dark:text-red-200 text-sm">Store foretak</div>
                            <div class="text-xs text-red-700 dark:text-red-300">Fulle krav, revisjonsplikt</div>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Noter</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Årsregnskapet skal inneholde noter som forklarer tallene:</p>
                        <ul>
                            <li><strong>Regnskapsprinsipper</strong> - Anvendte prinsipper (påkrevd)</li>
                            <li><strong>Ansatte</strong> - Lønnskostnader og antall (påkrevd)</li>
                            <li><strong>Varige driftsmidler</strong> - Avskrivninger og bevegelser</li>
                            <li><strong>Aksjekapital</strong> - Eierstruktur</li>
                            <li><strong>Egenkapital</strong> - Bevegelser i perioden</li>
                            <li><strong>Skatt</strong> - Betalbar og utsatt skatt</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">XBRL-generering og innsending</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Årsregnskapet sendes til Regnskapsregisteret i XBRL-format:</p>
                        <ol>
                            <li>Fullfør alle noter</li>
                            <li>Klikk <strong>Valider</strong> for å sjekke at alt er komplett</li>
                            <li>Klikk <strong>Godkjenn</strong> for styregodkjenning</li>
                            <li>Klikk <strong>Send til Altinn</strong> for å generere XBRL</li>
                        </ol>

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

                        <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                            <flux:callout.heading>Frist</flux:callout.heading>
                            <flux:callout.text>Årsregnskapet skal sendes til Regnskapsregisteret innen 31. juli året etter regnskapsåret.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
