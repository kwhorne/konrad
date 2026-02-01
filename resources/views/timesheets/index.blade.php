<x-layouts.app title="Timeregistrering">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="timesheets" />
        <x-app-header current="timesheets" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.clock class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Timeregistrering
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Registrer timer og send til godkjenning
                    </flux:text>
                </div>
            </div>

            <livewire:timesheet-manager />
        </flux:main>
    </div>
</x-layouts.app>
