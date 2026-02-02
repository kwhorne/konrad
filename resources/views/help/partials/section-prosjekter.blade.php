{{-- Prosjekter --}}
<flux:card id="prosjekter" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.folder class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Prosjekter</flux:heading>
            </div>
            <flux:badge color="orange" size="sm">Prosjekt</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Prosjektmodulen lar deg organisere arbeid knyttet til kunder med budsjett- og timesstyring.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Opprette prosjekt</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ol>
                            <li>Klikk <strong>Nytt prosjekt</strong></li>
                            <li>Gi prosjektet et navn og velg kunde</li>
                            <li>Velg <strong>prosjektleder</strong> - personen som har hovedansvaret</li>
                            <li>Velg prosjekttype og status</li>
                            <li>Angi budsjett og estimerte timer</li>
                            <li>Sett start- og sluttdato</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Statuser</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Følg prosjektets livssyklus:</p>
                        <div class="not-prose flex flex-wrap gap-2 my-4">
                            <flux:badge color="blue">Planlegging</flux:badge>
                            <flux:badge color="yellow">Pågår</flux:badge>
                            <flux:badge color="green">Fullført</flux:badge>
                            <flux:badge color="zinc">Pause</flux:badge>
                            <flux:badge color="red">Kansellert</flux:badge>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Prosjekttyper</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Kategoriser prosjektene dine:</p>
                        <ul>
                            <li><strong>Konsulentoppdrag</strong> - Rådgivning og konsulentarbeid</li>
                            <li><strong>Utviklingsprosjekt</strong> - Programvareutvikling og tekniske prosjekter</li>
                            <li><strong>Supportavtale</strong> - Løpende support- og vedlikeholdsavtaler</li>
                            <li><strong>Implementering</strong> - Utrulling av systemer</li>
                            <li><strong>Opplæring</strong> - Kurs og opplæringsprosjekter</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
