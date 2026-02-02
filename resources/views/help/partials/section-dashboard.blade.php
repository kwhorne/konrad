{{-- Dashboard --}}
<flux:card id="dashboard" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.home class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Dashboard</flux:heading>
            </div>
            <flux:badge color="blue" size="sm">Grunnleggende</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Dashboardet gir deg en personlig oversikt over det som er mest relevant for din rolle i bedriften.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Rollebasert innhold</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Dashboardet tilpasser seg automatisk basert på din brukerrolle:</p>
                        <ul>
                            <li><strong>Alle brukere</strong> - Ser egne timer denne uken og timeliste-status</li>
                            <li><strong>Økonomi og admin</strong> - Ser økonomiske nøkkeltall, forfalte fakturaer og bilag i innboks</li>
                            <li><strong>Salg</strong> - Ser aktive tilbud og åpne ordrer</li>
                            <li><strong>Prosjekt</strong> - Ser aktive prosjekter og åpne arbeidsordrer</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Mine timer denne uken</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Alle brukere ser et kort med timer registrert denne uken. Kortet viser:</p>
                        <ul>
                            <li>Antall timer ført</li>
                            <li>Status på timelisten (utkast, sendt, godkjent)</li>
                            <li>Direktelink til timeregistrering</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Tips</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:callout variant="info" icon="light-bulb" class="not-prose">
                        <flux:callout.heading>Tips</flux:callout.heading>
                        <flux:callout.text>
                            Bruk snarveiene nederst på dashbordet for rask tilgang til de vanligste funksjonene.
                        </flux:callout.text>
                    </flux:callout>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
