<!-- Footer -->
<footer class="bg-zinc-50 dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <svg class="h-8 w-8 shrink-0" viewBox="0 0 307 265" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M 0.0,139.5 L 0.5,120.0 C 1.0,100.5 2.0,61.5 13.7,41.0 C 25.3,20.5 47.7,18.5 69.2,19.0 C 90.7,19.5 111.3,22.5 132.0,26.0 L 152.7,29.5 L 152.8,66.5 C 152.8,86.8 152.8,127.3 152.8,147.7 L 152.8,188.0 L 132.2,188.0 C 111.5,188.0 70.0,188.0 49.0,188.0 C 28.0,188.0 27.5,188.0 18.3,174.0 C 9.2,160.0 0.8,132.0 0.4,118.0 C 0.0,104.0 0.0,104.0 0.0,139.5 Z" class="fill-[#457ba7] dark:fill-[#6b9bc4]" fill-rule="evenodd"/>
                        <path d="M 152.7,29.5 L 173.3,33.0 C 194.0,36.5 235.3,43.5 256.0,70.0 C 276.7,96.5 277.0,142.5 276.8,165.0 L 276.5,188.0 L 255.5,188.0 C 234.5,188.0 192.5,188.0 171.5,188.0 L 150.5,188.0 L 150.8,147.7 C 151.2,107.3 152.0,86.8 152.3,66.5 C 152.5,46.2 152.5,46.0 152.7,29.5 Z" class="fill-[#87c8b8] dark:fill-[#a8ddd0]" fill-rule="evenodd"/>
                        <path d="M 0.0,139.5 C 0.0,104.0 0.0,104.0 0.4,118.0 C 0.8,132.0 9.2,160.0 18.3,174.0 C 27.5,188.0 28.0,188.0 49.0,188.0 C 70.0,188.0 111.5,188.0 132.2,188.0 L 152.8,188.0 L 152.8,210.0 C 152.8,222.0 152.5,244.0 152.2,258.5 C 151.8,273.0 151.5,282.5 120.0,275.0 C 88.5,267.5 25.8,243.0 12.0,221.8 C -1.8,200.7 0.2,183.0 0.8,165.2 C 1.5,147.5 0.0,139.5 0.0,139.5 Z" class="fill-[#f2a35a] dark:fill-[#f5b97a]" fill-rule="evenodd"/>
                    </svg>
                    <span class="text-xl font-bold text-zinc-900 dark:text-white">Konrad Office</span>
                </div>
                <p class="text-zinc-600 dark:text-zinc-400 max-w-md">
                    Et komplett forretningssystem for norske bedrifter. Salg, fakturering, regnskap, prosjektstyring, kontrakter og eiendeler.
                </p>
                <div class="flex gap-4 mt-6">
                    <a href="#" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                        <flux:icon.link class="w-5 h-5" />
                    </a>
                    <a href="#" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                        <flux:icon.at-symbol class="w-5 h-5" />
                    </a>
                </div>
            </div>

            <!-- Product -->
            <div>
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white mb-4">Produkt</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="#modules" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                            Moduler
                        </a>
                    </li>
                    <li>
                        <a href="#features" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                            Funksjoner
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pricing') }}" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                            Priser
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('login') }}" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                            Logg inn
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Company -->
            <div>
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white mb-4">Selskap</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ route('contact') }}" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                            Kontakt oss
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom -->
        <div class="border-t border-zinc-200 dark:border-zinc-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                &copy; {{ date('Y') }} Konrad Office. Alle rettigheter reservert.
            </p>
            <div class="flex items-center gap-6 mt-4 md:mt-0">
                <button
                    type="button"
                    x-data
                    x-on:click="$flux.modal('privacy-modal').show()"
                    class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors"
                >
                    Personvern
                </button>
                <button
                    type="button"
                    x-data
                    x-on:click="$flux.modal('terms-modal').show()"
                    class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors"
                >
                    Vilkår
                </button>
            </div>
        </div>
    </div>
</footer>
