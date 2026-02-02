{{-- Altinn --}}
<flux:card id="altinn" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.paper-airplane class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Altinn-integrasjon</flux:heading>
            </div>
            <flux:badge color="indigo" size="sm">Årsoppgjør</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Altinn-dashboardet gir deg oversikt over alle obligatoriske innsendinger og deres status.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Oversikt over innsendinger</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="not-prose space-y-3">
                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-zinc-900 dark:text-white text-sm">Aksjonærregisteroppgaven (RF-1086)</span>
                                <span class="text-xs text-zinc-500">31. januar</span>
                            </div>
                            <div class="text-xs text-zinc-600 dark:text-zinc-400">Rapport til Skatteetaten om aksjonærforhold</div>
                        </div>
                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-zinc-900 dark:text-white text-sm">Skattemelding (RF-1028)</span>
                                <span class="text-xs text-zinc-500">31. mai</span>
                            </div>
                            <div class="text-xs text-zinc-600 dark:text-zinc-400">Næringsoppgave og skattemelding til Skatteetaten</div>
                        </div>
                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-zinc-900 dark:text-white text-sm">Årsregnskap (XBRL)</span>
                                <span class="text-xs text-zinc-500">31. juli</span>
                            </div>
                            <div class="text-xs text-zinc-600 dark:text-zinc-400">Årsregnskap til Regnskapsregisteret</div>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Status på innsending</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="not-prose space-y-2 text-sm">
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
                            <span>Godkjent og klar til å sendes</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                            <flux:badge color="purple">Sendt inn</flux:badge>
                            <span>Sendt til mottaker, venter på svar</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                            <flux:badge color="green">Akseptert</flux:badge>
                            <span>Godkjent av mottaker</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                            <flux:badge color="red">Avvist</flux:badge>
                            <span>Feil i innsendingen, må korrigeres</span>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Fristpåminnelser</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Systemet varsler om kommende frister:</p>
                        <ul>
                            <li><strong>30 dager før</strong> - Første påminnelse</li>
                            <li><strong>14 dager før</strong> - Oppfølging</li>
                            <li><strong>7 dager før</strong> - Hastevarsel</li>
                            <li><strong>1 dag før</strong> - Kritisk frist</li>
                        </ul>

                        <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                            <flux:callout.heading>Elektronisk signering</flux:callout.heading>
                            <flux:callout.text>Innsending til Altinn krever virksomhetssertifikat eller annen godkjent autentiseringsmetode.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
