<x-layouts.app title="Mine timer">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="timesheets-history" />
        <x-app-header current="timesheets-history" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.document-check class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Mine timer
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Oversikt over alle dine timesedler
                    </flux:text>
                </div>
            </div>

            <livewire:timesheet-history />
        </flux:main>
    </div>
</x-layouts.app>
