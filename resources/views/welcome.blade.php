<x-layouts.public
    title="Konrad Office - Komplett forretningssystem for norske bedrifter"
    description="Konrad Office samler fakturering, regnskap, prosjektstyring, kontraktshåndtering og eiendelsregister i ett system. Skreddersydd for norske SMB-bedrifter."
    :open-modal="$openModal ?? null"
>
    @php
        $softwareSchema = [
            "@context" => "https://schema.org",
            "@type" => "SoftwareApplication",
            "name" => "Konrad Office",
            "applicationCategory" => "BusinessApplication",
            "operatingSystem" => "Web",
            "description" => "Komplett forretningssystem med fakturering, regnskap, prosjektstyring og mer for norske bedrifter",
            "offers" => [
                "@type" => "Offer",
                "price" => "399",
                "priceCurrency" => "NOK",
                "priceValidUntil" => now()->addYear()->format('Y-m-d')
            ],
            "aggregateRating" => [
                "@type" => "AggregateRating",
                "ratingValue" => "4.8",
                "reviewCount" => "50"
            ]
        ];
    @endphp
    @push('jsonld')
    <script type="application/ld+json">{!! json_encode($softwareSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endpush
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-orange-50 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
            <div class="text-center">
                <!-- Logo -->
                <div class="mb-8 flex justify-center">
                    <svg class="h-20 w-auto" viewBox="0 0 307 265" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M 0.0,139.5 L 0.5,120.0 C 1.0,100.5 2.0,61.5 13.7,41.0 C 25.3,20.5 47.7,18.5 69.2,19.0 C 90.7,19.5 111.3,22.5 132.0,26.0 L 152.7,29.5 L 152.8,66.5 C 152.8,86.8 152.8,127.3 152.8,147.7 L 152.8,188.0 L 132.2,188.0 C 111.5,188.0 70.0,188.0 49.0,188.0 C 28.0,188.0 27.5,188.0 18.3,174.0 C 9.2,160.0 0.8,132.0 0.4,118.0 C 0.0,104.0 0.0,104.0 0.0,139.5 Z" class="fill-[#457ba7] dark:fill-[#6b9bc4]" fill-rule="evenodd"/>
                        <path d="M 152.7,29.5 L 173.3,33.0 C 194.0,36.5 235.3,43.5 256.0,70.0 C 276.7,96.5 277.0,142.5 276.8,165.0 L 276.5,188.0 L 255.5,188.0 C 234.5,188.0 192.5,188.0 171.5,188.0 L 150.5,188.0 L 150.8,147.7 C 151.2,107.3 152.0,86.8 152.3,66.5 C 152.5,46.2 152.5,46.0 152.7,29.5 Z" class="fill-[#87c8b8] dark:fill-[#a8ddd0]" fill-rule="evenodd"/>
                        <path d="M 0.0,139.5 C 0.0,104.0 0.0,104.0 0.4,118.0 C 0.8,132.0 9.2,160.0 18.3,174.0 C 27.5,188.0 28.0,188.0 49.0,188.0 C 70.0,188.0 111.5,188.0 132.2,188.0 L 152.8,188.0 L 152.8,210.0 C 152.8,222.0 152.5,244.0 152.2,258.5 C 151.8,273.0 151.5,282.5 120.0,275.0 C 88.5,267.5 25.8,243.0 12.0,221.8 C -1.8,200.7 0.2,183.0 0.8,165.2 C 1.5,147.5 0.0,139.5 0.0,139.5 Z" class="fill-[#f2a35a] dark:fill-[#f5b97a]" fill-rule="evenodd"/>
                    </svg>
                </div>

                <!-- Badge -->
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-8">
                    <flux:icon.building-office class="w-4 h-4 mr-2" />
                    Komplett forretningssystem for norske bedrifter
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
                    Konrad Office er et komplett forretningssystem med salg, fakturering, regnskap,
                    prosjektstyring, kontrakter og eiendeler. Enkelt, oversiktlig og effektivt.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <flux:button href="{{ route('login') }}" variant="primary" class="px-8 py-3">
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

    <!-- Social Proof / Testimonials -->
    <section class="py-16 bg-white dark:bg-zinc-900 border-b border-zinc-100 dark:border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-4">
                    Betrodd av norske bedrifter
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-8">
                    <div class="flex gap-1 mb-4">
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                    </div>
                    <blockquote class="text-zinc-700 dark:text-zinc-300 mb-6">
                        "Konrad Office har forenklet hverdagen vår betydelig. Vi har full oversikt over tilbud, ordrer og fakturaer i ett system. Slipper å bruke tid på manuelle oppgaver."
                    </blockquote>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                            KH
                        </div>
                        <div>
                            <div class="font-semibold text-zinc-900 dark:text-white">Kristian Hansen</div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Daglig leder, Hansen Elektro AS</div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-8">
                    <div class="flex gap-1 mb-4">
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                    </div>
                    <blockquote class="text-zinc-700 dark:text-zinc-300 mb-6">
                        "Prosjektstyring og timeregistrering fungerer utmerket. Vi sparer flere timer i uken på administrasjon, og regnskapet er alltid oppdatert."
                    </blockquote>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-full flex items-center justify-center text-white font-semibold">
                            MB
                        </div>
                        <div>
                            <div class="font-semibold text-zinc-900 dark:text-white">Mari Bergström</div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Økonomiansvarlig, Fjordtech Solutions</div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-8">
                    <div class="flex gap-1 mb-4">
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                        <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                    </div>
                    <blockquote class="text-zinc-700 dark:text-zinc-300 mb-6">
                        "Endelig et norsk system som faktisk forstår hvordan vi jobber. Kontraktsregisteret og eiendelsmodulen er gull verdt for oss."
                    </blockquote>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-rose-500 rounded-full flex items-center justify-center text-white font-semibold">
                            OL
                        </div>
                        <div>
                            <div class="font-semibold text-zinc-900 dark:text-white">Ole Larsen</div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Eier, Larsen Bygg & Vedlikehold</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company logos / trust badges -->
            <div class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-700">
                <div class="flex flex-wrap justify-center items-center gap-x-12 gap-y-6 text-zinc-400 dark:text-zinc-500">
                    <div class="flex items-center gap-2">
                        <flux:icon.building-office-2 class="w-5 h-5" />
                        <span class="font-medium">Hansen Elektro AS</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:icon.building-office-2 class="w-5 h-5" />
                        <span class="font-medium">Fjordtech Solutions</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:icon.building-office-2 class="w-5 h-5" />
                        <span class="font-medium">Larsen Bygg & Vedlikehold</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:icon.building-office-2 class="w-5 h-5" />
                        <span class="font-medium">Nordvik Consulting</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:icon.building-office-2 class="w-5 h-5" />
                        <span class="font-medium">Bergen Regnskap</span>
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
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-white dark:bg-zinc-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">9</div>
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

    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-r from-indigo-600 to-orange-500">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">
                Klar til å ta kontroll over bedriften?
            </h2>
            <p class="text-xl text-indigo-100 mb-8">
                Logg inn for å komme i gang med Konrad Office.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <flux:button href="{{ route('login') }}" variant="primary" class="px-8 py-3 bg-white text-indigo-600 hover:bg-gray-50 border-white">
                    <flux:icon.arrow-right-end-on-rectangle class="w-5 h-5 mr-2" />
                    Logg inn
                </flux:button>
            </div>
        </div>
    </section>

    {{-- Social Proof Notification - Flies in from left --}}
    <div x-data="{
        show: false,
        currentIndex: 0,
        notifications: [
            { name: 'Kristian Hansen', initials: 'KH', location: 'Bergen', action: 'startet med', product: 'Pro-planen', time: 'For 2 minutter siden' },
            { name: 'Mari Bergström', initials: 'MB', location: 'Oslo', action: 'oppgraderte til', product: 'Premium', time: 'For 5 minutter siden' },
            { name: 'Ole Larsen', initials: 'OL', location: 'Trondheim', action: 'registrerte seg for', product: 'Basis-planen', time: 'For 8 minutter siden' },
            { name: 'Ingrid Solberg', initials: 'IS', location: 'Stavanger', action: 'startet med', product: 'Pro-planen', time: 'For 12 minutter siden' },
            { name: 'Erik Nordvik', initials: 'EN', location: 'Tromsø', action: 'oppgraderte til', product: 'Premium', time: 'For 15 minutter siden' },
            { name: 'Camilla Vik', initials: 'CV', location: 'Kristiansand', action: 'registrerte seg for', product: 'Pro-planen', time: 'For 18 minutter siden' },
            { name: 'Anders Moen', initials: 'AM', location: 'Drammen', action: 'startet med', product: 'Basis-planen', time: 'For 22 minutter siden' },
            { name: 'Silje Haugen', initials: 'SH', location: 'Ålesund', action: 'oppgraderte til', product: 'Premium', time: 'For 25 minutter siden' },
        ],
        get notification() {
            return this.notifications[this.currentIndex];
        },
        showNotification() {
            this.show = true;
            setTimeout(() => {
                this.show = false;
                this.currentIndex = (this.currentIndex + 1) % this.notifications.length;
            }, 5000);
        },
        init() {
            setTimeout(() => this.showNotification(), 4000);
            setInterval(() => {
                if (!this.show) {
                    this.showNotification();
                }
            }, 30000);
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-500"
    x-transition:enter-start="opacity-0 -translate-x-full"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 -translate-x-full"
    class="fixed bottom-6 left-6 z-50 max-w-sm"
    style="display: none;">
        <div class="relative group">
            {{-- Glow effect --}}
            <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-orange-500 rounded-2xl blur-lg opacity-25 group-hover:opacity-40 transition-opacity duration-500"></div>

            {{-- Card --}}
            <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200/50 dark:border-zinc-700/50 overflow-hidden">
                {{-- Top accent bar --}}
                <div class="h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-orange-500"></div>

                <div class="p-4">
                    <div class="flex items-start gap-4">
                        {{-- Avatar --}}
                        <div class="relative shrink-0">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-indigo-500 to-orange-500 text-white font-bold flex items-center justify-center text-sm shadow-lg" x-text="notification.initials"></div>
                            {{-- Online indicator --}}
                            <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-green-500 rounded-full border-2 border-white dark:border-zinc-900 flex items-center justify-center">
                                <flux:icon.check class="w-2.5 h-2.5 text-white" />
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1">
                                    {{-- Name --}}
                                    <div class="flex items-center gap-1.5 mb-0.5">
                                        <span class="font-semibold text-zinc-900 dark:text-white text-sm" x-text="notification.name"></span>
                                    </div>

                                    {{-- Location --}}
                                    <div class="flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400 mb-2">
                                        <flux:icon.map-pin class="w-3 h-3" />
                                        <span x-text="notification.location + ', Norge'"></span>
                                    </div>

                                    {{-- Action --}}
                                    <div class="inline-flex items-center gap-2 px-2.5 py-1 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-500/10 dark:to-emerald-500/10 rounded-full">
                                        <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                                        <span class="text-xs text-green-700 dark:text-green-400">
                                            <span x-text="notification.action"></span> <span class="font-semibold" x-text="notification.product"></span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Close button --}}
                                <button
                                    @click="show = false"
                                    class="shrink-0 p-1 rounded-lg text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:text-zinc-300 dark:hover:bg-zinc-800 transition-all">
                                    <flux:icon.x-mark class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="mt-3 pt-2 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500">
                            <flux:icon.clock class="w-3 h-3" />
                            <span x-text="notification.time"></span>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-zinc-400 dark:text-zinc-500">
                            <div class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></div>
                            <span>Live</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-layouts.public>
