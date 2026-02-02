{{-- Timeregistrering --}}
<flux:card id="timeregistrering" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.clock class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Timeregistrering</flux:heading>
            </div>
            <flux:badge color="orange" size="sm">Prosjekt</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Registrer arbeidstimer ukentlig og send til godkjenning. Timer kan knyttes til prosjekter, arbeidsordrer eller registreres som intern tid.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Registrere timer</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Du kan registrere timer på to måter:</p>
                        <ul>
                            <li><strong>Inline i rutenett:</strong> Skriv timer direkte i cellen for den aktuelle dagen</li>
                            <li><strong>Via modal:</strong> Dobbelklikk på en celle eller bruk <strong>Registrer timer</strong>-knappen</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Koble til prosjekt eller arbeidsordre</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ol>
                            <li>Klikk <strong>Legg til linje</strong> eller <strong>Registrer timer</strong></li>
                            <li>Velg type: Prosjekt, Arbeidsordre eller Annet (intern tid)</li>
                            <li>Velg prosjekt/arbeidsordre fra listen</li>
                            <li>For intern tid, skriv en beskrivelse</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Sende til godkjenning</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ol>
                            <li>Når uken er ferdig, klikk <strong>Send til godkjenning</strong></li>
                            <li>Legg eventuelt til en kommentar</li>
                            <li>Timeseddelen låses for redigering</li>
                        </ol>

                        <div class="not-prose my-4">
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <flux:badge color="zinc">Utkast</flux:badge>
                                <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                <flux:badge color="amber">Innsendt</flux:badge>
                                <flux:icon.arrow-right class="w-4 h-4 text-zinc-400" />
                                <flux:badge color="green">Godkjent</flux:badge>
                            </div>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Timerapporter (for ledere)</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Under <strong>Timer → Rapporter</strong> finner ledere oversikt over timer på tvers av hele firmaet:</p>
                        <ul>
                            <li><strong>Per prosjekt:</strong> Se totaltimer fordelt på prosjekter</li>
                            <li><strong>Per arbeidsordre:</strong> Se timer per arbeidsordre med tilhørende prosjekt</li>
                            <li><strong>Per ansatt:</strong> Oversikt over timer per medarbeider</li>
                            <li><strong>Per uke:</strong> Se ukentlige totaler over tid</li>
                        </ul>

                        <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                            <flux:callout.heading>Tips</flux:callout.heading>
                            <flux:callout.text>
                                Dobbelklikk på en timecelle for å åpne registreringsskjemaet med prosjektet forhåndsvalgt.
                            </flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
