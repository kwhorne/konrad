{{-- Innboks (AI-tolkning) --}}
<flux:card id="innboks" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.inbox-arrow-down class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Innboks - Inngående bilag</flux:heading>
            </div>
            <flux:badge color="violet" size="sm">Økonomi</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Innboksen bruker kunstig intelligens (AI) til å automatisk tolke leverandørfakturaer fra PDF-er og bilder.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Laste opp bilag</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ol>
                            <li>Gå til <strong>Økonomi-panelet > Innkommende bilag</strong></li>
                            <li>Klikk <strong>Last opp bilag</strong></li>
                            <li>Velg en eller flere filer (PDF, JPG, PNG)</li>
                            <li>Klikk <strong>Last opp</strong></li>
                            <li>Bilagene sendes automatisk til AI-tolkning</li>
                        </ol>

                        <flux:callout variant="info" icon="sparkles" class="not-prose my-4">
                            <flux:callout.heading>AI-tolkning</flux:callout.heading>
                            <flux:callout.text>Systemet bruker ChatGPT (GPT-4o) til å lese og forstå innholdet i fakturaene. Tolkningen tar vanligvis 5-30 sekunder per bilag.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Godkjenningsflyt</flux:accordion.heading>
                <flux:accordion.content>
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
                            <flux:badge color="emerald">Bokført</flux:badge>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Attestere og godkjenne</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p><strong>Attestere bilag:</strong></p>
                        <ol>
                            <li>Klikk på et tolket bilag for å åpne detaljer</li>
                            <li>Kontroller at AI har tolket riktig</li>
                            <li>Gjør eventuelle korrigeringer</li>
                            <li>Klikk <strong>Attester</strong></li>
                        </ol>

                        <p><strong>Godkjenne og bokføre:</strong></p>
                        <ol>
                            <li>Når bilaget er attestert, klikk <strong>Godkjenn</strong></li>
                            <li>Systemet oppretter automatisk leverandørfaktura og regnskapsbilag</li>
                            <li>Bilaget er nå bokført og klart for betaling</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Statuser</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="not-prose space-y-2 text-sm">
                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                            <flux:badge color="zinc">Venter</flux:badge>
                            <span>Bilaget er lastet opp og venter på tolkning</span>
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
                            <span>Kontrollert, venter på endelig godkjenning</span>
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
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
