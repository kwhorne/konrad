{{-- Skatt --}}
<flux:card id="skatt" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-rose-100 dark:bg-rose-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.receipt-percent class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Skatt</flux:heading>
            </div>
            <flux:badge color="indigo" size="sm">Årsoppgjør</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Skattemodulen hjelper deg med å beregne skattepliktig inntekt, håndtere forskjeller, og generere skattemeldingen.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Skattemessige justeringer</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="not-prose space-y-3">
                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                            <div class="font-medium text-red-800 dark:text-red-200 mb-1 text-sm">Permanente forskjeller</div>
                            <div class="text-sm text-red-700 dark:text-red-300">Forskjeller som aldri reverseres skattemessig</div>
                            <ul class="text-xs text-red-600 dark:text-red-400 mt-2 space-y-1">
                                <li>Representasjonskostnader (ikke fradrag)</li>
                                <li>Boter og gebyrer (ikke fradrag)</li>
                                <li>Gaver over fradragsgrense</li>
                            </ul>
                        </div>
                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                            <div class="font-medium text-yellow-800 dark:text-yellow-200 mb-1 text-sm">Midlertidige forskjeller</div>
                            <div class="text-sm text-yellow-700 dark:text-yellow-300">Forskjeller som reverseres over tid</div>
                            <ul class="text-xs text-yellow-600 dark:text-yellow-400 mt-2 space-y-1">
                                <li>Avskrivningsforskjeller</li>
                                <li>Urealiserte gevinster/tap</li>
                                <li>Underskudd til fremføring</li>
                            </ul>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Saldoavskrivning</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="not-prose space-y-1 text-sm">
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
                            <span>Gruppe h - Bygg og anlegg</span>
                            <span class="font-mono">4%</span>
                        </div>
                        <div class="flex justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                            <span>Gruppe i - Forretningsbygg</span>
                            <span class="font-mono">2%</span>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Skattemelding (RF-1028)</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Generer skattemeldingen:</p>
                        <ol>
                            <li>Gå til <strong>Årsoppgjør > Skatt > Skattemelding</strong></li>
                            <li>Velg regnskapsår</li>
                            <li>Klikk <strong>Opprett skattemelding</strong></li>
                            <li>Systemet henter data fra regnskap og justeringer</li>
                            <li>Kontroller beregningen</li>
                            <li>Generer XML for innsending</li>
                        </ol>

                        <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                            <flux:callout.heading>Frist</flux:callout.heading>
                            <flux:callout.text>Skattemeldingen for aksjeselskaper skal sendes til Skatteetaten innen 31. mai året etter inntektsåret.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
