<x-layouts.economy title="MVA-meldinger">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-economy-sidebar current="vat-reports" />
        <x-app-header current="vat-reports" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <flux:button href="{{ route('economy.dashboard') }}" variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-5 h-5" />
                </flux:button>
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.document-chart-bar class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        MVA-meldinger
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Administrer og send MVA-meldinger til Altinn
                    </flux:text>
                </div>
            </div>

            <livewire:vat-report-manager />
        </flux:main>
    </div>
</x-layouts.economy>
