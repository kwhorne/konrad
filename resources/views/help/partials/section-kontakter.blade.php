{{-- Kontaktregister --}}
<flux:card id="kontakter" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.users class="w-5 h-5 text-green-600 dark:text-green-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Kontaktregister</flux:heading>
            </div>
            <flux:badge color="green" size="sm">CRM & Salg</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Kontaktregisteret er hjertet i systemet. Her administrerer du alle kunder og leverandører.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Opprette ny kontakt</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ol>
                            <li>Klikk <strong>Ny kontakt</strong>-knappen</li>
                            <li>Velg kontakttype: <em>Kunde</em>, <em>Leverandør</em> eller <em>Begge</em></li>
                            <li>Fyll inn firmanavn og organisasjonsnummer</li>
                            <li>Legg til adresse og kontaktinformasjon</li>
                            <li>Klikk <strong>Lagre</strong></li>
                        </ol>

                        <flux:callout variant="info" icon="light-bulb" class="not-prose my-4">
                            <flux:callout.heading>Tips: Brreg-oppslag</flux:callout.heading>
                            <flux:callout.text>Skriv inn organisasjonsnummer for å automatisk hente firmainformasjon fra Brønnøysundregistrene.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Kontaktpersoner</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Hver kontakt kan ha flere kontaktpersoner med navn, e-post, telefon og rolle.</p>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Aktiviteter</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Loggfør aktiviteter som telefonsamtaler, møter og e-poster for å holde oversikt over kundekommunikasjon:</p>
                        <ul>
                            <li>Klikk på en kontakt for å åpne detaljer</li>
                            <li>Gå til <strong>Aktiviteter</strong>-fanen</li>
                            <li>Klikk <strong>Ny aktivitet</strong></li>
                            <li>Velg type, dato og beskriv aktiviteten</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Tilbud, Ordrer og Fakturaer</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Fra kontaktkortet kan du se alle dokumenter knyttet til kontakten og opprette nye:</p>
                        <ol>
                            <li>Åpne kontakten og gå til <strong>Dokumenter</strong>-fanen</li>
                            <li>Her ser du alle tilbud, ordrer og fakturaer for kontakten</li>
                            <li>Klikk <strong>Nytt tilbud</strong>, <strong>Ny ordre</strong> eller <strong>Ny faktura</strong></li>
                            <li>Dokumentet opprettes med kontakten forhåndsvalgt</li>
                            <li>Legg til linjer med produkter fra vareregisteret</li>
                        </ol>

                        <p><strong>Dokumentdetaljer og hurtighandlinger:</strong></p>
                        <ul>
                            <li>Klikk på et dokument i listen for å se detaljer i en flyout-modal</li>
                            <li>For tilbud: Endre status direkte fra modalen via dropdown-menyen</li>
                            <li>Send dokumentet på e-post med ett klikk - PDF vedlegges automatisk</li>
                            <li>Se når dokumentet sist ble sendt og send på nytt om nødvendig</li>
                            <li>Forhåndsvis eller last ned PDF direkte fra modalen</li>
                        </ul>

                        <flux:callout variant="success" icon="light-bulb" class="not-prose my-4">
                            <flux:callout.heading>Tips: Rask oppfølging</flux:callout.heading>
                            <flux:callout.text>Klikk på et tilbud for å se detaljene, endre status til "Sendt" og send e-post - alt uten å forlate kontaktkortet.</flux:callout.text>
                        </flux:callout>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
