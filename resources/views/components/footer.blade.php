<!-- Footer -->
<footer class="bg-zinc-50 dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center space-x-2 mb-4">
                    <flux:icon.building-2 class="w-8 h-8 text-indigo-600 dark:text-indigo-400" />
                    <span class="text-xl font-bold text-zinc-900 dark:text-white">Konrad</span>
                </div>
                <p class="text-zinc-600 dark:text-zinc-400 max-w-md">
                    Et komplett office-system for mindre og mellomstore bedrifter. CRM, regnskap, timer, prosjektstyring og mer.
                </p>
                <div class="flex space-x-4 mt-6">
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
                        <a href="#features" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                            Tjenester
                        </a>
                    </li>
                    <li>
                        <a href="#components" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                            Teknologi
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('register') }}" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                            Kom i gang
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Company -->
            <div>
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white mb-4">Selskap</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="#" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                            Om oss
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                            Blogg
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                            Kontakt
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Bottom -->
        <div class="border-t border-zinc-200 dark:border-zinc-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                © {{ date('Y') }} Konrad. Alle rettigheter reservert.
            </p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="#" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white text-sm transition-colors">
                    Personvern
                </a>
                <a href="#" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white text-sm transition-colors">
                    Vilkår
                </a>
            </div>
        </div>
    </div>
</footer>
