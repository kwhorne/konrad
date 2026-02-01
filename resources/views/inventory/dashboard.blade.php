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

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="p-4">
                        <flux:text class="text-xs font-medium text-zinc-500">Lagervarer</flux:text>
                        <flux:heading size="xl" class="mt-1">-</flux:heading>
                        <flux:text class="text-xs text-zinc-400">Lagerforte produkter</flux:text>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="p-4">
                        <flux:text class="text-xs font-medium text-zinc-500">Total verdi</flux:text>
                        <flux:heading size="xl" class="mt-1">-</flux:heading>
                        <flux:text class="text-xs text-zinc-400">Vektet gjennomsnitt</flux:text>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="p-4">
                        <flux:text class="text-xs font-medium text-zinc-500">Apne bestillinger</flux:text>
                        <flux:heading size="xl" class="mt-1">-</flux:heading>
                        <flux:text class="text-xs text-zinc-400">Innkjopsordrer</flux:text>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="p-4">
                        <flux:text class="text-xs font-medium text-zinc-500">Under bestillingspunkt</flux:text>
                        <flux:heading size="xl" class="mt-1">-</flux:heading>
                        <flux:text class="text-xs text-zinc-400">Varer som ma bestilles</flux:text>
                    </div>
                </flux:card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="p-4">
                        <flux:heading size="base" class="mb-4">Siste bevegelser</flux:heading>
                        <flux:text class="text-zinc-500 text-sm">Ingen bevegelser enna</flux:text>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900">
                    <div class="p-4">
                        <flux:heading size="base" class="mb-4">Siste varemottak</flux:heading>
                        <flux:text class="text-zinc-500 text-sm">Ingen varemottak enna</flux:text>
                    </div>
                </flux:card>
            </div>
        </flux:main>
    </div>
</x-layouts.app>
