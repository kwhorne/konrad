<x-layouts.app title="Momssatser">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="products" />
        <x-app-header current="products" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center shadow-lg">
                        <flux:icon.receipt-percent class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            Momssatser
                        </flux:heading>
                        <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                            Administrer momssatser for varetyper
                        </flux:text>
                    </div>
                </div>
                <flux:button href="{{ route('vat-rates.create') }}" variant="primary" class="px-6 py-3 shadow-lg shadow-emerald-500/30">
                    <flux:icon.plus class="w-5 h-5 mr-2" />
                    Ny momssats
                </flux:button>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <flux:text class="text-green-800 dark:text-green-200">{{ session('success') }}</flux:text>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <flux:text class="text-red-800 dark:text-red-200">{{ session('error') }}</flux:text>
                </div>
            @endif

            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-6">
                    @if($vatRates->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Navn
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Kode
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Sats
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Standard
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Handlinger
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($vatRates as $rate)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                    {{ $rate->name }}
                                                </flux:text>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:badge variant="outline">{{ $rate->code }}</flux:badge>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                    {{ number_format($rate->rate, 0) }}%
                                                </flux:text>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($rate->is_default)
                                                    <flux:badge variant="success">Standard</flux:badge>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:badge variant="{{ $rate->is_active ? 'success' : 'outline' }}">
                                                    {{ $rate->is_active ? 'Aktiv' : 'Inaktiv' }}
                                                </flux:badge>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end gap-2">
                                                    <flux:button href="{{ route('vat-rates.edit', $rate) }}" variant="ghost" size="sm">
                                                        <flux:icon.pencil class="w-4 h-4" />
                                                    </flux:button>
                                                    <form action="{{ route('vat-rates.destroy', $rate) }}" method="POST" class="inline" onsubmit="return confirm('Er du sikker på at du vil slette denne momssatsen?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <flux:button type="submit" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
                                                            <flux:icon.trash class="w-4 h-4" />
                                                        </flux:button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $vatRates->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <flux:icon.receipt-percent class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                            <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                                Ingen momssatser funnet
                            </flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                                Kom i gang ved å opprette din første momssats
                            </flux:text>
                            <flux:button href="{{ route('vat-rates.create') }}" variant="primary">
                                <flux:icon.plus class="w-5 h-5 mr-2" />
                                Opprett momssats
                            </flux:button>
                        </div>
                    @endif
                </div>
            </flux:card>
        </flux:main>
    </div>
</x-layouts.app>
