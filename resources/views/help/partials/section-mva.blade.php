{{-- MVA-meldinger --}}
<flux:card id="mva" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.document-chart-bar class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">MVA-meldinger</flux:heading>
            </div>
            <flux:badge color="violet" size="sm">Økonomi</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            MVA-modulen hjelper deg med å rapportere merverdiavgift til Skatteetaten via Altinn.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Perioder</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Systemet støtter tomånedlig (terminvis) rapportering:</p>
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
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Opprette og beregne MVA-melding</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p><strong>Opprette:</strong></p>
                        <ol>
                            <li>Gå til <strong>Økonomi-panelet > MVA-meldinger</strong></li>
                            <li>Klikk <strong>Ny MVA-melding</strong></li>
                            <li>Velg år og periode</li>
                            <li>Klikk <strong>Opprett</strong></li>
                        </ol>

                        <p><strong>Beregne:</strong></p>
                        <ol>
                            <li>Åpne MVA-meldingen</li>
                            <li>Klikk <strong>Beregn MVA</strong></li>
                            <li>Systemet henter data fra fakturaer og leverandørfakturaer</li>
                            <li>Kontroller beløpene</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">MVA-koder</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="not-prose space-y-4">
                        <div>
                            <h5 class="font-medium text-zinc-900 dark:text-white mb-2 text-sm">Utgående MVA (salg)</h5>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                    <span>Kode 3 - Høy sats</span>
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
                            </div>
                        </div>
                        <div>
                            <h5 class="font-medium text-zinc-900 dark:text-white mb-2 text-sm">Inngående MVA (kjøp - fradrag)</h5>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                    <span>Kode 1 - Høy sats</span>
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
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Sende meldingen</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ol>
                            <li>Kontroller at alle beløp er korrekte</li>
                            <li>Klikk <strong>Merk som sendt</strong></li>
                            <li>Logg inn i Altinn og send meldingen der</li>
                            <li>Legg inn Altinn-referansen du mottar</li>
                            <li>Merk som godkjent når du får bekreftelse</li>
                        </ol>

                        <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                            <flux:callout.heading>Viktig</flux:callout.heading>
                            <flux:callout.text>Systemet genererer kun MVA-oppgaven. Du må fortsatt logge inn i Altinn for å sende den offisielt til Skatteetaten.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
