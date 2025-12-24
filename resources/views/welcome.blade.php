<x-layouts.public title="Konrad - Komplett forretningssystem">
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-orange-50 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
            <div class="text-center">
                <!-- Badge -->
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-8">
                    <flux:icon.building-office class="w-4 h-4 mr-2" />
                    Forretningssystem for norske bedrifter
                </div>

                <!-- Main Heading -->
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-bold text-zinc-900 dark:text-white mb-6">
                    Alt du trenger
                    <span class="bg-gradient-to-r from-indigo-600 to-orange-500 bg-clip-text text-transparent">
                        samlet i ett system
                    </span>
                </h1>

                <!-- Subtitle -->
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-3xl mx-auto mb-12">
                    Konrad er et komplett forretningssystem med kontakthåndtering, vareregister,
                    prosjektstyring og arbeidsordrer. Enkelt, oversiktlig og effektivt.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <flux:button href="{{ route('register') }}" variant="primary" class="px-8 py-3">
                        <flux:icon.rocket-launch class="w-5 h-5 mr-2" />
                        Kom i gang gratis
                    </flux:button>
                    <flux:button href="{{ route('login') }}" variant="ghost" class="px-8 py-3">
                        <flux:icon.arrow-right-end-on-rectangle class="w-5 h-5 mr-2" />
                        Logg inn
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Background decoration -->
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-gradient-to-r from-indigo-200/20 to-orange-200/20 dark:from-indigo-900/10 dark:to-orange-900/10 rounded-full blur-3xl"></div>
        </div>
    </section>

    <!-- Modules Section -->
    <section id="modules" class="py-24 bg-white dark:bg-zinc-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-zinc-900 dark:text-white mb-4">
                    Fire kraftige moduler
                </h2>
                <p class="text-lg text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                    Alt du trenger for å drive din bedrift effektivt - fra kundehåndtering til fakturering
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Contacts Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-blue-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.users class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Kontaktregister
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Hold oversikt over alle kunder og leverandører med fullstendig kontaktinformasjon,
                                organisasjonsnummer og aktivitetslogg.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-blue-500" />
                                    Kunder og leverandører
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-blue-500" />
                                    Aktivitetslogg
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-blue-500" />
                                    Adressehåndtering
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Products Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border border-emerald-200 dark:border-emerald-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-emerald-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.cube class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Vareregister
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Administrer produkter og tjenester med priser, MVA-satser,
                                enheter og varegrupper for enkel fakturering.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-emerald-500" />
                                    Produkter og tjenester
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-emerald-500" />
                                    MVA-satser og enheter
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-emerald-500" />
                                    Varegrupper
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Projects Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 border border-purple-200 dark:border-purple-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-purple-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.folder class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Prosjekter
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Styr prosjekter med budsjett, estimerte timer og fremdriftsstatus.
                                Koble til kunder og legg til prosjektlinjer.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-purple-500" />
                                    Budsjett og timer
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-purple-500" />
                                    Prosjektstatuser
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-purple-500" />
                                    Kundekobling
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Work Orders Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 border border-orange-200 dark:border-orange-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-orange-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.clipboard-document-list class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Arbeidsordrer
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett arbeidsordresystem med timeregistrering, produktlinjer,
                                prioriteter og statuser for full kontroll.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-orange-500" />
                                    Timeregistrering
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-orange-500" />
                                    8 statuser og 4 prioriteter
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-orange-500" />
                                    Tildeling til ansvarlig
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-24 bg-zinc-50 dark:bg-zinc-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-zinc-900 dark:text-white mb-4">
                    Bygget for effektivitet
                </h2>
                <p class="text-lg text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                    Moderne teknologi og gjennomtenkt design for en smidig arbeidshverdag
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.bolt class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Lynrask</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Moderne teknologi gir umiddelbar respons uten ventetid
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.device-phone-mobile class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Responsivt</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Fungerer like godt på mobil, nettbrett og PC
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.moon class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Mork modus</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Velg mellom lyst og morkt tema etter preferanse
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.magnifying-glass class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Kraftig sok</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Finn alt du leter etter med avansert sok og filtrering
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.lock-closed class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Sikkerhet</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Dine data er trygge med moderne sikkerhetstiltak
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.flag class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Norsk</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Fullt norsk grensesnitt tilpasset norske bedrifter
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-white dark:bg-zinc-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">4</div>
                    <div class="text-zinc-600 dark:text-zinc-400">Hovedmoduler</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">8</div>
                    <div class="text-zinc-600 dark:text-zinc-400">Ordrestatuser</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">100%</div>
                    <div class="text-zinc-600 dark:text-zinc-400">Norsk</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">24/7</div>
                    <div class="text-zinc-600 dark:text-zinc-400">Tilgjengelig</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-r from-indigo-600 to-orange-500">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">
                Klar til a ta kontroll over bedriften?
            </h2>
            <p class="text-xl text-indigo-100 mb-8">
                Kom i gang med Konrad i dag. Gratis a prove, enkelt a bruke.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <flux:button href="{{ route('register') }}" variant="primary" class="px-8 py-3 bg-white text-indigo-600 hover:bg-gray-50 border-white">
                    <flux:icon.rocket-launch class="w-5 h-5 mr-2" />
                    Opprett konto
                </flux:button>
                <flux:button href="{{ route('login') }}" variant="ghost" class="px-8 py-3 text-white border-white/20 hover:bg-white/10">
                    Har allerede konto? Logg inn
                </flux:button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-zinc-900 dark:bg-zinc-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2">
                    <flux:icon.building-2 class="w-6 h-6 text-indigo-500" />
                    <span class="text-white font-semibold">Konrad</span>
                </div>
                <p class="text-zinc-500 text-sm">
                    &copy; {{ date('Y') }} Konrad. Alle rettigheter reservert.
                </p>
            </div>
        </div>
    </footer>
</x-layouts.public>
