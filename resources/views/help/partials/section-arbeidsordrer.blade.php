{{-- Arbeidsordrer --}}
<flux:card id="arbeidsordrer" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.clipboard-document-list class="w-5 h-5 text-red-600 dark:text-red-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Arbeidsordrer</flux:heading>
            </div>
            <flux:badge color="orange" size="sm">Prosjekt</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Arbeidsordrer brukes for å planlegge og registrere arbeid som skal utføres.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Opprette arbeidsordre</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ol>
                            <li>Klikk <strong>Ny arbeidsordre</strong></li>
                            <li>Velg kunde og eventuelt prosjekt</li>
                            <li>Beskriv arbeidet som skal utføres</li>
                            <li>Velg type: Service, Reparasjon, Installasjon, Vedlikehold eller Konsultasjon</li>
                            <li>Sett prioritet og planlagt dato</li>
                            <li>Tildel ansvarlig person</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Timeregistrering og materialbruk</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p><strong>Timeregistrering:</strong></p>
                        <ul>
                            <li>Åpne arbeidsordren</li>
                            <li>Gå til <strong>Timer</strong>-seksjonen</li>
                            <li>Legg til medarbeider, dato og antall timer</li>
                            <li>Beskriv arbeidet som ble utført</li>
                        </ul>

                        <p><strong>Materialbruk:</strong></p>
                        <ul>
                            <li>Velg produkt fra vareregisteret</li>
                            <li>Angi antall</li>
                            <li>Pris hentes automatisk</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Arbeidsflyt</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="not-prose my-4">
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <flux:badge color="zinc">Ny</flux:badge>
                            <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                            <flux:badge color="blue">Planlagt</flux:badge>
                            <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                            <flux:badge color="yellow">Pågår</flux:badge>
                            <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                            <flux:badge color="green">Fullført</flux:badge>
                            <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                            <flux:badge color="emerald">Godkjent</flux:badge>
                            <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                            <flux:badge color="indigo">Fakturert</flux:badge>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
