<x-layouts.economy title="Regnskap">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-economy-sidebar current="accounting" />
        <x-app-header current="accounting" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.calculator class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Regnskap
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Oversikt over regnskap
                    </flux:text>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Customer Receivables -->
                <a href="{{ route('economy.customer-ledger') }}" class="block">
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                        <flux:icon.users class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg" class="text-zinc-900 dark:text-white">Kundereskontro</flux:heading>
                                        <flux:text class="text-zinc-500 dark:text-zinc-400 text-sm">Utestående kundefordringer</flux:text>
                                    </div>
                                </div>
                                <flux:icon.arrow-right class="w-5 h-5 text-zinc-400" />
                            </div>
                            <div class="text-3xl font-bold text-zinc-900 dark:text-white mb-4">
                                {{ number_format($customerBalance, 2, ',', ' ') }} <span class="text-lg font-normal text-zinc-500">NOK</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-sm">
                                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-2 text-center">
                                    <div class="text-green-600 dark:text-green-400 font-medium">{{ number_format($customerAging['1-30']['total'], 0, ',', ' ') }}</div>
                                    <div class="text-xs text-zinc-500">1-30 dager</div>
                                </div>
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-2 text-center">
                                    <div class="text-yellow-600 dark:text-yellow-400 font-medium">{{ number_format($customerAging['31-60']['total'], 0, ',', ' ') }}</div>
                                    <div class="text-xs text-zinc-500">31-60 dager</div>
                                </div>
                                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-2 text-center">
                                    <div class="text-red-600 dark:text-red-400 font-medium">{{ number_format($customerAging['90+']['total'], 0, ',', ' ') }}</div>
                                    <div class="text-xs text-zinc-500">90+ dager</div>
                                </div>
                            </div>
                        </div>
                    </flux:card>
                </a>

                <!-- Supplier Payables -->
                <a href="{{ route('economy.supplier-ledger') }}" class="block">
                    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 hover:border-orange-300 dark:hover:border-orange-700 transition-colors">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                        <flux:icon.building-office class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg" class="text-zinc-900 dark:text-white">Leverandørreskontro</flux:heading>
                                        <flux:text class="text-zinc-500 dark:text-zinc-400 text-sm">Utestående leverandørgjeld</flux:text>
                                    </div>
                                </div>
                                <flux:icon.arrow-right class="w-5 h-5 text-zinc-400" />
                            </div>
                            <div class="text-3xl font-bold text-zinc-900 dark:text-white mb-4">
                                {{ number_format($supplierBalance, 2, ',', ' ') }} <span class="text-lg font-normal text-zinc-500">NOK</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-sm">
                                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-2 text-center">
                                    <div class="text-green-600 dark:text-green-400 font-medium">{{ number_format($supplierAging['1-30']['total'], 0, ',', ' ') }}</div>
                                    <div class="text-xs text-zinc-500">1-30 dager</div>
                                </div>
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-2 text-center">
                                    <div class="text-yellow-600 dark:text-yellow-400 font-medium">{{ number_format($supplierAging['31-60']['total'], 0, ',', ' ') }}</div>
                                    <div class="text-xs text-zinc-500">31-60 dager</div>
                                </div>
                                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-2 text-center">
                                    <div class="text-red-600 dark:text-red-400 font-medium">{{ number_format($supplierAging['90+']['total'], 0, ',', ' ') }}</div>
                                    <div class="text-xs text-zinc-500">90+ dager</div>
                                </div>
                            </div>
                        </div>
                    </flux:card>
                </a>
            </div>

            <!-- Quick Links -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-6">
                    <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-4">Hurtiglenker</flux:heading>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <a href="{{ route('economy.vouchers') }}" class="flex items-center gap-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <flux:icon.document-plus class="w-6 h-6 text-emerald-500" />
                            <div>
                                <div class="font-medium text-zinc-900 dark:text-white">Bilag</div>
                                <div class="text-xs text-zinc-500">Bilagsregistrering</div>
                            </div>
                        </a>
                        <a href="{{ route('economy.accounts') }}" class="flex items-center gap-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <flux:icon.table-cells class="w-6 h-6 text-violet-500" />
                            <div>
                                <div class="font-medium text-zinc-900 dark:text-white">Kontoplan</div>
                                <div class="text-xs text-zinc-500">NS 4102</div>
                            </div>
                        </a>
                        <a href="{{ route('economy.customer-ledger') }}" class="flex items-center gap-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <flux:icon.users class="w-6 h-6 text-blue-500" />
                            <div>
                                <div class="font-medium text-zinc-900 dark:text-white">Kundereskontro</div>
                                <div class="text-xs text-zinc-500">Kundefordringer</div>
                            </div>
                        </a>
                        <a href="{{ route('economy.supplier-ledger') }}" class="flex items-center gap-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <flux:icon.building-office class="w-6 h-6 text-orange-500" />
                            <div>
                                <div class="font-medium text-zinc-900 dark:text-white">Leverandørreskontro</div>
                                <div class="text-xs text-zinc-500">Leverandørgjeld</div>
                            </div>
                        </a>
                        <a href="{{ route('economy.reports') }}" class="flex items-center gap-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <flux:icon.chart-bar class="w-6 h-6 text-green-500" />
                            <div>
                                <div class="font-medium text-zinc-900 dark:text-white">Rapporter</div>
                                <div class="text-xs text-zinc-500">Regnskapsrapporter</div>
                            </div>
                        </a>
                    </div>
                </div>
            </flux:card>
        </flux:main>
    </div>
</x-layouts.economy>
