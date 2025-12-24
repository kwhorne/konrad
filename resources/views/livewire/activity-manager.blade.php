<div>
    {{-- Activity filter tabs --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex gap-2">
            <flux:button wire:click="setFilter('all')" variant="{{ $filter === 'all' ? 'primary' : 'ghost' }}" size="sm">
                Alle ({{ $stats['total'] }})
            </flux:button>
            <flux:button wire:click="setFilter('pending')" variant="{{ $filter === 'pending' ? 'primary' : 'ghost' }}" size="sm">
                Ikke utført ({{ $stats['pending'] }})
            </flux:button>
            <flux:button wire:click="setFilter('completed')" variant="{{ $filter === 'completed' ? 'primary' : 'ghost' }}" size="sm">
                Utført ({{ $stats['completed'] }})
            </flux:button>
            @if($stats['overdue'] > 0)
                <flux:button wire:click="setFilter('overdue')" variant="{{ $filter === 'overdue' ? 'danger' : 'ghost' }}" size="sm" class="{{ $filter !== 'overdue' ? 'text-red-600' : '' }}">
                    Forfalt ({{ $stats['overdue'] }})
                </flux:button>
            @endif
        </div>
        <flux:button wire:click="openModal" variant="primary" size="sm">
            <flux:icon.plus class="w-4 h-4 mr-1" />
            Ny aktivitet
        </flux:button>
    </div>

    {{-- Activities list --}}
    <div class="space-y-3">
        @forelse($activities as $activity)
            <div wire:key="activity-{{ $activity->id }}" class="flex items-start gap-3 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 {{ $activity->isOverdue() ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/10' : '' }}">
                {{-- Checkbox --}}
                <button wire:click="toggleComplete({{ $activity->id }})" class="mt-0.5 flex-shrink-0">
                    @if($activity->is_completed)
                        <div class="w-5 h-5 bg-green-500 rounded flex items-center justify-center">
                            <flux:icon.check class="w-3.5 h-3.5 text-white" />
                        </div>
                    @else
                        <div class="w-5 h-5 border-2 border-zinc-300 dark:border-zinc-600 rounded hover:border-green-500 dark:hover:border-green-500 transition-colors"></div>
                    @endif
                </button>

                {{-- Icon --}}
                <div class="w-8 h-8 bg-{{ $activity->activityType->color }}-100 dark:bg-{{ $activity->activityType->color }}-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-dynamic-component :component="'flux::icon.' . $activity->activityType->icon" class="w-4 h-4 text-{{ $activity->activityType->color }}-600 dark:text-{{ $activity->activityType->color }}-400" />
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-zinc-900 dark:text-white {{ $activity->is_completed ? 'line-through text-zinc-500' : '' }}">
                            {{ $activity->subject }}
                        </span>
                        <flux:badge variant="{{ $activity->activityType->color }}" size="sm">
                            {{ $activity->activityType->name }}
                        </flux:badge>
                        <flux:badge variant="{{ $activity->getStatusColor() }}" size="sm">
                            {{ $activity->getStatusLabel() }}
                        </flux:badge>
                    </div>
                    <div class="flex items-center gap-4 mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        @if($activity->due_date)
                            <span class="flex items-center gap-1 {{ $activity->isOverdue() ? 'text-red-600 dark:text-red-400 font-medium' : '' }}">
                                <flux:icon.clock class="w-3.5 h-3.5" />
                                {{ $activity->due_date->format('d.m.Y H:i') }}
                            </span>
                        @endif
                        @if($activity->assignee)
                            <span class="flex items-center gap-1">
                                <flux:icon.user class="w-3.5 h-3.5" />
                                {{ $activity->assignee->name }}
                            </span>
                        @endif
                        <span class="flex items-center gap-1">
                            <flux:icon.calendar class="w-3.5 h-3.5" />
                            {{ $activity->created_at->format('d.m.Y') }}
                        </span>
                    </div>
                    @if($activity->description)
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">
                            {{ $activity->description }}
                        </p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-1">
                    <flux:button wire:click="openModal({{ $activity->id }})" variant="ghost" size="sm">
                        <flux:icon.pencil class="w-4 h-4" />
                    </flux:button>
                    <flux:button wire:click="delete({{ $activity->id }})" wire:confirm="Er du sikker på at du vil slette denne aktiviteten?" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
                        <flux:icon.trash class="w-4 h-4" />
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="text-center py-8 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700">
                <flux:icon.clipboard-document-list class="h-12 w-12 text-zinc-400 mx-auto mb-3" />
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    @if($filter === 'all')
                        Ingen aktiviteter registrert ennå
                    @elseif($filter === 'pending')
                        Ingen ventende aktiviteter
                    @elseif($filter === 'completed')
                        Ingen fullførte aktiviteter
                    @elseif($filter === 'overdue')
                        Ingen forfalte aktiviteter
                    @endif
                </flux:text>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($activities->hasPages())
        <div class="mt-4">
            {{ $activities->links() }}
        </div>
    @endif

    {{-- Modal --}}
    <flux:modal wire:model="showModal" class="min-w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Rediger aktivitet' : 'Ny aktivitet' }}
                </flux:heading>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Type *</flux:label>
                    <flux:select wire:model="activity_type_id">
                        <option value="">Velg type</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('activity_type_id')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Emne *</flux:label>
                    <flux:input wire:model="subject" type="text" placeholder="Kort beskrivelse av aktiviteten" />
                    @error('subject')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Beskrivelse</flux:label>
                    <flux:textarea wire:model="description" rows="3" placeholder="Detaljer om aktiviteten..."></flux:textarea>
                    @error('description')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Forfallsdato</flux:label>
                        <flux:input wire:model="due_date" type="datetime-local" />
                        @error('due_date')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>Tildelt til</flux:label>
                        <flux:select wire:model="assigned_to">
                            <option value="">Ingen</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </flux:select>
                        @error('assigned_to')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:checkbox wire:model="is_completed" label="Utført" />
                </flux:field>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeModal" variant="ghost">
                    Avbryt
                </flux:button>
                <flux:button wire:click="save" variant="primary">
                    {{ $editingId ? 'Oppdater' : 'Opprett' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
