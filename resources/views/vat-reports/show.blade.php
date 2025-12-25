<x-layouts.app :title="'MVA-melding ' . $vatReport->period_name">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="vat-reports" />
        <x-app-header current="vat-reports" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            {{-- Header --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
                <div class="flex items-center gap-4">
                    <a href="{{ route('vat-reports.index') }}" class="text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300">
                        <flux:icon.arrow-left class="w-5 h-5" />
                    </a>
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <flux:icon.document-chart-bar class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                                {{ $vatReport->period_name }}
                            </flux:heading>
                            <flux:badge color="{{ $vatReport->status_color }}">{{ $vatReport->status_name }}</flux:badge>
                        </div>
                        <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                            {{ $vatReport->report_type_name }} - {{ $vatReport->period_type_name }}
                        </flux:text>
                    </div>
                </div>
            </div>

            <livewire:vat-report-manager :initial-report-id="$vatReport->id" />
        </flux:main>
    </div>
</x-layouts.app>
