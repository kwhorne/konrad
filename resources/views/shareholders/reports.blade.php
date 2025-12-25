<x-layouts.app title="Aksjonærrapporter">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="shareholders" />
        <x-app-header current="shareholders" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.document-text class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Aksjonærrapporter
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Aksjonærregisteroppgaven (RF-1086) for innsending til Altinn
                    </flux:text>
                </div>
            </div>

            {{-- Navigation tabs --}}
            <div class="mb-6">
                <flux:tabs>
                    <flux:tab href="{{ route('shareholders.index') }}" :current="request()->routeIs('shareholders.index')">
                        Aksjonærer
                    </flux:tab>
                    <flux:tab href="{{ route('shareholders.classes') }}" :current="request()->routeIs('shareholders.classes')">
                        Aksjeklasser
                    </flux:tab>
                    <flux:tab href="{{ route('shareholders.transactions') }}" :current="request()->routeIs('shareholders.transactions')">
                        Transaksjoner
                    </flux:tab>
                    <flux:tab href="{{ route('shareholders.dividends') }}" :current="request()->routeIs('shareholders.dividends')">
                        Utbytte
                    </flux:tab>
                    <flux:tab href="{{ route('shareholders.reports') }}" :current="request()->routeIs('shareholders.reports')">
                        Rapporter
                    </flux:tab>
                </flux:tabs>
            </div>

            <livewire:shareholder-report-manager />
        </flux:main>
    </div>
</x-layouts.app>
