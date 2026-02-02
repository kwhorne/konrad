{{-- Selskapsanalyse --}}
<flux:card id="selskapsanalyse" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.sparkles class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Selskapsanalyse</flux:heading>
            </div>
            <flux:badge color="violet" size="sm">Økonomi</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Selskapsanalysen gir deg en komplett oversikt over selskapets økonomiske helse basert på dine faktiske regnskapsdata.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Kjøre en analyse</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ol>
                            <li>Gå til <strong>Økonomi → Analyse</strong></li>
                            <li>Klikk <strong>Start analyse</strong></li>
                            <li>Vent mens systemet analyserer dataene (10-30 sekunder)</li>
                            <li>Se gjennom resultatene</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Hva analysen inneholder</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p><strong>Økonomisk helse:</strong></p>
                        <ul>
                            <li>Score fra 0-100 som viser den generelle økonomiske tilstanden</li>
                            <li>Statusindikator: Utmerket, God, Akseptabel, Bekymringsfull eller Kritisk</li>
                        </ul>

                        <p><strong>Nøkkeltall:</strong></p>
                        <ul>
                            <li><strong>Likviditet</strong> - Evne til å betale løpende utgifter</li>
                            <li><strong>Lønnsomhet</strong> - Fortjeneste i forhold til omsetning</li>
                            <li><strong>Vekst</strong> - Endring fra forrige år</li>
                            <li><strong>Kundefordringer</strong> - Status på utestående krav</li>
                        </ul>

                        <p><strong>SWOT-analyse:</strong></p>
                        <ul>
                            <li><strong>Styrker</strong> - Hva som fungerer bra i selskapet</li>
                            <li><strong>Svakheter</strong> - Områder som kan forbedres</li>
                            <li><strong>Muligheter</strong> - Potensielle vekstområder</li>
                            <li><strong>Risikoer</strong> - Farer du bør være oppmerksom på</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Tips og advarsler</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:callout variant="info" icon="light-bulb" class="not-prose mb-4">
                        <flux:callout.heading>Tips</flux:callout.heading>
                        <flux:callout.text>Kjør analysen jevnlig for å følge med på utviklingen. Sammenlign resultatene over tid for å se om tiltakene dine gir effekt.</flux:callout.text>
                    </flux:callout>

                    <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose">
                        <flux:callout.heading>Viktig</flux:callout.heading>
                        <flux:callout.text>Analysen er et hjelpemiddel og bør verifiseres av en regnskapsfører for viktige beslutninger.</flux:callout.text>
                    </flux:callout>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
