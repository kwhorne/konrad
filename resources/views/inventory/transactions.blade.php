<x-layouts.app title="Lagertransaksjoner">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="inventory" />
        <x-app-header current="inventory" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <flux:heading size="xl" level="1">Lagertransaksjoner</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Historikk over alle lagerbevegelser</flux:text>
                </div>
            </div>

            <livewire:stock-transaction-manager />
        </flux:main>
    </div>
</x-layouts.app>
