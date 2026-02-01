<x-layouts.app title="Beholdning">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="inventory" />
        <x-app-header current="inventory" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <flux:heading size="xl" level="1">Beholdning</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Oversikt over lagerbeholdning per produkt og lokasjon</flux:text>
                </div>
            </div>

            <livewire:stock-level-manager />
        </flux:main>
    </div>
</x-layouts.app>
