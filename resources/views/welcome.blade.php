<x-layouts.public
    title="Konrad Office - Komplett forretningssystem for norske bedrifter"
    description="Konrad Office samler salg, lager, innkjøp, fakturering, regnskap, lønn, prosjektstyring, timeregistrering og intelligent selskapsanalyse i ett system. Skreddersydd for norske SMB-bedrifter."
    :open-modal="$openModal ?? null"
>
    @php
        $softwareSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'Konrad Office',
            'applicationCategory' => 'BusinessApplication',
            'applicationSubCategory' => 'AccountingSoftware',
            'operatingSystem' => 'Web',
            'url' => url('/'),
            'inLanguage' => 'nb-NO',
            'description' => 'Komplett norsk forretningssystem med fakturering, regnskap, lønn, lager, prosjektstyring, timeregistrering og aksjonærregister. Skreddersydd for norske SMB-bedrifter med støtte for A-melding, MVA og norsk kontoplan NS 4102.',
            'featureList' => [
                'Fakturering med norsk MVA',
                'Regnskap med kontoplan NS 4102',
                'Lønn med A-melding og skattetrekk',
                'Lagerstyring med innkjøpsordrer og varemottak',
                'Prosjektstyring med timeregistrering',
                'Aksjonærregister og aksjebok',
                'Kontraktstyring med fornyelsesvarsel',
                'Eiendelsstyring',
                'Arbeidsordrer',
                'AI-drevet selskapsanalyse',
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => url('/priser'),
                'price' => '399',
                'priceCurrency' => 'NOK',
                'priceValidUntil' => now()->addYear()->format('Y-m-d'),
            ],
        ];

        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Konrad Office',
            'url' => url('/'),
            'inLanguage' => 'nb-NO',
            'description' => 'Komplett forretningssystem for norske SMB-bedrifter',
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Konrad Office',
            ],
        ];

        $faqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'Hva er Konrad Office?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Konrad Office er et komplett norsk forretningssystem for små og mellomstore bedrifter. Det samler fakturering, regnskap, lønn, lager, prosjektstyring, timeregistrering og aksjonærregister i én løsning — uten behov for separate systemer.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Hvem passer Konrad Office for?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Konrad Office er skreddersydd for norske AS og ENK med 1–50 ansatte. Systemet passer spesielt godt for bedrifter innen handel, bygg og anlegg, konsulentvirksomhet og tjenesteyting som ønsker ett samlet system for hele driften.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Støtter Konrad Office A-melding og lønn?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Ja. Lønnssystemet i Konrad Office håndterer full lønnskjøring med skattetrekk, feriepenger, arbeidsgiveravgift (AGA) og innsending av A-melding til Skatteetaten — alt i henhold til norske regler og frister.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Hvilken kontoplan bruker regnskapssystemet?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Konrad Office bruker norsk standard kontoplan NS 4102 med automatisk bilagsføring, MVA-rapportering og støtte for SAF-T-eksport. Systemet håndterer alle norske MVA-satser (25 %, 15 %, 12 % og 0 %).',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Er aksjonærregister inkludert?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Ja. Konrad Office inkluderer et komplett aksjonærregister med aksjebok, transaksjonshistorikk, kapitalendringer, utbytteberegning og skattemessig skjermingsfradrag.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Har Konrad Office kunstig intelligens (AI)?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Ja. Konrad Office inkluderer AI-drevet selskapsanalyse som gir innsikt i likviditet, lønnsomhet og nøkkeltall, samt intelligente aktivitetsforslag som prioriterer dine viktigste oppgaver.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Hva koster Konrad Office?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Konrad Office tilbyr modulbasert prissetting fra 399 kr per måned. Du betaler kun for de modulene du bruker. Se prissiden for detaljert oversikt over alle pakker og tilleggstjenester.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Kan jeg prøve Konrad Office gratis?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Ja. Du kan starte en gratis prøveperiode uten betalingskort. Gå til bestillingssiden for å opprette din konto og utforske systemet på egen hånd.',
                    ],
                ],
            ],
        ];
    @endphp
    @push('jsonld')
    <script type="application/ld+json">{!! json_encode($softwareSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endpush
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-slate-50 via-white to-indigo-50/40 dark:from-zinc-950 dark:via-zinc-900 dark:to-indigo-950/20">
        <!-- Background decorations -->
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-40 right-0 w-[700px] h-[700px] bg-gradient-to-bl from-indigo-100/70 via-purple-50/30 to-transparent dark:from-indigo-900/20 dark:via-purple-900/10 dark:to-transparent rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-[500px] h-[500px] bg-gradient-to-tr from-orange-100/50 to-transparent dark:from-orange-900/10 dark:to-transparent rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

                <!-- Left: Text content -->
                <div class="text-center lg:text-left">
                    <!-- Logo -->
                    <div class="mb-8 flex justify-center lg:justify-start">
                        <svg class="h-14 w-auto" viewBox="0 0 307 265" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M 0.0,139.5 L 0.5,120.0 C 1.0,100.5 2.0,61.5 13.7,41.0 C 25.3,20.5 47.7,18.5 69.2,19.0 C 90.7,19.5 111.3,22.5 132.0,26.0 L 152.7,29.5 L 152.8,66.5 C 152.8,86.8 152.8,127.3 152.8,147.7 L 152.8,188.0 L 132.2,188.0 C 111.5,188.0 70.0,188.0 49.0,188.0 C 28.0,188.0 27.5,188.0 18.3,174.0 C 9.2,160.0 0.8,132.0 0.4,118.0 C 0.0,104.0 0.0,104.0 0.0,139.5 Z" class="fill-[#457ba7] dark:fill-[#6b9bc4]" fill-rule="evenodd"/>
                            <path d="M 152.7,29.5 L 173.3,33.0 C 194.0,36.5 235.3,43.5 256.0,70.0 C 276.7,96.5 277.0,142.5 276.8,165.0 L 276.5,188.0 L 255.5,188.0 C 234.5,188.0 192.5,188.0 171.5,188.0 L 150.5,188.0 L 150.8,147.7 C 151.2,107.3 152.0,86.8 152.3,66.5 C 152.5,46.2 152.5,46.0 152.7,29.5 Z" class="fill-[#87c8b8] dark:fill-[#a8ddd0]" fill-rule="evenodd"/>
                            <path d="M 0.0,139.5 C 0.0,104.0 0.0,104.0 0.4,118.0 C 0.8,132.0 9.2,160.0 18.3,174.0 C 27.5,188.0 28.0,188.0 49.0,188.0 C 70.0,188.0 111.5,188.0 132.2,188.0 L 152.8,188.0 L 152.8,210.0 C 152.8,222.0 152.5,244.0 152.2,258.5 C 151.8,273.0 151.5,282.5 120.0,275.0 C 88.5,267.5 25.8,243.0 12.0,221.8 C -1.8,200.7 0.2,183.0 0.8,165.2 C 1.5,147.5 0.0,139.5 0.0,139.5 Z" class="fill-[#f2a35a] dark:fill-[#f5b97a]" fill-rule="evenodd"/>
                        </svg>
                    </div>

                    <!-- Badge -->
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-6">
                        <flux:icon.building-office class="w-4 h-4 mr-2" />
                        Skreddersydd for norske SMB-bedrifter
                    </div>

                    <!-- Heading -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-zinc-900 dark:text-white mb-6 leading-tight">
                        Alt du trenger
                        <span class="bg-gradient-to-r from-indigo-600 to-orange-500 bg-clip-text text-transparent">
                            samlet i ett system
                        </span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8 max-w-lg mx-auto lg:mx-0">
                        Konrad Office samler regnskap, lønn, fakturering, lager og prosjektstyring i ett enkelt system — bygget for norske bedrifter.
                    </p>

                    <!-- CTA -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start mb-10">
                        <flux:button href="{{ route('order') }}" variant="primary" class="px-8">
                            Prøv gratis i 30 dager
                        </flux:button>
                        <flux:button href="{{ route('pricing') }}" variant="ghost" class="px-8">
                            Se priser
                        </flux:button>
                    </div>

                    <!-- Trust row -->
                    <div class="flex flex-wrap gap-5 justify-center lg:justify-start text-sm text-zinc-500 dark:text-zinc-400">
                        <div class="flex items-center gap-2">
                            <flux:icon.check-circle class="w-4 h-4 text-green-500 shrink-0" />
                            <span>14 moduler</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:icon.check-circle class="w-4 h-4 text-green-500 shrink-0" />
                            <span>Norsk kontoplan NS&nbsp;4102</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:icon.check-circle class="w-4 h-4 text-green-500 shrink-0" />
                            <span>A-melding og MVA</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Dashboard mockup -->
                <div class="relative lg:px-10 lg:py-14">
                    <!-- Glow behind mockup -->
                    <div class="absolute inset-4 bg-gradient-to-br from-indigo-400/25 to-orange-400/20 dark:from-indigo-500/20 dark:to-orange-500/15 rounded-3xl blur-2xl"></div>

                    <!-- App frame -->
                    <div class="relative bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl border border-zinc-200/80 dark:border-zinc-700/80 overflow-hidden">
                        <!-- Browser chrome -->
                        <div class="flex items-center gap-3 px-4 py-3 bg-zinc-100 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex gap-1.5 shrink-0">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            </div>
                            <div class="flex-1 bg-white dark:bg-zinc-800 rounded-md px-3 py-1 flex items-center gap-2 max-w-xs">
                                <div class="w-2 h-2 rounded-full bg-green-400 shrink-0"></div>
                                <span class="text-xs text-zinc-400 dark:text-zinc-500 truncate">konrad.app/dashboard</span>
                            </div>
                        </div>

                        <!-- Dashboard body -->
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-900">
                            <!-- Dashboard header -->
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">God morgen, Ole</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Februar 2025 · 3 ventende oppgaver</div>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-xs font-bold shrink-0">O</div>
                            </div>

                            <!-- Stat cards -->
                            <div class="grid grid-cols-3 gap-2.5 mb-3">
                                <div class="bg-white dark:bg-zinc-800 rounded-xl p-3 border border-zinc-100 dark:border-zinc-700">
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">Omsetning</div>
                                    <div class="text-sm font-bold text-zinc-900 dark:text-white">842 500</div>
                                    <div class="text-xs text-green-500 font-medium mt-0.5">↑ 12%</div>
                                </div>
                                <div class="bg-white dark:bg-zinc-800 rounded-xl p-3 border border-zinc-100 dark:border-zinc-700">
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">Fakturaer</div>
                                    <div class="text-sm font-bold text-zinc-900 dark:text-white">24</div>
                                    <div class="text-xs text-amber-500 font-medium mt-0.5">4 åpne</div>
                                </div>
                                <div class="bg-white dark:bg-zinc-800 rounded-xl p-3 border border-zinc-100 dark:border-zinc-700">
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">Prosjekter</div>
                                    <div class="text-sm font-bold text-zinc-900 dark:text-white">8</div>
                                    <div class="text-xs text-indigo-500 font-medium mt-0.5">3 aktive</div>
                                </div>
                            </div>

                            <!-- Area chart -->
                            <div class="bg-white dark:bg-zinc-800 rounded-xl p-3.5 border border-zinc-100 dark:border-zinc-700 mb-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Inntekter siste 6 mnd</span>
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">NOK</span>
                                </div>
                                <svg viewBox="0 0 280 60" class="w-full h-12" preserveAspectRatio="none">
                                    <defs>
                                        <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#6366f1" stop-opacity="0.25"/>
                                            <stop offset="100%" stop-color="#6366f1" stop-opacity="0"/>
                                        </linearGradient>
                                    </defs>
                                    <path d="M0,48 L46,38 L92,42 L138,24 L184,28 L230,10 L280,14 L280,60 L0,60 Z" fill="url(#areaGrad)"/>
                                    <path d="M0,48 L46,38 L92,42 L138,24 L184,28 L230,10 L280,14" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="230" cy="10" r="3" fill="#6366f1"/>
                                    <circle cx="280" cy="14" r="3" fill="#6366f1"/>
                                </svg>
                                <div class="flex justify-between text-xs text-zinc-400 dark:text-zinc-500 mt-1">
                                    <span>Sep</span><span>Okt</span><span>Nov</span><span>Des</span><span>Jan</span><span>Feb</span>
                                </div>
                            </div>

                            <!-- Recent invoices -->
                            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-100 dark:border-zinc-700 overflow-hidden">
                                <div class="px-3.5 py-2 border-b border-zinc-100 dark:border-zinc-700">
                                    <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Siste fakturaer</span>
                                </div>
                                <div class="divide-y divide-zinc-50 dark:divide-zinc-700/50">
                                    <div class="flex items-center justify-between px-3.5 py-2">
                                        <div>
                                            <div class="text-xs font-medium text-zinc-800 dark:text-zinc-200">Hansen Elektro AS</div>
                                            <div class="text-xs text-zinc-400 dark:text-zinc-500">FKT-2025-0142</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs font-semibold text-zinc-900 dark:text-white">45 000 kr</div>
                                            <div class="inline-flex px-1.5 py-0.5 rounded text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Betalt</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between px-3.5 py-2">
                                        <div>
                                            <div class="text-xs font-medium text-zinc-800 dark:text-zinc-200">Fjordtech Solutions</div>
                                            <div class="text-xs text-zinc-400 dark:text-zinc-500">FKT-2025-0141</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs font-semibold text-zinc-900 dark:text-white">28 750 kr</div>
                                            <div class="inline-flex px-1.5 py-0.5 rounded text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">Åpen</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between px-3.5 py-2">
                                        <div>
                                            <div class="text-xs font-medium text-zinc-800 dark:text-zinc-200">Nordvik Consulting</div>
                                            <div class="text-xs text-zinc-400 dark:text-zinc-500">FKT-2025-0140</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs font-semibold text-zinc-900 dark:text-white">12 200 kr</div>
                                            <div class="inline-flex px-1.5 py-0.5 rounded text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Betalt</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating card: Lønn -->
                    <div class="absolute -left-10 bottom-20 bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-100 dark:border-zinc-700 p-4 w-44 hidden lg:block">
                        <div class="flex items-center gap-2.5 mb-2">
                            <div class="w-8 h-8 bg-lime-100 dark:bg-lime-900/30 rounded-lg flex items-center justify-center shrink-0">
                                <flux:icon.banknotes class="w-4 h-4 text-lime-600 dark:text-lime-400" />
                            </div>
                            <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Lønn kjørt</div>
                        </div>
                        <div class="text-base font-bold text-zinc-900 dark:text-white">5 ansatte</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Februar 2025</div>
                    </div>

                    <!-- Floating card: AI -->
                    <div class="absolute -right-8 top-20 bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-100 dark:border-zinc-700 p-4 w-52 hidden lg:block">
                        <div class="flex items-center gap-2.5 mb-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-fuchsia-100 to-pink-100 dark:from-fuchsia-900/30 dark:to-pink-900/30 rounded-lg flex items-center justify-center shrink-0">
                                <flux:icon.sparkles class="w-4 h-4 text-fuchsia-600 dark:text-fuchsia-400" />
                            </div>
                            <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">AI-analyse</div>
                        </div>
                        <div class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">Likviditeten er god. 3 fakturaer forfaller denne uken.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modules Section -->
    <section id="modules" class="py-24 bg-white dark:bg-zinc-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-zinc-900 dark:text-white mb-4">
                    Alle modulene du trenger
                </h2>
                <p class="text-lg text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                    Fra salg og fakturering til regnskap og eiendelsstyring - alt samlet i ett system
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Sales Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-rose-50 to-pink-50 dark:from-rose-900/20 dark:to-pink-900/20 border border-rose-200 dark:border-rose-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-rose-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.shopping-cart class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Salg
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett salgsprosess fra tilbud til faktura med automatisk
                                konvertering og PDF-generering.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-rose-500" />
                                    Tilbud med godkjenning
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-rose-500" />
                                    Ordrer med sporing
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-rose-500" />
                                    Fakturaer med betaling
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Accounting Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900/20 dark:to-teal-900/20 border border-cyan-200 dark:border-cyan-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-cyan-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.calculator class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Regnskap
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Hovedbok med bilagsføring, kunde- og leverandørreskontro
                                basert på norsk standard (NS 4102).
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-cyan-500" />
                                    Norsk kontoplan
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-cyan-500" />
                                    Automatisk bokføring
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-cyan-500" />
                                    Leverandørfakturaer
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Contacts Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-blue-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.users class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Kontakter
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Hold oversikt over alle kunder og leverandører med
                                kontaktinformasjon og aktivitetslogg.
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
                                    Organisasjonsnummer
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
                                Produkter
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Administrer produkter og tjenester med priser, MVA-satser
                                og varegrupper for enkel fakturering.
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

                <!-- Inventory Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 border border-teal-200 dark:border-teal-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-teal-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.archive-box class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Lager
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett lagerstyring med innkjøpsordrer, varemottak
                                og varetelling for norsk lovkrav.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-teal-500" />
                                    Lagerbeholdning og lokasjoner
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-teal-500" />
                                    Innkjøpsordrer og varemottak
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-teal-500" />
                                    Varetelling og avviksrapport
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
                                Styr prosjekter med budsjett, timer og fremdrift.
                                Koble til kunder og dokumenter.
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
                                Komplett ordresystem med timeregistrering,
                                prioriteter og statuser.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-orange-500" />
                                    Timeregistrering
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-orange-500" />
                                    8 statuser og prioriteter
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-orange-500" />
                                    Tildeling til ansvarlig
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Contracts Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 border border-amber-200 dark:border-amber-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-amber-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.document-text class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Kontrakter
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Administrer kontrakter med leverandører og kunder,
                                med varsling ved fornyelse.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-amber-500" />
                                    Kontraktstyper
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-amber-500" />
                                    Fornyelsesvarsel
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-amber-500" />
                                    Dokumenthåndtering
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Assets Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-slate-50 to-gray-50 dark:from-slate-900/20 dark:to-gray-900/20 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-slate-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.computer-desktop class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Eiendeler
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Hold oversikt over maskiner, utstyr og inventar
                                med lokasjon og vedlikeholdsplan.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-slate-500" />
                                    Eiendelskategorier
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-slate-500" />
                                    Lokasjoner
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-slate-500" />
                                    Vedlikehold
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Admin Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-zinc-50 to-neutral-50 dark:from-zinc-800/40 dark:to-neutral-800/40 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-zinc-700 dark:bg-zinc-600 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.cog-6-tooth class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Administrasjon
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett administrasjonspanel for brukere,
                                bedriftsinnstillinger og systemoppsett.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-zinc-500" />
                                    Brukerhåndtering
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-zinc-500" />
                                    Bedriftsinnstillinger
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-zinc-500" />
                                    Kontoplan og MVA
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Time Tracking Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-900/20 dark:to-blue-900/20 border border-sky-200 dark:border-sky-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-sky-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.clock class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Timer
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett timeregistrering med ukelister,
                                godkjenning og rapporter.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-sky-500" />
                                    Ukelister og timelister
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-sky-500" />
                                    Godkjenningsflyt
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-sky-500" />
                                    Timerapporter
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Payroll Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-lime-50 to-green-50 dark:from-lime-900/20 dark:to-green-900/20 border border-lime-200 dark:border-lime-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-lime-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.banknotes class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Lønn
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-lime-500 to-green-500 text-white">
                                    Ny
                                </span>
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett norsk lønnssystem med skattetrekk,
                                feriepenger og A-melding.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-lime-500" />
                                    Lønnskjøring og lønnsslipper
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-lime-500" />
                                    Skattetrekk og feriepenger
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-lime-500" />
                                    A-melding og AGA
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- My Activities Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/20 dark:to-pink-900/20 border border-fuchsia-200 dark:border-fuchsia-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-fuchsia-500 to-pink-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.sparkles class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Mine aktiviteter
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-fuchsia-500 to-pink-500 text-white">
                                    AI
                                </span>
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Intelligente forslag til prioritering basert
                                på dine ventende oppgaver.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-fuchsia-500" />
                                    Intelligente forslag
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-fuchsia-500" />
                                    Personlige notater
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-fuchsia-500" />
                                    Arbeidsmengde-score
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Annual Accounts Module -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-50 dark:from-indigo-900/20 dark:to-violet-900/20 border border-indigo-200 dark:border-indigo-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-violet-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.chart-bar class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Årsoppgjør
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-indigo-500 to-violet-500 text-white">
                                    AI
                                </span>
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett årsoppgjør med aksjebok, skatteberegning
                                og selskapsanalyse.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-indigo-500" />
                                    Årsregnskap og noter
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-indigo-500" />
                                    Aksjebok og transaksjoner
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check class="w-4 h-4 text-indigo-500" />
                                    intelligent selskapsanalyse
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-zinc-50 dark:bg-zinc-800/50">
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
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Mørk modus</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Velg mellom lyst og mørkt tema etter preferanse
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.document-arrow-down class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">PDF-eksport</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Generer profesjonelle PDF-er av tilbud, ordrer og fakturaer
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

                <div class="text-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-fuchsia-100 to-pink-100 dark:from-fuchsia-900/30 dark:to-pink-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <flux:icon.sparkles class="w-6 h-6 text-fuchsia-600 dark:text-fuchsia-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Intelligent</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Intelligente forslag og selskapsanalyse
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
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">14</div>
                    <div class="text-zinc-600 dark:text-zinc-400">Hovedmoduler</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">NS 4102</div>
                    <div class="text-zinc-600 dark:text-zinc-400">Norsk kontoplan</div>
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

    <!-- FAQ Section -->
    <section id="faq" class="py-24 bg-white dark:bg-zinc-900">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-zinc-900 dark:text-white mb-4">
                    Ofte stilte spørsmål
                </h2>
                <p class="text-lg text-zinc-600 dark:text-zinc-400">
                    Alt du lurer på om Konrad Office
                </p>
            </div>

            <flux:accordion exclusive>
                <flux:accordion.item>
                    <flux:accordion.heading>Hva er Konrad Office?</flux:accordion.heading>
                    <flux:accordion.content>
                        <p class="text-zinc-600 dark:text-zinc-400">
                            Konrad Office er et komplett norsk forretningssystem for små og mellomstore bedrifter. Det samler fakturering, regnskap, lønn, lager, prosjektstyring, timeregistrering og aksjonærregister i én løsning — uten behov for separate systemer.
                        </p>
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Hvem passer Konrad Office for?</flux:accordion.heading>
                    <flux:accordion.content>
                        <p class="text-zinc-600 dark:text-zinc-400">
                            Systemet er skreddersydd for norske AS og ENK med 1–50 ansatte. Det passer spesielt godt for bedrifter innen handel, bygg og anlegg, konsulentvirksomhet og tjenesteyting som ønsker ett samlet system for hele driften.
                        </p>
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Støtter systemet A-melding og lønn?</flux:accordion.heading>
                    <flux:accordion.content>
                        <p class="text-zinc-600 dark:text-zinc-400">
                            Ja. Lønnssystemet håndterer full lønnskjøring med skattetrekk, feriepenger, arbeidsgiveravgift (AGA) og innsending av A-melding til Skatteetaten — alt i henhold til norske regler og frister.
                        </p>
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Hvilken kontoplan bruker regnskapssystemet?</flux:accordion.heading>
                    <flux:accordion.content>
                        <p class="text-zinc-600 dark:text-zinc-400">
                            Konrad Office bruker norsk standard kontoplan NS 4102 med automatisk bilagsføring og MVA-rapportering. Systemet håndterer alle norske MVA-satser (25&nbsp;%, 15&nbsp;%, 12&nbsp;% og 0&nbsp;%).
                        </p>
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Er aksjonærregister inkludert?</flux:accordion.heading>
                    <flux:accordion.content>
                        <p class="text-zinc-600 dark:text-zinc-400">
                            Ja. Konrad Office inkluderer et komplett aksjonærregister med aksjebok, transaksjonshistorikk, kapitalendringer, utbytteberegning og skattemessig skjermingsfradrag.
                        </p>
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Har Konrad Office kunstig intelligens (AI)?</flux:accordion.heading>
                    <flux:accordion.content>
                        <p class="text-zinc-600 dark:text-zinc-400">
                            Ja. Konrad Office inkluderer AI-drevet selskapsanalyse som gir innsikt i likviditet, lønnsomhet og nøkkeltall, samt intelligente aktivitetsforslag som prioriterer dine viktigste oppgaver automatisk.
                        </p>
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Hva koster Konrad Office?</flux:accordion.heading>
                    <flux:accordion.content>
                        <p class="text-zinc-600 dark:text-zinc-400">
                            Konrad Office tilbyr modulbasert prissetting fra 399&nbsp;kr per måned. Du betaler kun for de modulene du bruker. <a href="{{ route('pricing') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Se prissiden</a> for detaljert oversikt.
                        </p>
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Kan jeg prøve Konrad Office gratis?</flux:accordion.heading>
                    <flux:accordion.content>
                        <p class="text-zinc-600 dark:text-zinc-400">
                            Ja. Du kan starte en gratis prøveperiode uten betalingskort. Gå til <a href="{{ route('order') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">bestillingssiden</a> for å opprette din konto og utforske systemet på egen hånd.
                        </p>
                    </flux:accordion.content>
                </flux:accordion.item>
            </flux:accordion>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-r from-indigo-600 to-orange-500">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">
                Klar til å ta kontroll over bedriften?
            </h2>
            <p class="text-xl text-indigo-100 mb-8">
                Start en gratis prøveperiode og se hva Konrad Office kan gjøre for deg — ingen betalingskort kreves.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <flux:button href="{{ route('order') }}" variant="primary" class="px-8 bg-white! text-indigo-600! hover:bg-zinc-50!">
                    Prøv gratis i 30 dager
                </flux:button>
                <flux:button href="{{ route('login') }}" variant="ghost" class="px-8 text-white! border-white/40! hover:bg-white/10!">
                    Logg inn
                </flux:button>
            </div>
        </div>
    </section>


</x-layouts.public>
