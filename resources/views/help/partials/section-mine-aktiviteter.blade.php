{{-- Mine aktiviteter --}}
<flux:card id="mine-aktiviteter" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.clipboard-document-list class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Mine aktiviteter</flux:heading>
            </div>
            <flux:badge color="blue" size="sm">Grunnleggende</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Mine aktiviteter gir deg en personlig oversikt over hva du bør følge opp, med intelligente forslag og personlige notater.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Forslag til aktiviteter</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Klikk <strong>Generer forslag</strong> for å få intelligente anbefalinger basert på:</p>
                        <ul>
                            <li><strong>Aktiviteter</strong> - Ventende oppgaver tildelt deg</li>
                            <li><strong>Tilbud</strong> - Utkast og sendte tilbud som venter på svar</li>
                            <li><strong>Arbeidsordrer</strong> - Åpne arbeidsordrer tildelt deg</li>
                            <li><strong>Prosjekter</strong> - Prosjekter der du er prosjektleder</li>
                            <li><strong>Fakturaer</strong> - Ubetalte fakturaer du har opprettet</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Arbeidsmengde-score</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>En visuell indikator (0-100) som viser din totale arbeidsmengde:</p>
                        <ul>
                            <li><strong>0-49</strong> - Rolig periode</li>
                            <li><strong>50-79</strong> - Normal arbeidsmengde</li>
                            <li><strong>80-100</strong> - Mye å gjøre</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Mine notater</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Skriv personlige notater med rik tekst-formatering:</p>
                        <ul>
                            <li>Opprett notater via <strong>Nytt notat</strong>-knappen</li>
                            <li>Bruk overskrifter, lister, lenker og formatering</li>
                            <li>Fest viktige notater så de alltid vises øverst</li>
                            <li>Notatene følger deg på tvers av selskaper</li>
                        </ul>

                        <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                            <flux:callout.heading>Tips</flux:callout.heading>
                            <flux:callout.text>
                                Notatene dine er personlige og følger deg selv om du bytter mellom selskaper. Bruk dem til huskelister, møtenotater eller annet du vil ha lett tilgjengelig.
                            </flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
