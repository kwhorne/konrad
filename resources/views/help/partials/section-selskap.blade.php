{{-- Selskap og brukere --}}
<flux:card id="selskap" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.building-office-2 class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Selskap og brukere</flux:heading>
            </div>
            <flux:badge color="zinc" size="sm">Innstillinger</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Konrad Office støtter flere selskaper (multi-tenancy). Hver bruker kan tilhøre ett eller flere selskaper med ulike roller.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Brukerroller</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Det finnes tre roller innenfor et selskap:</p>
                        <ul>
                            <li><strong>Eier</strong> - Full tilgang til selskapet, kan administrere alle innstillinger og brukere</li>
                            <li><strong>Leder (Manager)</strong> - Kan administrere selskapsinnstillinger og invitere brukere</li>
                            <li><strong>Medlem</strong> - Standard bruker med tilgang til selskapets data</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Invitere brukere</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Som eier eller leder kan du invitere nye brukere:</p>
                        <ol>
                            <li>Gå til <strong>Innstillinger</strong> i sidemenyen</li>
                            <li>Velg fanen <strong>Brukere</strong></li>
                            <li>Klikk <strong>Inviter bruker</strong></li>
                            <li>Skriv inn e-postadresse og velg rolle</li>
                        </ol>
                        <p>Hvis brukeren allerede har en konto i Konrad Office, blir de umiddelbart lagt til i selskapet. Nye brukere får en invitasjon på e-post.</p>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Selskapsinnstillinger</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Under fanen <strong>Selskap</strong> i innstillinger kan du redigere:</p>
                        <ul>
                            <li><strong>Grunnleggende informasjon</strong> - Selskapsnavn, organisasjonsnummer, MVA-nummer</li>
                            <li><strong>Kontaktinformasjon</strong> - Adresse, telefon, e-post, nettside</li>
                            <li><strong>Bankopplysninger</strong> - Banknavn, kontonummer, IBAN, SWIFT</li>
                            <li><strong>Firmalogo</strong> - Last opp logo som vises på dokumenter</li>
                            <li><strong>Standardverdier</strong> - Betalingsfrist (dager), tilbudsgyldighet</li>
                            <li><strong>Dokumentmaler</strong> - Faktura-/tilbudsvilkår, dokumentfooter</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Bytte mellom selskaper</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Hvis du tilhører flere selskaper, kan du bytte mellom dem via profilmenyen oppe til høyre. All data du ser vil automatisk filtreres til det valgte selskapet.</p>

                        <div class="not-prose mt-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-amber-800 dark:text-amber-200 text-sm mb-0">
                                <strong>Merk:</strong> Systemadministratorer (is_admin) har tilgang til administrasjonspanelet hvor de kan se alle selskaper og brukere på tvers av systemet.
                            </p>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
