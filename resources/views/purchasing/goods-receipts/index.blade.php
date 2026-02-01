<x-layouts.app title="Varemottak">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="inventory" />
        <x-app-header current="inventory" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <flux:heading size="xl" level="1">Varemottak</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Registrer mottatte varer fra leverandorer</flux:text>
                </div>
                <flux:button href="{{ route('purchasing.goods-receipts.create') }}" variant="primary">
                    <flux:icon.plus class="w-4 h-4 mr-2" />
                    Nytt varemottak
                </flux:button>
            </div>

            <livewire:goods-receipt-manager />
        </flux:main>
    </div>
</x-layouts.app>
