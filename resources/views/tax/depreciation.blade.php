<x-layouts.app title="Saldoavskrivninger">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="tax" />
        <x-app-header current="tax" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.document-text class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Skatt
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Saldoavskrivninger per gruppe
                    </flux:text>
                </div>
            </div>

            {{-- Navigation tabs --}}
            <div class="mb-6">
                <flux:tabs>
                    <flux:tab href="{{ route('tax.returns') }}" :current="request()->routeIs('tax.returns')">
                        Skattemeldinger
                    </flux:tab>
                    <flux:tab href="{{ route('tax.adjustments') }}" :current="request()->routeIs('tax.adjustments')">
                        Forskjeller
                    </flux:tab>
                    <flux:tab href="{{ route('tax.deferred') }}" :current="request()->routeIs('tax.deferred')">
                        Utsatt skatt
                    </flux:tab>
                    <flux:tab href="{{ route('tax.depreciation') }}" :current="request()->routeIs('tax.depreciation')">
                        Saldoavskrivninger
                    </flux:tab>
                </flux:tabs>
            </div>

            <livewire:tax-depreciation-manager />
        </flux:main>
    </div>
</x-layouts.app>
