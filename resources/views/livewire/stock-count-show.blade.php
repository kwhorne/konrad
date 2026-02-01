<div>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <flux:button href="{{ route('inventory.stock-counts.index') }}" variant="ghost" size="sm" icon="arrow-left" />
                <div>
                    <flux:heading size="xl" level="1" class="font-mono">{{ $stockCount->count_number }}</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">{{ $stockCount->description }}</flux:text>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <flux:badge color="{{ $stockCount->status_color }}" size="lg">{{ $stockCount->status_label }}</flux:badge>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Progress & Summary -->
            @if($stockCount->status !== 'draft')
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <flux:card class="bg-white dark:bg-zinc-900">
                        <div class="p-4 text-center">
                            <flux:text class="text-xs font-medium text-zinc-500">Fremgang</flux:text>
                            <flux:heading size="xl" class="mt-1">{{ $summary['counted_lines'] }}/{{ $summary['total_lines'] }}</flux:heading>
                            <flux:text class="text-xs text-zinc-400">{{ $stockCount->progress['percentage'] }}%</flux:text>
                        </div>
                    </flux:card>
                    <flux:card class="bg-white dark:bg-zinc-900">
                        <div class="p-4 text-center">
                            <flux:text class="text-xs font-medium text-zinc-500">Forventet verdi</flux:text>
                            <flux:heading size="xl" class="mt-1">{{ number_format($summary['total_expected_value'], 0, ',', ' ') }}</flux:heading>
                        </div>
                    </flux:card>
                    <flux:card class="bg-white dark:bg-zinc-900">
                        <div class="p-4 text-center">
                            <flux:text class="text-xs font-medium text-zinc-500">Talt verdi</flux:text>
                            <flux:heading size="xl" class="mt-1">{{ number_format($summary['total_counted_value'], 0, ',', ' ') }}</flux:heading>
                        </div>
                    </flux:card>
                    <flux:card class="bg-white dark:bg-zinc-900">
                        <div class="p-4 text-center">
                            <flux:text class="text-xs font-medium text-zinc-500">Avvik</flux:text>
                            <flux:heading size="xl" class="mt-1 {{ $summary['total_variance_value'] < 0 ? 'text-red-600' : ($summary['total_variance_value'] > 0 ? 'text-green-600' : '') }}">
                                {{ $summary['total_variance_value'] >= 0 ? '+' : '' }}{{ number_format($summary['total_variance_value'], 0, ',', ' ') }}
                            </flux:heading>
                            @if($summary['variance_percentage'] != 0)
                                <flux:text class="text-xs text-zinc-400">({{ $summary['variance_percentage'] }}%)</flux:text>
                            @endif
                        </div>
                    </flux:card>
                </div>
            @endif

            <!-- Filters -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-4">
                    <div class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <flux:input wire:model.live.debounce.300ms="search" placeholder="Søk produkt..." icon="magnifying-glass" />
                        </div>
                        <flux:select wire:model.live="filterStatus" class="w-40">
                            <option value="">Alle</option>
                            <option value="not_counted">Ikke talt</option>
                            <option value="counted">Talt</option>
                            <option value="variance">Med avvik</option>
                        </flux:select>
                    </div>
                </div>
            </flux:card>

            <!-- Lines Table -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Produkt</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Forventet</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Talt</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Avvik</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Status</th>
                                @if($stockCount->can_edit)
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Handling</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($lines as $line)
                                <tr wire:key="line-{{ $line->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $line->has_variance ? ($line->variance_quantity < 0 ? 'bg-red-50 dark:bg-red-900/10' : 'bg-green-50 dark:bg-green-900/10') : '' }}">
                                    <td class="px-4 py-3">
                                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $line->product?->name }}</flux:text>
                                        @if($line->product?->sku)
                                            <flux:text class="text-sm text-zinc-500">{{ $line->product->sku }}</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ number_format($line->expected_quantity, 2, ',', ' ') }}</flux:text>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if($line->is_counted)
                                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ number_format($line->counted_quantity, 2, ',', ' ') }}</flux:text>
                                        @else
                                            <flux:text class="text-zinc-400">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if($line->is_counted)
                                            <flux:text class="{{ $line->variance_quantity < 0 ? 'text-red-600 font-medium' : ($line->variance_quantity > 0 ? 'text-green-600 font-medium' : 'text-zinc-600') }}">
                                                {{ $line->variance_quantity >= 0 ? '+' : '' }}{{ number_format($line->variance_quantity, 2, ',', ' ') }}
                                            </flux:text>
                                            @if($line->variance_reason)
                                                <flux:text class="text-xs text-zinc-500 block">{{ Str::limit($line->variance_reason, 20) }}</flux:text>
                                            @endif
                                        @else
                                            <flux:text class="text-zinc-400">-</flux:text>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($line->is_counted)
                                            <flux:icon.check-circle class="w-5 h-5 text-green-500 mx-auto" />
                                        @else
                                            <flux:icon.clock class="w-5 h-5 text-zinc-400 mx-auto" />
                                        @endif
                                    </td>
                                    @if($stockCount->can_edit)
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-1">
                                                @if(!$line->is_counted)
                                                    <flux:button wire:click="quickCount({{ $line->id }})" variant="ghost" size="sm" title="Tell som forventet">
                                                        <flux:icon.check class="w-4 h-4" />
                                                    </flux:button>
                                                @endif
                                                <flux:button wire:click="openCountModal({{ $line->id }})" variant="ghost" size="sm" title="Registrer telling">
                                                    <flux:icon.pencil class="w-4 h-4" />
                                                </flux:button>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $stockCount->can_edit ? 6 : 5 }}" class="px-4 py-8 text-center">
                                        <flux:text class="text-zinc-500">Ingen produkter funnet</flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </flux:card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Info -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-4">
                    <flux:heading size="base" class="mb-4">Detaljer</flux:heading>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-zinc-500">Lokasjon</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $stockCount->stockLocation?->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">Telledato</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $stockCount->count_date->format('d.m.Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">Opprettet av</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $stockCount->creator?->name ?? '-' }}</dd>
                        </div>
                        @if($stockCount->completed_at)
                            <div>
                                <dt class="text-zinc-500">Fullfort</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ $stockCount->completed_at->format('d.m.Y H:i') }}</dd>
                            </div>
                        @endif
                        @if($stockCount->posted_at)
                            <div>
                                <dt class="text-zinc-500">Bokført</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ $stockCount->posted_at->format('d.m.Y H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </flux:card>

            <!-- Actions -->
            <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-4 space-y-3">
                    @if($stockCount->can_start)
                        <flux:button wire:click="startCount" variant="primary" class="w-full" icon="play">
                            Start telling
                        </flux:button>
                    @endif

                    @if($stockCount->can_complete)
                        <flux:button wire:click="complete" variant="primary" class="w-full" icon="check">
                            Fullfør telling
                        </flux:button>
                    @endif

                    @if($stockCount->can_post)
                        <flux:button wire:click="post" variant="primary" class="w-full" icon="document-check">
                            Bokfor justeringer
                        </flux:button>
                        <flux:text class="text-xs text-zinc-500 text-center">
                            {{ $summary['lines_with_variance'] }} linje(r) med avvik vil bli justert
                        </flux:text>
                    @endif

                    @if($stockCount->can_cancel)
                        <flux:button wire:click="cancel" variant="ghost" class="w-full text-red-600" icon="x-mark">
                            Kanseller
                        </flux:button>
                    @endif

                    <flux:button href="{{ route('inventory.stock-counts.index') }}" variant="ghost" class="w-full">
                        Tilbake til liste
                    </flux:button>
                </div>
            </flux:card>

            <!-- Notes -->
            @if($stockCount->notes)
                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="p-4">
                        <flux:heading size="base" class="mb-2">Merknader</flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ $stockCount->notes }}</flux:text>
                    </div>
                </flux:card>
            @endif
        </div>
    </div>

    <!-- Count Modal -->
    <flux:modal wire:model="showCountModal" class="max-w-md">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4">Registrer telling</flux:heading>

            @php
                $currentLine = $countingLineId ? $stockCount->lines->find($countingLineId) : null;
            @endphp

            @if($currentLine)
                <div class="mb-4 p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                    <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $currentLine->product?->name }}</flux:text>
                    <flux:text class="text-sm text-zinc-500">Forventet: {{ number_format($currentLine->expected_quantity, 2, ',', ' ') }}</flux:text>
                </div>
            @endif

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Talt antall *</flux:label>
                    <flux:input type="number" step="0.01" min="0" wire:model="counted_quantity" autofocus />
                    <flux:error name="counted_quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>Avviksforklaring</flux:label>
                    <flux:textarea wire:model="variance_reason" rows="2" placeholder="Forklaring ved avvik" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <flux:button wire:click="closeCountModal" variant="ghost">
                    Avbryt
                </flux:button>
                <flux:button wire:click="saveCount" variant="primary">
                    Lagre
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
