<x-layouts.app title="Lageroversikt">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="inventory" />
        <x-app-header current="inventory" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <flux:heading size="xl" level="1">Lageroversikt</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Oversikt over lagerbeholdning og bevegelser</flux:text>
                </div>
                <div class="flex gap-2">
                    <flux:button href="{{ route('inventory.adjustments') }}" variant="ghost">
                        <flux:icon.adjustments-horizontal class="w-4 h-4 mr-2" />
                        Justering
                    </flux:button>
                    <flux:button href="{{ route('purchasing.purchase-orders.create') }}" variant="primary">
                        <flux:icon.plus class="w-4 h-4 mr-2" />
                        Ny innkjopsordre
                    </flux:button>
                </div>
            </div>

            <livewire:inventory-dashboard />
        </flux:main>
    </div>
</x-layouts.app>
