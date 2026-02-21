<x-layouts.public
    title="Bestill - Konrad Office AS"
    description="Start din gratis prøveperiode med Konrad Office. Fyll ut skjemaet, så setter vi opp kontoen din — ingen betalingskort kreves."
>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-white to-indigo-50/40 dark:from-zinc-950 dark:via-zinc-900 dark:to-indigo-950/20 py-16 lg:py-20">
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-40 right-0 w-[500px] h-[500px] bg-gradient-to-bl from-indigo-100/60 to-transparent dark:from-indigo-900/20 dark:to-transparent rounded-full blur-3xl"></div>
        </div>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm font-medium mb-6">
                <flux:icon.check-circle class="w-4 h-4 mr-2" />
                Gratis prøveperiode · Ingen betalingskort
            </div>
            <h1 class="text-4xl sm:text-5xl font-bold text-zinc-900 dark:text-white mb-5 leading-tight">
                Kom i gang med Konrad Office
            </h1>
            <p class="text-xl text-zinc-600 dark:text-zinc-400">
                Fyll ut skjemaet, så setter vi opp kontoen din og sender deg innloggingsinformasjon innen én virkedag.
            </p>
        </div>
    </section>

    <!-- Form section -->
    <section class="py-16 lg:py-20 bg-white dark:bg-zinc-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-12 lg:gap-16 items-start">

                <!-- Order form (3/5) -->
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-8 shadow-sm">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">Bestillingsskjema</h2>
                        <p class="text-zinc-500 dark:text-zinc-400 mb-8">Alle felt merket med * er påkrevd.</p>
                        <livewire:public-order-form />
                    </div>
                </div>

                <!-- Sidebar (2/5) -->
                <div class="lg:col-span-2">
                    <div class="sticky top-20 lg:top-24 space-y-6">

                        <!-- How it works -->
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-100 dark:border-zinc-700/50 p-6">
                            <h3 class="font-semibold text-zinc-900 dark:text-white mb-5">Slik fungerer det</h3>
                            <div class="space-y-5">
                                @foreach([
                                    ['step' => '1', 'title' => 'Send bestilling', 'desc' => 'Fyll ut skjemaet med informasjon om bedriften og valgt plan.'],
                                    ['step' => '2', 'title' => 'Vi tar kontakt', 'desc' => 'En av oss kontakter deg innen én virkedag for å gå gjennom behovene dine.'],
                                    ['step' => '3', 'title' => 'Konto settes opp', 'desc' => 'Vi klargjør systemet og sender innloggingsinformasjon på e-post.'],
                                ] as $item)
                                <div class="flex items-start gap-3">
                                    <div class="w-7 h-7 bg-indigo-600 rounded-full flex items-center justify-center shrink-0 text-white text-xs font-bold mt-0.5">{{ $item['step'] }}</div>
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-white text-sm">{{ $item['title'] }}</div>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $item['desc'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                                <div class="flex items-start gap-3">
                                    <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                                        <flux:icon.check class="w-4 h-4 text-white" />
                                    </div>
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-white text-sm">Kom i gang</div>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Logg inn og ta systemet i bruk. Vi er tilgjengelige for support.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Trust -->
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800/30 p-6">
                            <div class="space-y-3">
                                @foreach([
                                    'Gratis prøveperiode — ingen betalingskort',
                                    'Ingen bindingstid',
                                    'Support på norsk',
                                    'Norsk regelverk innebygd',
                                ] as $point)
                                <div class="flex items-center gap-2.5 text-sm text-emerald-800 dark:text-emerald-300">
                                    <flux:icon.check-circle class="w-4 h-4 text-emerald-500 shrink-0" />
                                    {{ $point }}
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Contact -->
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-100 dark:border-zinc-700/50 p-6">
                            <h3 class="font-semibold text-zinc-900 dark:text-white mb-3">Har du spørsmål?</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">Ta gjerne kontakt med oss før du bestiller.</p>
                            <div class="space-y-2.5 text-sm">
                                <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-400">
                                    <flux:icon.phone class="w-4 h-4 shrink-0" />
                                    <a href="tel:+4755612050" class="hover:text-zinc-900 dark:hover:text-white transition-colors">+47 55 61 20 50</a>
                                </div>
                                <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-400">
                                    <flux:icon.envelope class="w-4 h-4 shrink-0" />
                                    <a href="mailto:post@konradoffice.no" class="text-indigo-600 dark:text-indigo-400 hover:underline">post@konradoffice.no</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</x-layouts.public>
