<x-layouts.app title="Kontantstrøm - {{ $annualAccount->fiscal_year }}">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="annual-accounts" />
        <x-app-header current="annual-accounts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('annual-accounts.index') }}" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                    <flux:icon.arrow-left class="w-6 h-6" />
                </a>
                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.banknotes class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Kontantstrømoppstilling {{ $annualAccount->fiscal_year }}
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        {{ $annualAccount->getReportPeriod() }}
                    </flux:text>
                </div>
            </div>

            <livewire:cash-flow-statement-manager :annual-account-id="$annualAccount->id" />
        </flux:main>
    </div>
</x-layouts.app>
