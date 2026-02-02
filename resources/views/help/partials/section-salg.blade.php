{{-- Salg --}}
<flux:card id="salg" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.shopping-cart class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Salg</flux:heading>
            </div>
            <flux:badge color="green" size="sm">CRM & Salg</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Salgsmodulen dekker hele salgsprosessen fra tilbud til faktura.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Tilbud</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Opprett profesjonelle tilbud til kunder:</p>
                        <ol>
                            <li>Klikk <strong>Nytt tilbud</strong> fra Tilbud-siden eller fra kontaktkortet</li>
                            <li>Velg kunde - adresseinformasjon fylles ut automatisk</li>
                            <li>Klikk <strong>Opprett og legg til linjer</strong></li>
                            <li>Legg til produkter og tjenester fra vareregisteret</li>
                            <li>Angi rabatter om ønskelig</li>
                            <li>Sett gyldighetsdato</li>
                            <li>Forhåndsvis PDF og send på e-post</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Ordrer</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Når kunden aksepterer tilbudet:</p>
                        <ol>
                            <li>Åpne tilbudet</li>
                            <li>Klikk <strong>Konverter til ordre</strong></li>
                            <li>Ordren opprettes med alle linjer fra tilbudet</li>
                            <li>Send ordrebekreftelse til kunden</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Fakturaer</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Fakturer kunden når arbeidet er levert:</p>
                        <ol>
                            <li>Opprett faktura fra ordre eller manuelt</li>
                            <li>Kontroller linjer og beløp</li>
                            <li>Sett forfallsdato (standard 14 dager)</li>
                            <li>Send faktura på e-post med PDF</li>
                        </ol>

                        <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                            <flux:callout.heading>Kreditnota</flux:callout.heading>
                            <flux:callout.text>For å kreditere en faktura, åpne fakturaen og klikk "Opprett kreditnota". Dette oppretter en negativ faktura som utligner den opprinnelige.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Betalinger og nummerserier</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p><strong>Registrer innbetalinger:</strong></p>
                        <ol>
                            <li>Åpne fakturaen</li>
                            <li>Klikk <strong>Registrer betaling</strong></li>
                            <li>Angi beløp, dato og betalingsmåte</li>
                            <li>Fakturaen oppdateres automatisk</li>
                        </ol>

                        <p><strong>Nummerserier:</strong></p>
                        <ul>
                            <li>Tilbud: T-YYYY-NNNN</li>
                            <li>Ordrer: O-YYYY-NNNN</li>
                            <li>Fakturaer: F-YYYY-NNNN</li>
                            <li>Kreditnotaer: K-YYYY-NNNN</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
