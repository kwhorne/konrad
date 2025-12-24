<x-layouts.app title="Ordrer">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="orders" />
        <x-app-header current="orders" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-8 flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-green-500 to-emerald-500 shadow-lg">
                    <flux:icon.shopping-cart class="h-7 w-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Ordrer
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Administrer ordrer og konverter til fakturaer
                    </flux:text>
                </div>
            </div>

            <livewire:order-manager />
        </flux:main>
    </div>
</x-layouts.app>
