{{-- Kontoplan --}}
<flux:card id="kontoplan" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.table-cells class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Kontoplan</flux:heading>
            </div>
            <flux:badge color="zinc" size="sm">Innstillinger</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Kontoplanen er grunnlaget for regnskapet ditt. Konrad støtter NS 4102 - Norsk standard kontoplan.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Opprette NS 4102 kontoplan</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>For nye selskaper anbefaler vi å bruke den norske standardkontoplanen:</p>
                        <ol>
                            <li>Gå til <strong>Økonomi → Kontoplan</strong></li>
                            <li>Klikk <strong>Opprett NS 4102 kontoplan</strong></li>
                            <li>Bekreft at du vil opprette kontoplanen</li>
                        </ol>
                        <p>Dette oppretter over 200 forhåndsdefinerte kontoer som dekker de fleste behov for norske aksjeselskaper.</p>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Administrere kontoer</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Du kan også opprette og redigere kontoer manuelt:</p>
                        <ul>
                            <li><strong>Kontonummer</strong> - Firesifret nummer i henhold til NS 4102</li>
                            <li><strong>Kontonavn</strong> - Beskrivende navn på kontoen</li>
                            <li><strong>Kontoklasse</strong> - F.eks. anleggsmidler, kortsiktig gjeld, driftsinntekter</li>
                            <li><strong>Kontotype</strong> - Eiendel, gjeld, egenkapital, inntekt eller kostnad</li>
                            <li><strong>MVA-kode</strong> - Koble kontoen til en MVA-kode for automatisk beregning</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Systemkontoer</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Noen kontoer er markert som systemkontoer og kan ikke slettes eller endres. Disse brukes av systemet for automatiske posteringer:</p>
                        <ul>
                            <li>Kundefordringer (1500)</li>
                            <li>Leverandørgjeld (2400)</li>
                            <li>Utgående MVA (2700)</li>
                            <li>Inngående MVA (2710)</li>
                        </ul>

                        <div class="not-prose mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <p class="text-blue-800 dark:text-blue-200 text-sm mb-0">
                                <strong>Tips:</strong> Eksisterende kontoer med samme kontonummer vil ikke bli overskrevet når du oppretter NS 4102 kontoplan.
                            </p>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
