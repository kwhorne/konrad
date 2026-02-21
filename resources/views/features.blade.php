<x-layouts.public
    title="Funksjoner - Konrad Office AS"
    description="Utforsk alle funksjoner og moduler i Konrad Office. Fakturering, regnskap, lønn, lager, prosjekter, kontrakter og mer — alt i ett norsk forretningssystem."
>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-white to-indigo-50/40 dark:from-zinc-950 dark:via-zinc-900 dark:to-indigo-950/20 py-16 lg:py-24">
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-40 right-0 w-[500px] h-[500px] bg-gradient-to-bl from-indigo-100/60 to-transparent dark:from-indigo-900/20 dark:to-transparent rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-gradient-to-tr from-teal-100/40 to-transparent dark:from-teal-900/10 dark:to-transparent rounded-full blur-3xl"></div>
        </div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-6">
                <flux:icon.squares-2x2 class="w-4 h-4 mr-2" />
                14 moduler, ett system
            </div>
            <h1 class="text-4xl sm:text-5xl font-bold text-zinc-900 dark:text-white mb-5 leading-tight">
                Alt du trenger for å drive norsk bedrift
            </h1>
            <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto mb-8">
                Fra tilbud til årsoppgjør — Konrad Office dekker hele driftssyklusen med norsk regelverk innebygd.
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <flux:button href="{{ route('order') }}" variant="primary">
                    Prøv gratis
                </flux:button>
                <flux:button href="{{ route('contact') }}" variant="ghost">
                    Book demo
                </flux:button>
            </div>
        </div>
    </section>

    <!-- Platform features (technical qualities) -->
    <section class="py-16 bg-white dark:bg-zinc-900 border-b border-zinc-100 dark:border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-white mb-3">Bygget for effektivitet</h2>
                <p class="text-zinc-500 dark:text-zinc-400">Moderne teknologi og gjennomtenkt design for en smidig arbeidshverdag</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-6">
                @foreach([
                    ['icon' => 'bolt', 'label' => 'Lynrask', 'desc' => 'Umiddelbar respons', 'color' => 'indigo'],
                    ['icon' => 'device-phone-mobile', 'label' => 'Responsivt', 'desc' => 'Mobil, nettbrett og PC', 'color' => 'indigo'],
                    ['icon' => 'moon', 'label' => 'Mørk modus', 'desc' => 'Lyst og mørkt tema', 'color' => 'indigo'],
                    ['icon' => 'document-arrow-down', 'label' => 'PDF-eksport', 'desc' => 'Profesjonelle PDFer', 'color' => 'indigo'],
                    ['icon' => 'lock-closed', 'label' => 'Sikkerhet', 'desc' => 'Moderne sikring', 'color' => 'indigo'],
                    ['icon' => 'flag', 'label' => 'Norsk', 'desc' => 'Fullt norsk grensesnitt', 'color' => 'indigo'],
                    ['icon' => 'sparkles', 'label' => 'AI-støtte', 'desc' => 'Intelligente forslag', 'color' => 'fuchsia'],
                ] as $f)
                <div class="text-center">
                    <div class="w-12 h-12 {{ $f['color'] === 'fuchsia' ? 'bg-gradient-to-br from-fuchsia-100 to-pink-100 dark:from-fuchsia-900/30 dark:to-pink-900/30' : 'bg-indigo-100 dark:bg-indigo-900/30' }} rounded-xl flex items-center justify-center mx-auto mb-3">
                        <flux:icon :icon="$f['icon']" class="w-6 h-6 {{ $f['color'] === 'fuchsia' ? 'text-fuchsia-600 dark:text-fuchsia-400' : 'text-indigo-600 dark:text-indigo-400' }}" />
                    </div>
                    <div class="font-semibold text-zinc-900 dark:text-white text-sm mb-0.5">{{ $f['label'] }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $f['desc'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- All modules -->
    <section class="py-20 bg-zinc-50 dark:bg-zinc-800/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-3xl sm:text-4xl font-bold text-zinc-900 dark:text-white mb-4">Alle modulene</h2>
                <p class="text-lg text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                    Fra salg og fakturering til regnskap og eiendelsstyring — alt samlet i ett system
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Sales -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-rose-50 to-pink-50 dark:from-rose-900/20 dark:to-pink-900/20 border border-rose-200 dark:border-rose-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-rose-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.shopping-cart class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Salg</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett salgsprosess fra tilbud til faktura med automatisk konvertering og PDF-generering.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-rose-500 shrink-0" />Tilbud med godkjenning</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-rose-500 shrink-0" />Ordrer med sporing</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-rose-500 shrink-0" />Fakturaer med betaling</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-rose-500 shrink-0" />PDF-generering</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Accounting -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900/20 dark:to-teal-900/20 border border-cyan-200 dark:border-cyan-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-cyan-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.calculator class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Regnskap</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Hovedbok med bilagsføring, kunde- og leverandørreskontro basert på norsk standard (NS 4102).
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-cyan-500 shrink-0" />Norsk kontoplan (NS 4102)</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-cyan-500 shrink-0" />Automatisk bokføring</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-cyan-500 shrink-0" />Leverandørfakturaer</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-cyan-500 shrink-0" />Resultat og balanse</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Contacts -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-blue-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.users class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Kontakter</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Hold oversikt over alle kunder og leverandører med kontaktinformasjon og aktivitetslogg.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-blue-500 shrink-0" />Kunder og leverandører</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-blue-500 shrink-0" />Aktivitetslogg</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-blue-500 shrink-0" />Organisasjonsnummer</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-blue-500 shrink-0" />Kontaktpersoner</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border border-emerald-200 dark:border-emerald-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-emerald-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.cube class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Produkter</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Administrer produkter og tjenester med priser, MVA-satser og varegrupper for enkel fakturering.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-emerald-500 shrink-0" />Produkter og tjenester</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-emerald-500 shrink-0" />MVA-satser og enheter</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-emerald-500 shrink-0" />Varegrupper</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-emerald-500 shrink-0" />Prislister</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Inventory -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 border border-teal-200 dark:border-teal-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-teal-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.archive-box class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Lager</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett lagerstyring med innkjøpsordrer, varemottak og varetelling for norsk lovkrav.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-teal-500 shrink-0" />Lagerbeholdning og lokasjoner</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-teal-500 shrink-0" />Innkjøpsordrer og varemottak</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-teal-500 shrink-0" />Varetelling og avviksrapport</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-teal-500 shrink-0" />Lagerbevegelser</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Projects -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 border border-purple-200 dark:border-purple-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-purple-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.folder class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Prosjekter</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Styr prosjekter med budsjett, timer og fremdrift. Koble til kunder og dokumenter.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-purple-500 shrink-0" />Budsjett og timer</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-purple-500 shrink-0" />Prosjektstatuser</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-purple-500 shrink-0" />Kundekobling</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-purple-500 shrink-0" />Fremdriftsrapporter</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Work Orders -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 border border-orange-200 dark:border-orange-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-orange-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.clipboard-document-list class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Arbeidsordrer</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett ordresystem med timeregistrering, prioriteter og statuser.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-orange-500 shrink-0" />Timeregistrering</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-orange-500 shrink-0" />8 statuser og prioriteter</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-orange-500 shrink-0" />Tildeling til ansvarlig</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-orange-500 shrink-0" />Ordrehistorikk</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Contracts -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 border border-amber-200 dark:border-amber-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-amber-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.document-text class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Kontrakter</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Administrer kontrakter med leverandører og kunder, med varsling ved fornyelse.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-amber-500 shrink-0" />Kontraktstyper</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-amber-500 shrink-0" />Fornyelsesvarsel</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-amber-500 shrink-0" />Dokumenthåndtering</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-amber-500 shrink-0" />Kontraktsoversikt</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Assets -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-slate-50 to-gray-50 dark:from-slate-900/20 dark:to-gray-900/20 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-slate-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.computer-desktop class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Eiendeler</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Hold oversikt over maskiner, utstyr og inventar med lokasjon og vedlikeholdsplan.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-slate-500 shrink-0" />Eiendelskategorier</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-slate-500 shrink-0" />Lokasjoner</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-slate-500 shrink-0" />Vedlikehold</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-slate-500 shrink-0" />Avskrivninger</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Time Tracking -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-900/20 dark:to-blue-900/20 border border-sky-200 dark:border-sky-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-sky-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.clock class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Timer</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett timeregistrering med ukelister, godkjenning og rapporter.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-sky-500 shrink-0" />Ukelister og timelister</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-sky-500 shrink-0" />Godkjenningsflyt</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-sky-500 shrink-0" />Timerapporter</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-sky-500 shrink-0" />Prosjektkobling</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Payroll -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-lime-50 to-green-50 dark:from-lime-900/20 dark:to-green-900/20 border border-lime-200 dark:border-lime-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-lime-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.banknotes class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Lønn
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-lime-500 to-green-500 text-white">Ny</span>
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett norsk lønnssystem med skattetrekk, feriepenger og A-melding.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-lime-500 shrink-0" />Lønnskjøring og lønnsslipper</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-lime-500 shrink-0" />Skattetrekk og feriepenger</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-lime-500 shrink-0" />A-melding og AGA</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-lime-500 shrink-0" />Lønnsslipper på PDF</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- My Activities -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/20 dark:to-pink-900/20 border border-fuchsia-200 dark:border-fuchsia-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-fuchsia-500 to-pink-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.sparkles class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Mine aktiviteter
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-fuchsia-500 to-pink-500 text-white">AI</span>
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Intelligente forslag til prioritering basert på dine ventende oppgaver.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-fuchsia-500 shrink-0" />Intelligente forslag</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-fuchsia-500 shrink-0" />Personlige notater</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-fuchsia-500 shrink-0" />Arbeidsmengde-score</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-fuchsia-500 shrink-0" />Oppgaveprioritet</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Annual Accounts -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-50 dark:from-indigo-900/20 dark:to-violet-900/20 border border-indigo-200 dark:border-indigo-800">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-violet-500 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.chart-bar class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                                Årsoppgjør
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-indigo-500 to-violet-500 text-white">AI</span>
                            </h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett årsoppgjør med aksjebok, skatteberegning og selskapsanalyse.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-indigo-500 shrink-0" />Årsregnskap og noter</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-indigo-500 shrink-0" />Aksjebok og transaksjoner</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-indigo-500 shrink-0" />Intelligent selskapsanalyse</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-indigo-500 shrink-0" />Skatteberegning</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Administration -->
                <div class="p-8 rounded-2xl bg-gradient-to-br from-zinc-50 to-neutral-50 dark:from-zinc-800/40 dark:to-neutral-800/40 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-zinc-700 dark:bg-zinc-600 rounded-2xl flex items-center justify-center shrink-0">
                            <flux:icon.cog-6-tooth class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Administrasjon</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Komplett administrasjonspanel for brukere, bedriftsinnstillinger og systemoppsett.
                            </p>
                            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-zinc-500 shrink-0" />Brukerhåndtering</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-zinc-500 shrink-0" />Bedriftsinnstillinger</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-zinc-500 shrink-0" />Kontoplan og MVA</li>
                                <li class="flex items-center gap-2"><flux:icon.check class="w-4 h-4 text-zinc-500 shrink-0" />Roller og tilganger</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Norwegian compliance highlight -->
    <section class="py-16 bg-white dark:bg-zinc-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-950/40 dark:to-blue-950/40 rounded-3xl border border-indigo-100 dark:border-indigo-800/30 p-10 lg:p-14">
                <div class="grid lg:grid-cols-2 gap-10 items-center">
                    <div>
                        <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-5">
                            <flux:icon.flag class="w-4 h-4 mr-2" />
                            100% norsk
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-white mb-4">
                            Norsk regelverk innebygd fra dag én
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                            Konrad Office er bygget for det norske markedet. Alle moduler følger gjeldende norsk lovgivning og rapporteringskrav.
                        </p>
                        <flux:button href="{{ route('order') }}" variant="primary">
                            Start gratis prøveperiode
                        </flux:button>
                    </div>
                    <div class="grid grid-cols-1 gap-3">
                        @foreach([
                            ['icon' => 'building-library', 'title' => 'NS 4102', 'desc' => 'Norsk standard kontoplan'],
                            ['icon' => 'document-chart-bar', 'title' => 'A-melding', 'desc' => 'Automatisk rapportering til Altinn'],
                            ['icon' => 'calculator', 'title' => 'MVA-håndtering', 'desc' => 'Korrekte satser og meldinger'],
                            ['icon' => 'banknotes', 'title' => 'AGA og skattetrekk', 'desc' => 'Beregnes automatisk ved lønnskjøring'],
                            ['icon' => 'chart-bar', 'title' => 'Årsregnskap', 'desc' => 'Etter norsk regnskapsstandard'],
                        ] as $item)
                        <div class="flex items-center gap-4 bg-white dark:bg-zinc-800/50 rounded-xl p-4 border border-indigo-100 dark:border-indigo-800/20">
                            <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg flex items-center justify-center shrink-0">
                                <flux:icon :icon="$item['icon']" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <div class="font-semibold text-zinc-900 dark:text-white text-sm">{{ $item['title'] }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item['desc'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-zinc-900 dark:text-white mb-4">
                Klar til å prøve?
            </h2>
            <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8">
                Start en gratis prøveperiode og se selv hva Konrad Office kan gjøre for din bedrift. Ingen betalingskort kreves.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <flux:button href="{{ route('order') }}" variant="primary" size="lg">
                    Prøv gratis i 30 dager
                </flux:button>
                <flux:button href="{{ route('pricing') }}" variant="ghost" size="lg">
                    Se priser
                </flux:button>
            </div>
        </div>
    </section>
</x-layouts.public>
