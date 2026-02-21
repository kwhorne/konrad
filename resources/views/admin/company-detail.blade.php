<x-layouts.app title="Admin - Selskapsdetaljer">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-admin-sidebar current="companies" />
        <x-admin-header current="companies" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-6 flex items-center gap-4">
                <a href="{{ route('admin.companies') }}" class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    <flux:icon.arrow-left class="w-5 h-5" />
                </a>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">Selskapsdetaljer</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">Administrer selskapsinformasjon, brukere, moduler og fakturering</flux:text>
                </div>
            </div>

            <flux:separator variant="subtle" class="mb-6" />

            <livewire:admin.company-detail-manager :company-id="$companyId" />
        </flux:main>
    </div>
</x-layouts.app>
