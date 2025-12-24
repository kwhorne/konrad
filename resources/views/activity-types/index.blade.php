<x-layouts.app title="Aktivitetstyper">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="contacts" />
        <x-app-header current="contacts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg">
                        <flux:icon.tag class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            Aktivitetstyper
                        </flux:heading>
                        <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                            Administrer aktivitetstyper for oppfølging av kontakter
                        </flux:text>
                    </div>
                </div>
                <flux:button href="{{ route('activity-types.create') }}" variant="primary" class="px-6 py-3 shadow-lg shadow-purple-500/30">
                    <flux:icon.plus class="w-5 h-5 mr-2" />
                    Ny aktivitetstype
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
                    @if($activityTypes->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Navn
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Ikon
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Farge
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Rekkefølge
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Handlinger
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($activityTypes as $type)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-{{ $type->color }}-100 dark:bg-{{ $type->color }}-900/30 rounded-lg flex items-center justify-center">
                                                        <x-dynamic-component :component="'flux::icon.' . $type->icon" class="w-4 h-4 text-{{ $type->color }}-600 dark:text-{{ $type->color }}-400" />
                                                    </div>
                                                    <flux:text class="font-medium text-zinc-900 dark:text-white">
                                                        {{ $type->name }}
                                                    </flux:text>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                                    {{ $type->icon }}
                                                </flux:text>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:badge variant="{{ $type->color }}">
                                                    {{ $type->color }}
                                                </flux:badge>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:badge variant="{{ $type->is_active ? 'success' : 'outline' }}">
                                                    {{ $type->is_active ? 'Aktiv' : 'Inaktiv' }}
                                                </flux:badge>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                                    {{ $type->sort_order }}
                                                </flux:text>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end gap-2">
                                                    <flux:button href="{{ route('activity-types.edit', $type) }}" variant="ghost" size="sm">
                                                        <flux:icon.pencil class="w-4 h-4" />
                                                    </flux:button>
                                                    <form action="{{ route('activity-types.destroy', $type) }}" method="POST" class="inline" onsubmit="return confirm('Er du sikker på at du vil slette denne aktivitetstypen?')">
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
                            {{ $activityTypes->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <flux:icon.tag class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                            <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                                Ingen aktivitetstyper funnet
                            </flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                                Kom i gang ved å opprette din første aktivitetstype
                            </flux:text>
                            <flux:button href="{{ route('activity-types.create') }}" variant="primary">
                                <flux:icon.plus class="w-5 h-5 mr-2" />
                                Opprett aktivitetstype
                            </flux:button>
                        </div>
                    @endif
                </div>
            </flux:card>
        </flux:main>
    </div>
</x-layouts.app>
