<x-layouts.economy title="Selskapsanalyse">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-economy-sidebar current="economy-analysis" />
        <x-app-header current="economy-analysis" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-violet-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.sparkles class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Selskapsanalyse
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Analyse av selskapets økonomiske helse
                    </flux:text>
                </div>
            </div>

            <livewire:company-analysis-manager />
        </flux:main>
    </div>
</x-layouts.economy>
