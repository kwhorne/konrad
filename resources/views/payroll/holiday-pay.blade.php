<x-layouts.payroll title="Feriepenger">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-payroll-sidebar current="holiday-pay" />
        <x-app-header current="holiday-pay" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-yellow-500 to-amber-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.sun class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Feriepenger
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Feriepengeoversikt og utbetaling
                    </flux:text>
                </div>
            </div>

            <livewire:payroll.holiday-pay-manager />
        </flux:main>
    </div>
</x-layouts.payroll>
