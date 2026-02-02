{{-- Avdelinger --}}
<flux:card id="avdelinger" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.building-library class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Avdelinger</flux:heading>
            </div>
            <flux:badge color="zinc" size="sm">Innstillinger</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Avdelinger lar deg gruppere brukere og spore kostnader og inntekter per organisatorisk enhet.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Opprette avdelinger</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ol>
                            <li>Gå til <strong>Innstillinger</strong> i sidemenyen</li>
                            <li>Velg fanen <strong>Avdelinger</strong></li>
                            <li>Klikk <strong>Ny avdeling</strong></li>
                            <li>Fyll inn avdelingskode (f.eks. "ADM", "SAL") og navn</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Tildele brukere til avdelinger</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Når avdelinger er aktivert kan du tildele brukere til avdelinger:</p>
                        <ol>
                            <li>Gå til <strong>Innstillinger → Brukere</strong></li>
                            <li>Klikk på en bruker for å redigere</li>
                            <li>Velg avdeling fra nedtrekkslisten</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Automatisk propagering</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Når en bruker har en avdeling, vil avdelingen automatisk følge med på:</p>
                        <ul>
                            <li>Tilbud brukeren oppretter</li>
                            <li>Ordrer konvertert fra tilbud</li>
                            <li>Fakturaer konvertert fra ordrer</li>
                            <li>Alle bilagslinjer som bokføres</li>
                        </ul>

                        <div class="not-prose mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <p class="text-blue-800 dark:text-blue-200 text-sm mb-0">
                                <strong>Tips:</strong> Du må aktivere avdelingsfunksjonen under <strong>Innstillinger → Regnskap</strong> for at avdelinger skal være tilgjengelig.
                            </p>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
