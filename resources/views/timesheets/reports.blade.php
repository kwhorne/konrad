<x-layouts.app title="Timerapporter">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="timesheets-reports" />
        <x-app-header current="timesheets-reports" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.chart-bar class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Timerapporter
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Oversikt over timer fordelt på prosjekter, ansatte og perioder
                    </flux:text>
                </div>
            </div>

            <livewire:timesheet-report-manager />
        </flux:main>
    </div>
</x-layouts.app>
