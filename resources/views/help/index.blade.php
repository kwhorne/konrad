<x-layouts.app title="Brukerdokumentasjon">
    <div x-data="{
        searchQuery: '',
        activeSection: 'kom-i-gang',
        showBackToTop: false,
        init() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.activeSection = entry.target.id;
                    }
                });
            }, { rootMargin: '-100px 0px -70% 0px' });

            document.querySelectorAll('[data-section]').forEach(section => {
                observer.observe(section);
            });

            window.addEventListener('scroll', () => {
                this.showBackToTop = window.scrollY > 500;
            });
        },
        scrollToSection(id) {
            const el = document.getElementById(id);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }" class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="help" />
        <x-app-header current="help" />

        {{-- Hero Section - Compact --}}
        <section class="bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-800 dark:to-indigo-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                        <flux:icon.book-open class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <flux:heading size="lg" level="1" class="text-white">
                            Brukerdokumentasjon
                        </flux:heading>
                        <flux:text class="text-sm text-white/80">
                            Komplett veiledning for Konrad Office forretningssystem. Finn svar på alt du lurer på.
                        </flux:text>
                    </div>
                </div>
            </div>
        </section>

        <flux:main class="bg-zinc-50 dark:bg-zinc-800 !pt-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                {{-- Sidebar Navigation --}}
                <div class="lg:col-span-1">
                    {{-- Mobile dropdown --}}
                    <div class="lg:hidden mb-4">
                        <flux:select x-model="activeSection" @change="scrollToSection($event.target.value)">
                            <option value="kom-i-gang">Kom i gang</option>
                            <option value="dashboard">Dashboard</option>
                            <option value="mine-aktiviteter">Mine aktiviteter</option>
                            <option value="kontakter">Kontaktregister</option>
                            <option value="produkter">Vareregister</option>
                            <option value="prosjekter">Prosjekter</option>
                            <option value="arbeidsordrer">Arbeidsordrer</option>
                            <option value="timeregistrering">Timeregistrering</option>
                            <option value="salg">Salg</option>
                            <option value="okonomi">Økonomi</option>
                            <option value="selskapsanalyse">Selskapsanalyse</option>
                            <option value="innboks">Innboks</option>
                            <option value="rapporter">Rapporter</option>
                            <option value="mva">MVA-meldinger</option>
                            <option value="aksjonaerregister">Aksjonærregister</option>
                            <option value="skatt">Skatt</option>
                            <option value="arsregnskap">Årsregnskap</option>
                            <option value="altinn">Altinn</option>
                            <option value="innstillinger">Innstillinger</option>
                        </flux:select>
                    </div>

                    {{-- Desktop sidebar --}}
                    <div class="hidden lg:block">
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 sticky top-4">
                            <div class="p-4">
                                {{-- Search --}}
                                <flux:input
                                    x-model="searchQuery"
                                    placeholder="Søk i dokumentasjonen..."
                                    icon="magnifying-glass"
                                    class="w-full mb-4"
                                />

                                {{-- Navigation Groups --}}
                                <flux:accordion exclusive class="space-y-1">
                                    {{-- Grunnleggende --}}
                                    <flux:accordion.item expanded>
                                        <flux:accordion.heading class="text-sm font-medium py-2">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded flex items-center justify-center">
                                                    <flux:icon.rocket-launch class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                                                </div>
                                                <span class="text-zinc-900 dark:text-white">Grunnleggende</span>
                                            </div>
                                        </flux:accordion.heading>
                                        <flux:accordion.content>
                                            <nav class="space-y-0.5 pl-8" x-show="!searchQuery || 'kom i gang'.includes(searchQuery.toLowerCase()) || 'dashboard'.includes(searchQuery.toLowerCase()) || 'mine aktiviteter'.includes(searchQuery.toLowerCase())">
                                                <a href="#kom-i-gang" @click.prevent="scrollToSection('kom-i-gang')"
                                                   x-show="!searchQuery || 'kom i gang'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'kom-i-gang' ? 'text-blue-600 dark:text-blue-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                                    Kom i gang
                                                </a>
                                                <a href="#dashboard" @click.prevent="scrollToSection('dashboard')"
                                                   x-show="!searchQuery || 'dashboard'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'dashboard' ? 'text-blue-600 dark:text-blue-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                                    Dashboard
                                                </a>
                                                <a href="#mine-aktiviteter" @click.prevent="scrollToSection('mine-aktiviteter')"
                                                   x-show="!searchQuery || 'mine aktiviteter'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'mine-aktiviteter' ? 'text-blue-600 dark:text-blue-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                                    Mine aktiviteter
                                                </a>
                                            </nav>
                                        </flux:accordion.content>
                                    </flux:accordion.item>

                                    {{-- CRM & Salg --}}
                                    <flux:accordion.item>
                                        <flux:accordion.heading class="text-sm font-medium py-2">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 bg-green-100 dark:bg-green-900/30 rounded flex items-center justify-center">
                                                    <flux:icon.shopping-cart class="w-3.5 h-3.5 text-green-600 dark:text-green-400" />
                                                </div>
                                                <span class="text-zinc-900 dark:text-white">CRM & Salg</span>
                                            </div>
                                        </flux:accordion.heading>
                                        <flux:accordion.content>
                                            <nav class="space-y-0.5 pl-8">
                                                <a href="#kontakter" @click.prevent="scrollToSection('kontakter')"
                                                   x-show="!searchQuery || 'kontakter kontaktregister'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'kontakter' ? 'text-green-600 dark:text-green-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-green-600 dark:hover:text-green-400 transition-colors">
                                                    Kontaktregister
                                                </a>
                                                <a href="#produkter" @click.prevent="scrollToSection('produkter')"
                                                   x-show="!searchQuery || 'produkter vareregister varer'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'produkter' ? 'text-green-600 dark:text-green-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-green-600 dark:hover:text-green-400 transition-colors">
                                                    Vareregister
                                                </a>
                                                <a href="#salg" @click.prevent="scrollToSection('salg')"
                                                   x-show="!searchQuery || 'salg tilbud ordre faktura'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'salg' ? 'text-green-600 dark:text-green-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-green-600 dark:hover:text-green-400 transition-colors">
                                                    Salg
                                                </a>
                                            </nav>
                                        </flux:accordion.content>
                                    </flux:accordion.item>

                                    {{-- Prosjekt --}}
                                    <flux:accordion.item>
                                        <flux:accordion.heading class="text-sm font-medium py-2">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 bg-orange-100 dark:bg-orange-900/30 rounded flex items-center justify-center">
                                                    <flux:icon.folder class="w-3.5 h-3.5 text-orange-600 dark:text-orange-400" />
                                                </div>
                                                <span class="text-zinc-900 dark:text-white">Prosjekt</span>
                                            </div>
                                        </flux:accordion.heading>
                                        <flux:accordion.content>
                                            <nav class="space-y-0.5 pl-8">
                                                <a href="#prosjekter" @click.prevent="scrollToSection('prosjekter')"
                                                   x-show="!searchQuery || 'prosjekter prosjekt'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'prosjekter' ? 'text-orange-600 dark:text-orange-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
                                                    Prosjekter
                                                </a>
                                                <a href="#arbeidsordrer" @click.prevent="scrollToSection('arbeidsordrer')"
                                                   x-show="!searchQuery || 'arbeidsordrer arbeid'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'arbeidsordrer' ? 'text-orange-600 dark:text-orange-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
                                                    Arbeidsordrer
                                                </a>
                                                <a href="#timeregistrering" @click.prevent="scrollToSection('timeregistrering')"
                                                   x-show="!searchQuery || 'timer timeregistrering timeseddel'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'timeregistrering' ? 'text-orange-600 dark:text-orange-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
                                                    Timeregistrering
                                                </a>
                                            </nav>
                                        </flux:accordion.content>
                                    </flux:accordion.item>

                                    {{-- Økonomi --}}
                                    <flux:accordion.item>
                                        <flux:accordion.heading class="text-sm font-medium py-2">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 bg-violet-100 dark:bg-violet-900/30 rounded flex items-center justify-center">
                                                    <flux:icon.calculator class="w-3.5 h-3.5 text-violet-600 dark:text-violet-400" />
                                                </div>
                                                <span class="text-zinc-900 dark:text-white">Økonomi</span>
                                            </div>
                                        </flux:accordion.heading>
                                        <flux:accordion.content>
                                            <nav class="space-y-0.5 pl-8">
                                                <a href="#okonomi" @click.prevent="scrollToSection('okonomi')"
                                                   x-show="!searchQuery || 'økonomi regnskap bilag'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'okonomi' ? 'text-violet-600 dark:text-violet-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                                                    Økonomi
                                                </a>
                                                <a href="#selskapsanalyse" @click.prevent="scrollToSection('selskapsanalyse')"
                                                   x-show="!searchQuery || 'analyse selskapsanalyse ai'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'selskapsanalyse' ? 'text-violet-600 dark:text-violet-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                                                    Selskapsanalyse
                                                </a>
                                                <a href="#innboks" @click.prevent="scrollToSection('innboks')"
                                                   x-show="!searchQuery || 'innboks ai tolkning bilag'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'innboks' ? 'text-violet-600 dark:text-violet-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                                                    Innboks (AI)
                                                </a>
                                                <a href="#rapporter" @click.prevent="scrollToSection('rapporter')"
                                                   x-show="!searchQuery || 'rapporter hovedbok resultat balanse'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'rapporter' ? 'text-violet-600 dark:text-violet-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                                                    Rapporter
                                                </a>
                                                <a href="#mva" @click.prevent="scrollToSection('mva')"
                                                   x-show="!searchQuery || 'mva merverdiavgift'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'mva' ? 'text-violet-600 dark:text-violet-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                                                    MVA-meldinger
                                                </a>
                                            </nav>
                                        </flux:accordion.content>
                                    </flux:accordion.item>

                                    {{-- Årsoppgjør --}}
                                    <flux:accordion.item>
                                        <flux:accordion.heading class="text-sm font-medium py-2">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 bg-indigo-100 dark:bg-indigo-900/30 rounded flex items-center justify-center">
                                                    <flux:icon.document-text class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" />
                                                </div>
                                                <span class="text-zinc-900 dark:text-white">Årsoppgjør</span>
                                            </div>
                                        </flux:accordion.heading>
                                        <flux:accordion.content>
                                            <nav class="space-y-0.5 pl-8">
                                                <a href="#aksjonaerregister" @click.prevent="scrollToSection('aksjonaerregister')"
                                                   x-show="!searchQuery || 'aksjonær aksje eier'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'aksjonaerregister' ? 'text-indigo-600 dark:text-indigo-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                                    Aksjonærregister
                                                </a>
                                                <a href="#skatt" @click.prevent="scrollToSection('skatt')"
                                                   x-show="!searchQuery || 'skatt skattemelding'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'skatt' ? 'text-indigo-600 dark:text-indigo-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                                    Skatt
                                                </a>
                                                <a href="#arsregnskap" @click.prevent="scrollToSection('arsregnskap')"
                                                   x-show="!searchQuery || 'årsregnskap xbrl noter'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'arsregnskap' ? 'text-indigo-600 dark:text-indigo-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                                    Årsregnskap
                                                </a>
                                                <a href="#altinn" @click.prevent="scrollToSection('altinn')"
                                                   x-show="!searchQuery || 'altinn innsending'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'altinn' ? 'text-indigo-600 dark:text-indigo-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                                    Altinn
                                                </a>
                                            </nav>
                                        </flux:accordion.content>
                                    </flux:accordion.item>

                                    {{-- Innstillinger --}}
                                    <flux:accordion.item>
                                        <flux:accordion.heading class="text-sm font-medium py-2">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 bg-zinc-100 dark:bg-zinc-700 rounded flex items-center justify-center">
                                                    <flux:icon.cog-6-tooth class="w-3.5 h-3.5 text-zinc-600 dark:text-zinc-400" />
                                                </div>
                                                <span class="text-zinc-900 dark:text-white">Innstillinger</span>
                                            </div>
                                        </flux:accordion.heading>
                                        <flux:accordion.content>
                                            <nav class="space-y-0.5 pl-8">
                                                <a href="#innstillinger" @click.prevent="scrollToSection('innstillinger')"
                                                   x-show="!searchQuery || 'innstillinger konto'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'innstillinger' ? 'text-zinc-900 dark:text-white font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-zinc-900 dark:hover:text-white transition-colors">
                                                    Innstillinger
                                                </a>
                                                <a href="#sikkerhet" @click.prevent="scrollToSection('sikkerhet')"
                                                   x-show="!searchQuery || 'sikkerhet 2fa tofaktor passord'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'sikkerhet' ? 'text-zinc-900 dark:text-white font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-zinc-900 dark:hover:text-white transition-colors">
                                                    Sikkerhet og 2FA
                                                </a>
                                                <a href="#avdelinger" @click.prevent="scrollToSection('avdelinger')"
                                                   x-show="!searchQuery || 'avdelinger'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'avdelinger' ? 'text-zinc-900 dark:text-white font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-zinc-900 dark:hover:text-white transition-colors">
                                                    Avdelinger
                                                </a>
                                                <a href="#kontoplan" @click.prevent="scrollToSection('kontoplan')"
                                                   x-show="!searchQuery || 'kontoplan konto ns4102'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'kontoplan' ? 'text-zinc-900 dark:text-white font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-zinc-900 dark:hover:text-white transition-colors">
                                                    Kontoplan
                                                </a>
                                                <a href="#selskap" @click.prevent="scrollToSection('selskap')"
                                                   x-show="!searchQuery || 'selskap brukere roller'.includes(searchQuery.toLowerCase())"
                                                   :class="activeSection === 'selskap' ? 'text-zinc-900 dark:text-white font-medium' : 'text-zinc-600 dark:text-zinc-400'"
                                                   class="block py-1.5 text-sm hover:text-zinc-900 dark:hover:text-white transition-colors">
                                                    Selskap og brukere
                                                </a>
                                            </nav>
                                        </flux:accordion.content>
                                    </flux:accordion.item>
                                </flux:accordion>
                            </div>
                        </flux:card>
                    </div>
                </div>

                {{-- Main Content --}}
                <div class="lg:col-span-3 space-y-6">
                    @include('help.partials.section-kom-i-gang')
                    @include('help.partials.section-dashboard')
                    @include('help.partials.section-mine-aktiviteter')
                    @include('help.partials.section-kontakter')
                    @include('help.partials.section-produkter')
                    @include('help.partials.section-prosjekter')
                    @include('help.partials.section-arbeidsordrer')
                    @include('help.partials.section-timeregistrering')
                    @include('help.partials.section-salg')
                    @include('help.partials.section-okonomi')
                    @include('help.partials.section-selskapsanalyse')
                    @include('help.partials.section-innboks')
                    @include('help.partials.section-rapporter')
                    @include('help.partials.section-mva')
                    @include('help.partials.section-aksjonaerregister')
                    @include('help.partials.section-skatt')
                    @include('help.partials.section-arsregnskap')
                    @include('help.partials.section-altinn')
                    @include('help.partials.section-innstillinger')
                    @include('help.partials.section-sikkerhet')
                    @include('help.partials.section-avdelinger')
                    @include('help.partials.section-kontoplan')
                    @include('help.partials.section-selskap')

                    {{-- Support Card --}}
                    <flux:card class="bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg border-0">
                        <div class="p-6 text-white">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                    <flux:icon.question-mark-circle class="w-5 h-5 text-white" />
                                </div>
                                <flux:heading size="lg" class="text-white">Trenger du hjelp?</flux:heading>
                            </div>
                            <p class="text-white/90 mb-4">
                                Finner du ikke svar på det du lurer på? Ta kontakt med oss for support.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <flux:button variant="filled" class="bg-white text-indigo-600 hover:bg-white/90">
                                    <flux:icon.envelope class="w-4 h-4 mr-2" />
                                    Send e-post
                                </flux:button>
                                <flux:button variant="ghost" class="text-white border-white/30 hover:bg-white/10">
                                    <flux:icon.phone class="w-4 h-4 mr-2" />
                                    Ring oss
                                </flux:button>
                            </div>
                        </div>
                    </flux:card>
                </div>
            </div>
        </flux:main>

        {{-- Back to Top Button --}}
        <button
            x-show="showBackToTop"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-8 right-8 p-3 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-colors z-50"
        >
            <flux:icon.chevron-up class="w-5 h-5" />
        </button>
    </div>
</x-layouts.app>
