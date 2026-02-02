{{-- Kom i gang --}}
<flux:card id="kom-i-gang" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.rocket-launch class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Kom i gang</flux:heading>
            </div>
            <flux:badge color="blue" size="sm">Grunnleggende</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Velkommen til Konrad - et komplett forretningssystem for norske bedrifter. Denne dokumentasjonen hjelper deg med a ta i bruk systemet effektivt.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Første gangs innlogging</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ol>
                            <li>Åpne nettleseren og gå til systemets adresse</li>
                            <li>Logg inn med brukernavn og passord du har fått tildelt</li>
                            <li>Du kommer til dashboardet som gir deg oversikt over systemet</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Navigasjon</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Konrad har to hovedpaneler med egne menyer:</p>

                        <h5>App-panel (hovedmeny)</h5>
                        <ul>
                            <li><strong>Dashboard</strong> - Hovedoversikt over virksomheten</li>
                            <li><strong>Mine aktiviteter</strong> - Personlige oppgaver, forslag og notater</li>
                            <li><strong>CRM</strong> - Kontakter, Varer, Tilbud, Ordrer, Faktura</li>
                            <li><strong>Prosjekt</strong> - Prosjekter, Arbeidsordrer</li>
                            <li><strong>Kontrakter</strong> - Kontraktsregister</li>
                            <li><strong>Eiendeler</strong> - Eiendelsregister</li>
                            <li><strong>Økonomi</strong> - Link til økonomi-panelet</li>
                            <li><strong>Administrasjon</strong> - Brukeradministrasjon (kun admin)</li>
                        </ul>

                        <h5>Økonomi-panel</h5>
                        <p>Eget panel for regnskap og økonomi (krever økonomi- eller admin-rolle):</p>
                        <ul>
                            <li><strong>Dashboard</strong> - Økonomisk oversikt med grafer</li>
                            <li><strong>Økonomi</strong> - Bilag, Innboks, Reskontro, Rapporter, MVA, Kontoplan</li>
                            <li><strong>Årsoppgjør</strong> - Aksjonærregister, Skattemelding, Årsregnskap, Altinn</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Brukerroller</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <flux:callout variant="info" icon="information-circle" class="not-prose my-4">
                            <flux:callout.heading>Brukerroller</flux:callout.heading>
                            <flux:callout.text>
                                <strong>Admin</strong> har full tilgang. <strong>Økonomi</strong> har tilgang til app og økonomi-panel. <strong>Bruker</strong> har kun tilgang til app-panelet.
                            </flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Hurtigtaster</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="not-prose">
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div class="flex items-center gap-2 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                <kbd class="px-2 py-1 bg-white dark:bg-zinc-700 rounded text-xs">Ctrl</kbd> + <kbd class="px-2 py-1 bg-white dark:bg-zinc-700 rounded text-xs">K</kbd>
                                <span>Søk</span>
                            </div>
                            <div class="flex items-center gap-2 p-2 bg-zinc-100 dark:bg-zinc-800 rounded">
                                <kbd class="px-2 py-1 bg-white dark:bg-zinc-700 rounded text-xs">Esc</kbd>
                                <span>Lukk modal/meny</span>
                            </div>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
