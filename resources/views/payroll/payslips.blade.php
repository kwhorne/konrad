<x-layouts.payroll title="Lønnsslipper">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-payroll-sidebar current="payslips" />
        <x-app-header current="payslips" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.document-text class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Lønnsslipper
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Se og last ned lønnsslipper
                    </flux:text>
                </div>
            </div>

            <livewire:payroll.payslip-manager />
        </flux:main>
    </div>
</x-layouts.payroll>
