{{-- Sikkerhet og 2FA --}}
<flux:card id="sikkerhet" data-section class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 scroll-mt-20">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <flux:icon.shield-check class="w-5 h-5 text-green-600 dark:text-green-400" />
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Sikkerhet og tofaktorautentisering</flux:heading>
            </div>
            <flux:badge color="zinc" size="sm">Innstillinger</flux:badge>
        </div>

        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">
            Tofaktorautentisering (2FA) gir et ekstra lag med sikkerhet for kontoen din.
        </flux:text>

        <flux:accordion transition class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Aktivere tofaktorautentisering</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <ol>
                            <li>Gå til <strong>Innstillinger</strong> i sidemenyen</li>
                            <li>Under <strong>Sikkerhet</strong> klikker du <strong>Aktiver tofaktorautentisering</strong></li>
                            <li>Skann QR-koden med en autentiseringsapp (Google Authenticator, Authy, etc.)</li>
                            <li>Skriv inn bekreftelseskoden fra appen</li>
                            <li>Lagre gjenopprettingskodene et trygt sted</li>
                        </ol>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Gjenopprettingskoder</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p>Når du aktiverer 2FA får du 8 gjenopprettingskoder. Disse kan brukes én gang hver for å logge inn hvis du mister tilgang til autentiseringsappen. Lagre dem sikkert - de vises kun én gang!</p>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading class="text-base font-medium">Karensperiode og låst konto</flux:accordion.heading>
                <flux:accordion.content>
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                        <p><strong>Karensperiode:</strong> Alle brukere har en karensperiode på 5 dager for å aktivere tofaktorautentisering. Etter 5 dager vil kontoen bli låst hvis 2FA ikke er aktivert.</p>

                        <p><strong>Låst konto:</strong> Hvis kontoen din er låst fordi du ikke aktiverte 2FA i tide, kontakt <strong>support@konradoffice.no</strong> for å få kontoen låst opp igjen.</p>

                        <div class="not-prose mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                            <p class="text-green-800 dark:text-green-200 text-sm mb-0">
                                <strong>Tips:</strong> Bruk en sikker autentiseringsapp som Google Authenticator, Authy eller 1Password. Unngå SMS-basert 2FA da det er mindre sikkert.
                            </p>
                        </div>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </div>
</flux:card>
