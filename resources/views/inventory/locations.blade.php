<x-layouts.app title="Lagerlokasjoner">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="inventory" />
        <x-app-header current="inventory" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <flux:heading size="xl" level="1">Lagerlokasjoner</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Administrer lagre, soner og hyller</flux:text>
                </div>
            </div>

            <livewire:stock-location-manager />
        </flux:main>
    </div>
</x-layouts.app>
