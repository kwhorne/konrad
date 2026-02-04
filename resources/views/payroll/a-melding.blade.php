<x-layouts.payroll title="A-melding">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-payroll-sidebar current="a-melding" />
        <x-app-header current="a-melding" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.cloud-arrow-up class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        A-melding
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Generer og send A-melding til Skatteetaten
                    </flux:text>
                </div>
            </div>

            <livewire:payroll.a-melding-manager />
        </flux:main>
    </div>
</x-layouts.payroll>
