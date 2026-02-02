{{-- Rapporter --}}
<flux:card id="rapporter" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.chart-bar class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Rapporter</flux:heading>
            </div>
            <flux:badge color="violet" size="sm">Økonomi</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Rapportmodulen gir deg innsikt i økonomien.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Hovedbok</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Viser alle transaksjoner på en eller flere konti i en periode:</p>
                        <ul>
                            <li>Velg periode (fra/til-dato)</li>
                            <li>Filtrer eventuelt på spesifikk konto</li>
                            <li>Se alle posteringer med bilagsreferanse</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Bilagsjournal</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Kronologisk liste over alle bilag:</p>
                        <ul>
                            <li>Velg periode</li>
                            <li>Se bilagsnummer, dato, beskrivelse og beløp</li>
                            <li>Klikk på bilag for detaljer</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Saldobalanse</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Saldo for alle konti på en gitt dato:</p>
                        <ul>
                            <li>Velg balansedato</li>
                            <li>Se inngående balanse, bevegelse og utgående balanse</li>
                            <li>Verifiser at debet = kredit</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Resultatregnskap og Balanse</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p><strong>Resultatregnskap:</strong></p>
                        <ul>
                            <li>Velg periode</li>
                            <li>Se driftsinntekter (klasse 3)</li>
                            <li>Se driftskostnader (klasse 4-7)</li>
                            <li>Se finansposter (klasse 8)</li>
                            <li>Resultat før år beregnes automatisk</li>
                        </ul>

                        <p><strong>Balanse:</strong></p>
                        <ul>
                            <li>Velg balansedato</li>
                            <li>Se eiendeler (klasse 1)</li>
                            <li>Se egenkapital og gjeld (klasse 2)</li>
                            <li>Kontroller at balansen balanserer</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
