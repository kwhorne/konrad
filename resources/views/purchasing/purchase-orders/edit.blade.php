<x-layouts.app title="Rediger innkjopsordre">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="inventory" />
        <x-app-header current="inventory" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-6">
                <flux:button href="{{ route('purchasing.purchase-orders.show', $purchaseOrder) }}" variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-4 h-4 mr-2" />
                    Tilbake
                </flux:button>
            </div>

            <div class="mb-6">
                <flux:heading size="xl" level="1">Rediger innkjopsordre</flux:heading>
            </div>

            <livewire:purchase-order-form :purchase-order-id="$purchaseOrder" />
        </flux:main>
    </div>
</x-layouts.app>
