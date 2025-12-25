<x-layouts.app title="Årsregnskap">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="annual-accounts" />
        <x-app-header current="annual-accounts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.document-chart-bar class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Årsregnskap
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Årsregnskap og innsending til Regnskapsregisteret
                    </flux:text>
                </div>
            </div>

            <livewire:annual-account-manager />
        </flux:main>
    </div>
</x-layouts.app>
