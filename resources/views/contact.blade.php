<x-layouts.public
    title="Kontakt oss - Konrad Office AS"
    description="Ta kontakt med Konrad Office AS for spørsmål, demo eller support. Vi svarer innen én virkedag."
>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-white to-indigo-50/40 dark:from-zinc-950 dark:via-zinc-900 dark:to-indigo-950/20 py-16 lg:py-24">
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-40 right-0 w-[500px] h-[500px] bg-gradient-to-bl from-indigo-100/60 to-transparent dark:from-indigo-900/20 dark:to-transparent rounded-full blur-3xl"></div>
        </div>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-6">
                <flux:icon.chat-bubble-left-right class="w-4 h-4 mr-2" />
                Vi svarer innen én virkedag
            </div>
            <h1 class="text-4xl sm:text-5xl font-bold text-zinc-900 dark:text-white mb-5 leading-tight">
                Ta kontakt med oss
            </h1>
            <p class="text-xl text-zinc-600 dark:text-zinc-400">
                Har du spørsmål, ønsker du en demo, eller trenger du hjelp? Send oss en melding — vi hjelper deg gjerne.
            </p>
        </div>
    </section>

    <!-- Main content -->
    <section class="py-16 lg:py-24 bg-white dark:bg-zinc-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-5 gap-12 lg:gap-16 items-start">

                <!-- Contact form (3/5) -->
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-8 shadow-sm">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">Send oss en melding</h2>
                        <p class="text-zinc-500 dark:text-zinc-400 mb-8">Fyll ut skjemaet så tar vi kontakt så snart vi kan.</p>
                        <livewire:public-contact-form />
                    </div>
                </div>

                <!-- Contact info (2/5) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Contact cards -->
                    <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-100 dark:border-zinc-700/50 p-6">
                        <h3 class="font-semibold text-zinc-900 dark:text-white mb-5">Kontaktinformasjon</h3>
                        <div class="space-y-5">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center shrink-0">
                                    <flux:icon.phone class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-0.5">Telefon</div>
                                    <a href="tel:+4755612050" class="font-semibold text-zinc-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">+47 55 61 20 50</a>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Man–fre 08:00–16:00</div>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center shrink-0">
                                    <flux:icon.envelope class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-0.5">E-post</div>
                                    <a href="mailto:post@konradoffice.no" class="font-semibold text-zinc-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">post@konradoffice.no</a>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center shrink-0">
                                    <flux:icon.wrench-screwdriver class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-0.5">Support</div>
                                    <a href="mailto:support@konradoffice.no" class="font-semibold text-zinc-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">support@konradoffice.no</a>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">For eksisterende kunder</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Response time -->
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800/30 p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></div>
                            <span class="font-semibold text-emerald-800 dark:text-emerald-300 text-sm">Vi er tilgjengelige</span>
                        </div>
                        <p class="text-sm text-emerald-700 dark:text-emerald-400">
                            Vi svarer normalt innen <strong>én virkedag</strong>. Telefonstøtte er tilgjengelig man–fre 08:00–16:00.
                        </p>
                    </div>

                    <!-- What to expect -->
                    <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-100 dark:border-zinc-700/50 p-6">
                        <h3 class="font-semibold text-zinc-900 dark:text-white mb-4">Hva skjer etter du sender?</h3>
                        <div class="space-y-4">
                            @foreach([
                                ['step' => '1', 'text' => 'Vi mottar meldingen og leser den nøye'],
                                ['step' => '2', 'text' => 'En av oss tar kontakt med deg innen én virkedag'],
                                ['step' => '3', 'text' => 'Vi finner den beste løsningen for din bedrift'],
                            ] as $item)
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center shrink-0 text-white text-xs font-bold mt-0.5">{{ $item['step'] }}</div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $item['text'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- CTA to pricing -->
                    <div class="bg-gradient-to-br from-indigo-600 to-orange-500 rounded-2xl p-6 text-white">
                        <h3 class="font-semibold mb-2">Klar til å komme i gang?</h3>
                        <p class="text-indigo-100 text-sm mb-4">Start en gratis prøveperiode og utforsk systemet selv — ingen betalingskort kreves.</p>
                        <flux:button href="{{ route('order') }}" variant="primary" size="sm" class="bg-white! text-indigo-600! hover:bg-zinc-50!">
                            Prøv gratis
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Feature row -->
    <section class="py-16 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
                <div>
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.clock class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <h3 class="font-semibold text-zinc-900 dark:text-white mb-2">Rask respons</h3>
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm">Vi svarer normalt innen én virkedag på alle henvendelser.</p>
                </div>
                <div>
                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.presentation-chart-line class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <h3 class="font-semibold text-zinc-900 dark:text-white mb-2">Gratis demo</h3>
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm">Book en gratis demo og se hvordan Konrad Office løser dine behov.</p>
                </div>
                <div>
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.flag class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                    </div>
                    <h3 class="font-semibold text-zinc-900 dark:text-white mb-2">Norsk support</h3>
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm">All støtte på norsk fra folk som kjenner norsk regelverk og praksis.</p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
