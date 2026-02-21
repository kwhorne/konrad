<x-layouts.app title="Lageroversikt">
    <div class="min-h-screen bg-zinc-100 dark:bg-zinc-950">
        <x-app-sidebar current="inventory" />
        <x-app-header current="inventory" />

        <flux:main class="bg-zinc-100 dark:bg-zinc-950">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">Lageroversikt</h1>
                    <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Beholdning, bestillinger og lagerflyt</p>
                </div>
                <div class="flex gap-2">
                    <flux:button href="{{ route('inventory.adjustments') }}" variant="ghost" icon="adjustments-horizontal">
                        Justering
                    </flux:button>
                    <flux:button href="{{ route('purchasing.purchase-orders.create') }}" variant="primary" icon="plus">
                        Ny innkjøpsordre
                    </flux:button>
                </div>
            </div>

            <livewire:inventory-dashboard />
        </flux:main>
    </div>
</x-layouts.app>
