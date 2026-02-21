<x-layouts.public
    title="Priser - Konrad Office AS"
    description="Enkle og transparente priser for Konrad Office. Basis fra 380 kr/mnd, Pro fra 890 kr/mnd, Premium fra 1 890 kr/mnd. Ingen bindingstid, ingen skjulte kostnader."
>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-white to-indigo-50/40 dark:from-zinc-950 dark:via-zinc-900 dark:to-indigo-950/20 py-16 lg:py-24">
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-40 right-0 w-[500px] h-[500px] bg-gradient-to-bl from-indigo-100/60 to-transparent dark:from-indigo-900/20 dark:to-transparent rounded-full blur-3xl"></div>
        </div>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-6">
                <flux:icon.tag class="w-4 h-4 mr-2" />
                Ingen bindingstid · Ingen skjulte kostnader
            </div>
            <h1 class="text-4xl sm:text-5xl font-bold text-zinc-900 dark:text-white mb-5 leading-tight">
                Enkel og transparent prising
            </h1>
            <p class="text-xl text-zinc-600 dark:text-zinc-400">
                Velg planen som passer din bedrift. Alle planer inkluderer support, oppdateringer og gratis prøveperiode.
            </p>
        </div>
    </section>

    <!-- Pricing Cards -->
    <section class="py-16 lg:py-20 bg-white dark:bg-zinc-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                <!-- Basis -->
                <div class="relative rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-8 flex flex-col shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-1">Basis</h3>
                        <p class="text-zinc-500 dark:text-zinc-400 text-sm">For mindre bedrifter som trenger det grunnleggende</p>
                    </div>
                    <div class="mb-6">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-bold text-zinc-900 dark:text-white">380</span>
                            <span class="text-zinc-500 dark:text-zinc-400 text-sm">kr / mnd</span>
                        </div>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">eks. MVA</p>
                    </div>
                    <flux:button href="{{ route('order', ['plan' => 'basis']) }}" variant="outline" class="w-full mb-8">
                        Prøv gratis
                    </flux:button>
                    <div class="space-y-3 flex-1">
                        <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Inkluderer</p>
                        <ul class="space-y-2.5">
                            @foreach([
                                'Ubegrenset antall brukere',
                                'Regnskap (NS 4102)',
                                'Tilbud og ordre',
                                'Fakturering med MVA',
                                'CRM — kunder og leverandører',
                                'Produkter og lager',
                                'E-postsupport',
                            ] as $feature)
                            <li class="flex items-center gap-2.5 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.check class="w-4 h-4 text-emerald-500 shrink-0" />
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Pro (popular) -->
                <div class="relative rounded-2xl border-2 border-indigo-600 bg-white dark:bg-zinc-800 p-8 flex flex-col shadow-xl">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                        <span class="bg-indigo-600 text-white text-xs font-semibold px-4 py-1.5 rounded-full whitespace-nowrap">
                            Mest populær
                        </span>
                    </div>
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-1">Pro</h3>
                        <p class="text-zinc-500 dark:text-zinc-400 text-sm">For bedrifter som trenger prosjektstyring og timer</p>
                    </div>
                    <div class="mb-6">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-bold text-zinc-900 dark:text-white">890</span>
                            <span class="text-zinc-500 dark:text-zinc-400 text-sm">kr / mnd</span>
                        </div>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">eks. MVA</p>
                    </div>
                    <flux:button href="{{ route('order', ['plan' => 'pro']) }}" variant="primary" class="w-full mb-8">
                        Prøv gratis
                    </flux:button>
                    <div class="space-y-3 flex-1">
                        <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Alt i Basis, pluss</p>
                        <ul class="space-y-2.5">
                            @foreach([
                                'Prosjektstyring med budsjett',
                                'Timeregistrering og godkjenning',
                                'Timerapporter',
                                'Arbeidsordrer',
                                'Mine aktiviteter (AI)',
                            ] as $feature)
                            <li class="flex items-center gap-2.5 text-sm text-indigo-700 dark:text-indigo-300 font-medium">
                                <flux:icon.check class="w-4 h-4 text-indigo-500 shrink-0" />
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Premium -->
                <div class="relative rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-8 flex flex-col shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-1">Premium</h3>
                        <p class="text-zinc-500 dark:text-zinc-400 text-sm">For bedrifter som trenger alt, inkludert lønn og årsoppgjør</p>
                    </div>
                    <div class="mb-6">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-bold text-zinc-900 dark:text-white">1 890</span>
                            <span class="text-zinc-500 dark:text-zinc-400 text-sm">kr / mnd</span>
                        </div>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">eks. MVA</p>
                    </div>
                    <flux:button href="{{ route('order', ['plan' => 'premium']) }}" variant="outline" class="w-full mb-8">
                        Prøv gratis
                    </flux:button>
                    <div class="space-y-3 flex-1">
                        <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Alt i Pro, pluss</p>
                        <ul class="space-y-2.5">
                            @foreach([
                                'Lønn med skattetrekk og feriepenger',
                                'A-melding og AGA',
                                'Kontrakter med fornyelsesvarsel',
                                'Eiendels- og vedlikeholdsregister',
                                'Årsoppgjør med aksjebok',
                                'AI-drevet selskapsanalyse',
                                'Dedikert URL og database',
                                'API-tilgang',
                                'Prioritert support',
                            ] as $feature)
                            <li class="flex items-center gap-2.5 text-sm text-orange-700 dark:text-orange-300 font-medium">
                                <flux:icon.check class="w-4 h-4 text-orange-500 shrink-0" />
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Trust indicators -->
            <div class="mt-12 flex flex-wrap justify-center gap-x-4 sm:gap-x-8 gap-y-3 text-sm text-zinc-500 dark:text-zinc-400">
                <div class="flex items-center gap-2">
                    <flux:icon.check-circle class="w-5 h-5 text-emerald-500 shrink-0" />
                    Ingen bindingstid
                </div>
                <div class="flex items-center gap-2">
                    <flux:icon.check-circle class="w-5 h-5 text-emerald-500 shrink-0" />
                    Ingen etableringsgebyr
                </div>
                <div class="flex items-center gap-2">
                    <flux:icon.check-circle class="w-5 h-5 text-emerald-500 shrink-0" />
                    Ingen transaksjonsgebyrer
                </div>
                <div class="flex items-center gap-2">
                    <flux:icon.check-circle class="w-5 h-5 text-emerald-500 shrink-0" />
                    Gratis prøveperiode
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 lg:py-24 bg-zinc-50 dark:bg-zinc-800/50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-white mb-3">Ofte stilte spørsmål</h2>
                <p class="text-zinc-500 dark:text-zinc-400">Alt du lurer på om priser og abonnement</p>
            </div>
            <flux:accordion exclusive>
                <flux:accordion.item>
                    <flux:accordion.heading>Kan jeg prøve gratis?</flux:accordion.heading>
                    <flux:accordion.content>
                        <p class="text-zinc-600 dark:text-zinc-400">Ja. Alle planer inkluderer en gratis prøveperiode uten betalingskort. Du får full tilgang til alle funksjoner i planen du velger.</p>
                    </flux:accordion.content>
                </flux:accordion.item>
                <flux:accordion.item>
                    <flux:accordion.heading>Kan jeg bytte plan?</flux:accordion.heading>
                    <flux:accordion.content>
                        <p class="text-zinc-600 dark:text-zinc-400">Ja, du kan oppgradere eller nedgradere planen din når som helst. Endringen trer i kraft fra neste faktureringsperiode.</p>
                    </flux:accordion.content>
                </flux:accordion.item>
                <flux:accordion.item>
                    <flux:accordion.heading>Er det bindingstid?</flux:accordion.heading>
                    <flux:accordion.content>
                        <p class="text-zinc-600 dark:text-zinc-400">Nei. Det er ingen bindingstid. Du kan avslutte abonnementet når som helst og beholder tilgang ut inneværende periode.</p>
                    </flux:accordion.content>
                </flux:accordion.item>
                <flux:accordion.item>
                    <flux:accordion.heading>Hva inkluderer support?</flux:accordion.heading>
                    <flux:accordion.content>
                        <p class="text-zinc-600 dark:text-zinc-400">Alle planer inkluderer e-postsupport på norsk. Premium-kunder får i tillegg prioritert support og dedikert kontaktperson.</p>
                    </flux:accordion.content>
                </flux:accordion.item>
                <flux:accordion.item>
                    <flux:accordion.heading>Hva er dedikert URL og database?</flux:accordion.heading>
                    <flux:accordion.content>
                        <p class="text-zinc-600 dark:text-zinc-400">Med Premium-planen får du din egen URL (f.eks. dittfirma.konradoffice.no) og en isolert database — for bedre ytelse, sikkerhet og tilpasning.</p>
                    </flux:accordion.content>
                </flux:accordion.item>
            </flux:accordion>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 lg:py-24 bg-gradient-to-r from-indigo-600 to-orange-500">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-5">
                Klar til å komme i gang?
            </h2>
            <p class="text-xl text-indigo-100 mb-8">
                Start en gratis prøveperiode — ingen betalingskort, ingen forpliktelser.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <flux:button href="{{ route('order') }}" variant="primary" class="px-8 bg-white! text-indigo-600! hover:bg-zinc-50!">
                    Prøv gratis i 30 dager
                </flux:button>
                <flux:button href="{{ route('contact') }}" variant="ghost" class="px-8 text-white! border-white/40! hover:bg-white/10!">
                    Book en demo
                </flux:button>
            </div>
        </div>
    </section>
</x-layouts.public>
