<x-layouts.app title="Godkjenn timer">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="timesheets-approval" />
        <x-app-header current="timesheets-approval" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.check-badge class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Godkjenn timer
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Gjennomga og godkjenn timesedler fra ansatte
                    </flux:text>
                </div>
            </div>

            <livewire:timesheet-approval-manager />
        </flux:main>
    </div>
</x-layouts.app>
