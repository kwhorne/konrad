<x-layouts.payroll title="Rapporter">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-payroll-sidebar current="reports" />
        <x-app-header current="reports" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.chart-bar class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Rapporter
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Lonnsrapporter og statistikk
                    </flux:text>
                </div>
            </div>

            <livewire:payroll.report-manager />
        </flux:main>
    </div>
</x-layouts.payroll>
