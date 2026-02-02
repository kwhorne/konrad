{{-- Vareregister --}}
<flux:card id="produkter" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.cube class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Vareregister</flux:heading>
            </div>
            <flux:badge color="green" size="sm">CRM & Salg</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Vareregisteret inneholder alle produkter og tjenester du selger.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Produktstruktur</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ul>
                            <li><strong>Varegrupper</strong> - Overordnet kategorisering (f.eks. "Tjenester", "Varer")</li>
                            <li><strong>Varetyper</strong> - Underkategorier med standard MVA-sats</li>
                            <li><strong>Produkter</strong> - Individuelle varer/tjenester</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Opprette produkt</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ol>
                            <li>Klikk <strong>Nytt produkt</strong></li>
                            <li>Fyll inn produktnavn og eventuelt SKU (varenummer)</li>
                            <li>Velg varegruppe og varetype</li>
                            <li>Angi pris og eventuell kostpris</li>
                            <li>Velg enhet (stk, timer, kg, etc.)</li>
                            <li>MVA-sats arves fra varetypen</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">MVA-satser</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Systemet støtter alle norske MVA-satser:</p>
                        <div class="not-prose">
                            <div class="grid grid-cols-3 gap-2 text-sm my-4">
                                <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded text-center">
                                    <div class="font-bold text-lg">25%</div>
                                    <div class="text-zinc-500">Standard</div>
                                </div>
                                <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded text-center">
                                    <div class="font-bold text-lg">15%</div>
                                    <div class="text-zinc-500">Næringsmidler</div>
                                </div>
                                <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded text-center">
                                    <div class="font-bold text-lg">12%</div>
                                    <div class="text-zinc-500">Transport/kultur</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
