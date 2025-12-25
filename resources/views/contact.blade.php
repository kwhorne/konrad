<x-layouts.public title="Kontakt oss - Konrad Office">
    <!-- Contact Header -->
    <section class="py-16 lg:py-24 bg-gradient-to-br from-indigo-50 via-white to-orange-50 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-4xl sm:text-5xl font-bold text-zinc-900 dark:text-white mb-6">
                    Kontakt oss
                </h1>
                <p class="text-xl text-zinc-600 dark:text-zinc-400">
                    Vi er her for å hjelpe deg. Ta kontakt med oss for spørsmål, demo eller support.
                </p>
            </div>
        </div>
    </section>

    <!-- Contact Cards -->
    <section class="py-16 lg:py-24 bg-zinc-900 dark:bg-zinc-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-16">
                <!-- General Contact -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">
                        Hvordan kan vi hjelpe deg?
                    </h2>
                    <p class="text-zinc-400 mb-8">
                        Kontakt oss om du har noen spørsmål eller om du ønsker en demo av Konrad Office. Kontakt oss gjerne via e-post eller telefon.
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <flux:icon.phone class="w-5 h-5 text-indigo-400 mt-1 shrink-0" />
                            <div>
                                <p class="text-white font-medium">+47 55 61 20 50</p>
                                <p class="text-zinc-500 text-sm">Man-Fre 08:00 - 16:00</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <flux:icon.envelope class="w-5 h-5 text-indigo-400 mt-1 shrink-0" />
                            <div>
                                <a href="mailto:post@konradoffice.no" class="text-indigo-400 hover:text-indigo-300 transition-colors">
                                    post@konradoffice.no
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">
                        Support
                    </h2>
                    <p class="text-zinc-400 mb-8">
                        For våre eksisterende kunder - kontakt oss via telefon eller e-post for driftsstøtte.
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <flux:icon.phone class="w-5 h-5 text-indigo-400 mt-1 shrink-0" />
                            <div>
                                <p class="text-white font-medium">+47 55 61 20 50</p>
                                <p class="text-zinc-500 text-sm">Man-Fre 08:00 - 16:00</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <flux:icon.envelope class="w-5 h-5 text-indigo-400 mt-1 shrink-0" />
                            <div>
                                <a href="mailto:support@konradoffice.no" class="text-indigo-400 hover:text-indigo-300 transition-colors">
                                    support@konradoffice.no
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Additional Info -->
    <section class="py-16 lg:py-24 bg-white dark:bg-zinc-800">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.clock class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Rask respons</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        Vi svarer normalt innen 24 timer på hverdager.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.academic-cap class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Gratis demo</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        Book en gratis demo og se hvordan Konrad Office kan hjelpe din bedrift.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.heart class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Dedikert support</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        Premium-kunder får prioritert support og dedikert kontaktperson.
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
