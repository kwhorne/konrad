<x-layouts.app title="Moduler">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-admin-sidebar current="modules" />
        <x-admin-header current="modules" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-8">
                <flux:heading size="2xl" level="1" class="text-zinc-900 dark:text-white mb-2">
                    Moduler
                </flux:heading>
                <flux:text class="text-lg text-zinc-600 dark:text-zinc-400">
                    Administrer tilgjengelige moduler og premium-pakker
                </flux:text>
            </div>

            <livewire:module-admin-manager />
        </flux:main>

    </div>
</x-layouts.app>
