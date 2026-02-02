<x-layouts.economy title="Bankavstemming">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-economy-sidebar current="bank-reconciliation" />
        <x-app-header current="bank-reconciliation" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <flux:button href="{{ route('economy.dashboard') }}" variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-5 h-5" />
                </flux:button>
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.building-library class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Bankavstemming
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Importer kontoutskrift og match transaksjoner mot bilag
                    </flux:text>
                </div>
            </div>

            @livewire('bank-reconciliation-manager', ['statementId' => request()->query('statement')])
        </flux:main>
    </div>
</x-layouts.economy>
