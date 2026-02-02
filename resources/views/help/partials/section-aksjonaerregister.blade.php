{{-- Aksjonaerregister --}}
<flux:card id="aksjonaerregister" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.user-group class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Aksjonærregister</flux:heading>
            </div>
            <flux:badge color="indigo" size="sm">Årsoppgjør</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Aksjonærregisteret holder oversikt over selskapets aksjonærer, aksjeklasser, transaksjoner og utbytte.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Aksjonærer</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Registrer alle aksjonærer i selskapet:</p>
                        <ol>
                            <li>Gå til <strong>Årsoppgjør > Aksjonærregister</strong></li>
                            <li>Klikk <strong>Ny aksjonær</strong></li>
                            <li>Velg type: Person eller Selskap</li>
                            <li>Fyll inn identifikasjon (fødselsnummer eller org.nr)</li>
                            <li>Legg til navn og adresse</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Aksjeklasser og innehav</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p><strong>Aksjeklasser:</strong></p>
                        <ul>
                            <li><strong>Navn</strong> - F.eks. A-aksjer, B-aksjer</li>
                            <li><strong>ISIN</strong> - Internasjonal verdipapiridentifikator</li>
                            <li><strong>Pålydende</strong> - Nominell verdi per aksje</li>
                            <li><strong>Totalt antall</strong> - Antall aksjer i klassen</li>
                            <li><strong>Stemmerett</strong> - Har aksjene stemmerett?</li>
                            <li><strong>Utbytterett</strong> - Har aksjene rett til utbytte?</li>
                        </ul>

                        <p><strong>Aksjeinnehav:</strong></p>
                        <ul>
                            <li>Velg aksjonær og aksjeklasse</li>
                            <li>Angi antall aksjer</li>
                            <li>Registrer inngangsverdi (anskaffelseskost)</li>
                            <li>Sett ervervsdato og -måte</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Aksjetransaksjoner</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="not-prose space-y-2 text-sm">
                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                            <flux:badge color="blue">Stiftelse</flux:badge>
                            <span>Tildeling ved selskapsstiftelse</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                            <flux:badge color="green">Emisjon</flux:badge>
                            <span>Kapitalforhøyelse med nye aksjer</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                            <flux:badge color="yellow">Overdragelse</flux:badge>
                            <span>Kjøp/salg mellom aksjonærer</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                            <flux:badge color="purple">Splitt</flux:badge>
                            <span>Oppsplittes i flere aksjer</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                            <flux:badge color="red">Innløsning</flux:badge>
                            <span>Selskapet kjøper tilbake aksjer</span>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Årsrapport (RF-1086)</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Generer aksjonærregisteroppgaven:</p>
                        <ol>
                            <li>Gå til <strong>Rapporter</strong>-fanen</li>
                            <li>Velg år</li>
                            <li>Klikk <strong>Opprett rapport</strong></li>
                            <li>Systemet samler data fra registeret</li>
                            <li>Generer XML for innsending via Altinn</li>
                        </ol>

                        <flux:callout variant="warning" icon="exclamation-triangle" class="not-prose my-4">
                            <flux:callout.heading>Frist</flux:callout.heading>
                            <flux:callout.text>Aksjonærregisteroppgaven skal sendes til Skatteetaten innen 31. januar året etter inntektsåret.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
