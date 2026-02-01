<x-layouts.app title="Varemottak">
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

            <livewire:goods-receipt-show :goods-receipt-id="$goodsReceipt" />
        </flux:main>
    </div>
</x-layouts.app>
