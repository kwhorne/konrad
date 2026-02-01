<div>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Varetelling</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Periodisk opptelling av lagerbeholdning</flux:text>
        </div>
        <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
            Ny varetelling
        </flux:button>
    </div>

    <!-- Filters -->
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700 mb-6">
        <div class="p-4">
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Sok..." icon="magnifying-glass" />
                </div>
                <flux:select wire:model.live="filterStatus" class="w-40">
                    <option value="">Alle statuser</option>
                    <option value="draft">Utkast</option>
                    <option value="in_progress">Pagar</option>
                    <option value="completed">Fullfort</option>
                    <option value="posted">Bokfort</option>
                    <option value="cancelled">Kansellert</option>
                </flux:select>
                <flux:select wire:model.live="filterLocation" class="w-48">
                    <option value="">Alle lokasjoner</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>
    </flux:card>

    <!-- Table -->
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Nummer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Lokasjon</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Dato</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Forventet</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Avvik</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Handlinger</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($stockCounts as $count)
                        <tr wire:key="count-{{ $count->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <td class="px-4 py-3">
                                <a href="{{ route('inventory.stock-counts.show', $count) }}" class="font-mono text-indigo-600 dark:text-indigo-400 hover:underline">
                                    {{ $count->count_number }}
                                </a>
                                @if($count->description)
                                    <flux:text class="text-sm text-zinc-500">{{ Str::limit($count->description, 30) }}</flux:text>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <flux:text class="text-zinc-900 dark:text-white">{{ $count->stockLocation?->name }}</flux:text>
                            </td>
                            <td class="px-4 py-3">
                                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $count->count_date->format('d.m.Y') }}</flux:text>
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge color="{{ $count->status_color }}">{{ $count->status_label }}</flux:badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <flux:text class="text-zinc-900 dark:text-white">{{ number_format($count->total_expected_value, 0, ',', ' ') }}</flux:text>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($count->status !== 'draft')
                                    <flux:text class="{{ $count->total_variance_value < 0 ? 'text-red-600' : ($count->total_variance_value > 0 ? 'text-green-600' : 'text-zinc-600') }}">
                                        {{ $count->total_variance_value >= 0 ? '+' : '' }}{{ number_format($count->total_variance_value, 0, ',', ' ') }}
                                    </flux:text>
                                @else
                                    <flux:text class="text-zinc-400">-</flux:text>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" />
                                    <flux:menu>
                                        <flux:menu.item href="{{ route('inventory.stock-counts.show', $count) }}" icon="eye">
                                            {{ $count->can_edit ? 'Apne' : 'Vis' }}
                                        </flux:menu.item>
                                        @if($count->can_start)
                                            <flux:menu.item wire:click="startCount({{ $count->id }})" icon="play">
                                                Start telling
                                            </flux:menu.item>
                                        @endif
                                        @if($count->can_cancel)
                                            <flux:menu.separator />
                                            <flux:menu.item wire:click="cancelCount({{ $count->id }})" icon="x-mark" variant="danger">
                                                Kanseller
                                            </flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center">
                                <flux:text class="text-zinc-500">Ingen varetellinger funnet</flux:text>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stockCounts->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                {{ $stockCounts->links() }}
            </div>
        @endif
    </flux:card>

    <!-- Create Modal -->
    <flux:modal wire:model="showCreateModal" class="max-w-md">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4">Ny varetelling</flux:heading>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Lokasjon *</flux:label>
                    <flux:select wire:model="create_location_id">
                        <option value="">Velg lokasjon</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="create_location_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Beskrivelse</flux:label>
                    <flux:input wire:model="create_description" placeholder="F.eks. Arstelling 2026" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <flux:button wire:click="closeCreateModal" variant="ghost">
                    Avbryt
                </flux:button>
                <flux:button wire:click="createCount" variant="primary">
                    Opprett telling
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
