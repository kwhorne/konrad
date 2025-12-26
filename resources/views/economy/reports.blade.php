<x-layouts.economy title="Rapporter">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-economy-sidebar current="reports" />
        <x-app-header current="reports" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <flux:button href="{{ route('economy.dashboard') }}" variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-5 h-5" />
                </flux:button>
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.chart-bar class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Rapporter
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Regnskapsrapporter og oversikter
                    </flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Hovedbok -->
                <a href="{{ route('reports.general-ledger') }}" class="block">
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors h-full">
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                                    <flux:icon.book-open class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div>
                                    <flux:heading size="lg" class="text-zinc-900 dark:text-white">Hovedbok</flux:heading>
                                </div>
                            </div>
                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                Detaljert oversikt over alle transaksjoner per konto med inngående og utgående saldo.
                            </flux:text>
                        </div>
                    </flux:card>
                </a>

                <!-- Bilagsjournal -->
                <a href="{{ route('reports.voucher-journal') }}" class="block">
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors h-full">
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                                    <flux:icon.document-text class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <flux:heading size="lg" class="text-zinc-900 dark:text-white">Bilagsjournal</flux:heading>
                                </div>
                            </div>
                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                Kronologisk liste over alle bokførte bilag med debet- og kreditposteringer.
                            </flux:text>
                        </div>
                    </flux:card>
                </a>

                <!-- Saldobalanse -->
                <a href="{{ route('reports.trial-balance') }}" class="block">
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 hover:border-amber-300 dark:hover:border-amber-700 transition-colors h-full">
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                                    <flux:icon.scale class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                                </div>
                                <div>
                                    <flux:heading size="lg" class="text-zinc-900 dark:text-white">Saldobalanse</flux:heading>
                                </div>
                            </div>
                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                Oversikt over alle kontoers debet- og kreditsaldo. Verifiserer at regnskapet balanserer.
                            </flux:text>
                        </div>
                    </flux:card>
                </a>

                <!-- Resultatregnskap -->
                <a href="{{ route('reports.income-statement') }}" class="block">
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 hover:border-green-300 dark:hover:border-green-700 transition-colors h-full">
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                                    <flux:icon.arrow-trending-up class="w-6 h-6 text-green-600 dark:text-green-400" />
                                </div>
                                <div>
                                    <flux:heading size="lg" class="text-zinc-900 dark:text-white">Resultatregnskap</flux:heading>
                                </div>
                            </div>
                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                Viser inntekter og kostnader for en periode med beregning av driftsresultat og årsresultat.
                            </flux:text>
                        </div>
                    </flux:card>
                </a>

                <!-- Balanse -->
                <a href="{{ route('reports.balance-sheet') }}" class="block">
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 hover:border-blue-300 dark:hover:border-blue-700 transition-colors h-full">
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                                    <flux:icon.building-library class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <flux:heading size="lg" class="text-zinc-900 dark:text-white">Balanse</flux:heading>
                                </div>
                            </div>
                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                Oversikt over eiendeler, gjeld og egenkapital på et gitt tidspunkt.
                            </flux:text>
                        </div>
                    </flux:card>
                </a>
            </div>
        </flux:main>
    </div>
</x-layouts.economy>
