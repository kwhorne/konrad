<x-layouts.app title="Nytt varemottak">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="inventory" />
        <x-app-header current="inventory" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-6">
                <flux:button href="{{ route('purchasing.goods-receipts.index') }}" variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-4 h-4 mr-2" />
                    Tilbake til oversikt
                </flux:button>
            </div>

            <div class="mb-6">
                <flux:heading size="xl" level="1">Nytt varemottak</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Registrer mottatte varer</flux:text>
            </div>

            <livewire:goods-receipt-form />
        </flux:main>
    </div>
</x-layouts.app>
