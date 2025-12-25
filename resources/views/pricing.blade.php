<x-layouts.public
    title="Priser - Konrad Office forretningssystem"
    description="Se priser for Konrad Office. Velg mellom Start, Profesjonell og Enterprise. Fra 399 kr/mnd. Inkluderer support og oppdateringer."
>
    <!-- Pricing Header -->
    <section class="py-16 lg:py-24 bg-gradient-to-br from-indigo-50 via-white to-orange-50 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-4xl sm:text-5xl font-bold text-zinc-900 dark:text-white mb-6">
                    Enkel og transparent prising
                </h1>
                <p class="text-xl text-zinc-600 dark:text-zinc-400">
                    Velg planen som passer din bedrift. Alle planer inkluderer support og oppdateringer.
                </p>
            </div>
        </div>
    </section>

    <!-- Pricing Cards -->
    <section class="py-16 lg:py-24 bg-white dark:bg-zinc-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                <!-- Basis Plan -->
                <div class="relative rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-8 shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Basis</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 text-sm">For mindre bedrifter som trenger det grunnleggende</p>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-baseline">
                            <span class="text-4xl font-bold text-zinc-900 dark:text-white">380,-</span>
                            <span class="text-zinc-600 dark:text-zinc-400 ml-2">/ mnd</span>
                        </div>
                    </div>

                    <flux:button href="{{ route('order', ['plan' => 'basis']) }}" variant="outline" class="w-full mb-8">
                        Kom i gang
                    </flux:button>

                    <div class="space-y-4">
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">Inkluderer:</p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Ubegrenset antall brukere
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Regnskap
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Tilbud
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Ordre
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Faktura
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                CRM
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Pro Plan (Popular) -->
                <div class="relative rounded-2xl border-2 border-indigo-600 bg-white dark:bg-zinc-800 p-8 shadow-lg">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                        <span class="bg-indigo-600 text-white text-sm font-medium px-4 py-1 rounded-full">
                            Mest populær
                        </span>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Pro</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 text-sm">For bedrifter som trenger prosjektstyring</p>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-baseline">
                            <span class="text-4xl font-bold text-zinc-900 dark:text-white">890,-</span>
                            <span class="text-zinc-600 dark:text-zinc-400 ml-2">/ mnd</span>
                        </div>
                    </div>

                    <flux:button href="{{ route('order', ['plan' => 'pro']) }}" variant="primary" class="w-full mb-8">
                        Kom i gang
                    </flux:button>

                    <div class="space-y-4">
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">Alt i Basis, pluss:</p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Ubegrenset antall brukere
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Regnskap
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Tilbud
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Ordre
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Faktura
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                CRM
                            </li>
                            <li class="flex items-center gap-3 text-sm text-indigo-600 dark:text-indigo-400 font-medium">
                                <flux:icon.check class="w-5 h-5 text-indigo-500 shrink-0" />
                                Prosjektstyring
                            </li>
                            <li class="flex items-center gap-3 text-sm text-indigo-600 dark:text-indigo-400 font-medium">
                                <flux:icon.check class="w-5 h-5 text-indigo-500 shrink-0" />
                                Timeregistrering
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div class="relative rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-8 shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Premium</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 text-sm">For bedrifter som trenger alt</p>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-baseline">
                            <span class="text-4xl font-bold text-zinc-900 dark:text-white">1 890,-</span>
                            <span class="text-zinc-600 dark:text-zinc-400 ml-2">/ mnd</span>
                        </div>
                    </div>

                    <flux:button href="{{ route('order', ['plan' => 'premium']) }}" variant="outline" class="w-full mb-8">
                        Kom i gang
                    </flux:button>

                    <div class="space-y-4">
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">Alt i Pro, pluss:</p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Ubegrenset antall brukere
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Regnskap
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Tilbud
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Ordre
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Faktura
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                CRM
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Prosjektstyring
                            </li>
                            <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-5 h-5 text-emerald-500 shrink-0" />
                                Timeregistrering
                            </li>
                            <li class="flex items-center gap-3 text-sm text-orange-600 dark:text-orange-400 font-medium">
                                <flux:icon.check class="w-5 h-5 text-orange-500 shrink-0" />
                                Arbeidsordre
                            </li>
                            <li class="flex items-center gap-3 text-sm text-orange-600 dark:text-orange-400 font-medium">
                                <flux:icon.check class="w-5 h-5 text-orange-500 shrink-0" />
                                Kontraktsregister
                            </li>
                            <li class="flex items-center gap-3 text-sm text-orange-600 dark:text-orange-400 font-medium">
                                <flux:icon.check class="w-5 h-5 text-orange-500 shrink-0" />
                                Eiendelsregister
                            </li>
                            <li class="flex items-center gap-3 text-sm text-orange-600 dark:text-orange-400 font-medium">
                                <flux:icon.check class="w-5 h-5 text-orange-500 shrink-0" />
                                Dedikert URL og database
                            </li>
                            <li class="flex items-center gap-3 text-sm text-orange-600 dark:text-orange-400 font-medium">
                                <flux:icon.check class="w-5 h-5 text-orange-500 shrink-0" />
                                API-tilgang
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Trust indicators -->
            <div class="mt-16 text-center">
                <div class="inline-flex flex-wrap justify-center gap-x-8 gap-y-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <div class="flex items-center gap-2">
                        <flux:icon.check-circle class="w-5 h-5 text-emerald-500" />
                        Ubegrenset antall brukere
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:icon.check-circle class="w-5 h-5 text-emerald-500" />
                        Ingen transaksjonsgebyrer
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:icon.check-circle class="w-5 h-5 text-emerald-500" />
                        Ingen bindingstid
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:icon.check-circle class="w-5 h-5 text-emerald-500" />
                        Ingen etableringsgebyr
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 lg:py-24 bg-zinc-50 dark:bg-zinc-800/50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-white text-center mb-12">
                Ofte stilte spørsmål
            </h2>

            <div class="space-y-6">
                <flux:accordion>
                    <flux:accordion.item>
                        <flux:accordion.heading>
                            Kan jeg bytte plan senere?
                        </flux:accordion.heading>
                        <flux:accordion.content>
                            Ja, du kan oppgradere eller nedgradere planen din når som helst. Endringen trer i kraft fra neste faktureringsperiode.
                        </flux:accordion.content>
                    </flux:accordion.item>

                    <flux:accordion.item>
                        <flux:accordion.heading>
                            Er det bindingstid?
                        </flux:accordion.heading>
                        <flux:accordion.content>
                            Nei, det er ingen bindingstid. Du kan avslutte abonnementet når som helst, og du beholder tilgang ut inneværende periode.
                        </flux:accordion.content>
                    </flux:accordion.item>

                    <flux:accordion.item>
                        <flux:accordion.heading>
                            Hva inkluderer support?
                        </flux:accordion.heading>
                        <flux:accordion.content>
                            Alle planer inkluderer e-postsupport. Premium-kunder får i tillegg prioritert support og dedikert kontaktperson.
                        </flux:accordion.content>
                    </flux:accordion.item>

                    <flux:accordion.item>
                        <flux:accordion.heading>
                            Hvordan fungerer dedikert URL og database?
                        </flux:accordion.heading>
                        <flux:accordion.content>
                            Med Premium-planen får du din egen URL (f.eks. dittfirma.konradoffice.no) og en egen database som gir bedre ytelse og sikkerhet.
                        </flux:accordion.content>
                    </flux:accordion.item>
                </flux:accordion>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 lg:py-24 bg-gradient-to-r from-indigo-600 to-orange-500">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">
                Klar til å komme i gang?
            </h2>
            <p class="text-xl text-indigo-100 mb-8">
                Kontakt oss for en demo eller for å få tilgang til systemet.
            </p>
            <flux:button href="mailto:post@konradoffice.no" variant="primary" class="px-8 py-3 bg-white text-indigo-600 hover:bg-gray-50 border-white">
                <flux:icon.envelope class="w-5 h-5 mr-2" />
                Kontakt oss
            </flux:button>
        </div>
    </section>
</x-layouts.public>
