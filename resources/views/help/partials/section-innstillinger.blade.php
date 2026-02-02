{{-- Innstillinger --}}
<flux:card id="innstillinger" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.cog-6-tooth class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Innstillinger</flux:heading>
            </div>
            <flux:badge color="zinc" size="sm">Innstillinger</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Innstillingssiden er organisert i faner for enkel navigering.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Min konto</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Personlige innstillinger for din brukerkonto:</p>
                        <ul>
                            <li><strong>Profilinformasjon</strong> - Se navn og e-postadresse</li>
                            <li><strong>Sikkerhet</strong> - Endre passord og administrer tofaktorautentisering (2FA)</li>
                            <li><strong>Utseende</strong> - Velg lyst, mørkt eller automatisk tema</li>
                            <li><strong>Varsler</strong> - Administrer e-post- og push-varsler</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Selskap (kun for eiere/ledere)</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Hvis du er eier eller leder av selskapet, vil du se en ekstra fane for selskapsinnstillinger:</p>
                        <ul>
                            <li>Rediger selskapsinformasjon (navn, organisasjonsnummer, adresse)</li>
                            <li>Last opp firmalogo</li>
                            <li>Konfigurer bankopplysninger</li>
                            <li>Sett standardverdier for betalingsbetingelser</li>
                            <li>Tilpass dokumentfooter og vilkår</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Brukere (kun for eiere/ledere)</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Administrer brukerne i ditt selskap:</p>
                        <ul>
                            <li>Inviter nye brukere via e-post</li>
                            <li>Tildel roller (medlem eller leder)</li>
                            <li>Fjern brukere fra selskapet</li>
                        </ul>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
