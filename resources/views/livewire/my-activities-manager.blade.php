<div>
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-cyan-600 to-blue-600 rounded-full blur-xl opacity-20"></div>
                <div class="relative w-14 h-14 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                    <flux:icon.clipboard-document-list class="w-7 h-7 text-white" />
                </div>
            </div>
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">
                    Mine aktiviteter
                </flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    Dine oppgaver og personlige notater
                </flux:text>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <flux:tabs wire:model="activeTab" class="mb-6">
        <flux:tab name="suggestions" icon="sparkles">Forslag</flux:tab>
        <flux:tab name="notes" icon="document-text">Mine notater</flux:tab>
    </flux:tabs>

    {{-- Suggestions Tab --}}
    <div x-show="$wire.activeTab === 'suggestions'" x-cloak>
        @if(!$hasSuggestions)
            {{-- Initial State - Generate Suggestions --}}
            <div class="flex flex-col items-center justify-center py-16">
                <div class="relative mb-8">
                    <div class="absolute inset-0 bg-gradient-to-r from-cyan-600 to-blue-600 rounded-full blur-2xl opacity-20 animate-pulse"></div>
                    <div class="relative w-32 h-32 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-full flex items-center justify-center shadow-2xl">
                        <flux:icon.sparkles class="w-16 h-16 text-white" />
                    </div>
                </div>

                <flux:heading size="xl" class="text-zinc-900 dark:text-white mb-3">
                    Forslag til aktiviteter
                </flux:heading>

                <flux:text class="text-zinc-600 dark:text-zinc-400 text-center max-w-lg mb-8">
                    Få intelligente forslag til hva du bør prioritere basert på dine ventende aktiviteter, tilbud, prosjekter, arbeidsordrer og fakturaer.
                </flux:text>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10 max-w-2xl">
                    <div class="flex items-center gap-3 p-4 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                        <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center shrink-0">
                            <flux:icon.document-text class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                        </div>
                        <div class="text-sm">
                            <div class="font-medium text-zinc-900 dark:text-white">Tilbud</div>
                            <div class="text-zinc-500 dark:text-zinc-400">Oppfølging</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center shrink-0">
                            <flux:icon.banknotes class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="text-sm">
                            <div class="font-medium text-zinc-900 dark:text-white">Fakturaer</div>
                            <div class="text-zinc-500 dark:text-zinc-400">Ubetalte</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                        <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center shrink-0">
                            <flux:icon.clipboard-document-check class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                        </div>
                        <div class="text-sm">
                            <div class="font-medium text-zinc-900 dark:text-white">Aktiviteter</div>
                            <div class="text-zinc-500 dark:text-zinc-400">Ventende</div>
                        </div>
                    </div>
                </div>

                @if($error)
                    <flux:callout variant="danger" icon="exclamation-triangle" class="mb-6 max-w-lg">
                        <flux:callout.heading>Feil ved generering</flux:callout.heading>
                        <flux:callout.text>{{ $error }}</flux:callout.text>
                    </flux:callout>
                @endif

                <flux:button
                    wire:click="generateSuggestions"
                    variant="primary"
                    class="bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25 px-6 py-3"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="generateSuggestions" class="flex items-center gap-2">
                        <flux:icon.sparkles class="w-5 h-5" />
                        Generer forslag
                    </span>
                    <span wire:loading wire:target="generateSuggestions" class="flex items-center gap-2">
                        <flux:icon.arrow-path class="w-5 h-5 animate-spin" />
                        Analyserer...
                    </span>
                </flux:button>

                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-4">
                    Analysen tar vanligvis 10-30 sekunder
                </flux:text>
            </div>
        @else
            {{-- Suggestions Results --}}
            <div class="space-y-6">
                {{-- Header with Priority Score --}}
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        @if($generatedAt)
                            <flux:text class="text-zinc-500 dark:text-zinc-400 text-sm">
                                Generert {{ \Carbon\Carbon::parse($generatedAt)->format('d.m.Y H:i') }}
                            </flux:text>
                        @endif
                    </div>
                    <flux:button wire:click="generateSuggestions" variant="ghost" size="sm">
                        <flux:icon.arrow-path class="w-4 h-4 mr-2" wire:loading.class="animate-spin" wire:target="generateSuggestions" />
                        Oppdater
                    </flux:button>
                </div>

                {{-- Priority Score Card --}}
                <div class="relative overflow-hidden bg-gradient-to-br from-{{ $this->priorityColor }}-500 to-{{ $this->priorityColor }}-600 rounded-2xl p-6 text-white shadow-xl">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>

                    <div class="relative flex flex-col md:flex-row md:items-center gap-6">
                        <div>
                            <div class="text-white/80 text-sm font-medium mb-2">Arbeidsmengde</div>
                            <div class="flex items-baseline gap-3">
                                <span class="text-5xl font-bold">{{ $suggestions['priority_score'] ?? 0 }}</span>
                                <span class="text-xl text-white/70">/100</span>
                            </div>
                        </div>

                        <div class="flex-1">
                            <p class="text-white/90 text-lg leading-relaxed">
                                {{ $suggestions['summary'] ?? 'Ingen oppsummering tilgjengelig.' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Summary Cards --}}
                @if($summary)
                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                        <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                            <div class="p-4 text-center">
                                <div class="text-2xl font-bold text-zinc-900 dark:text-white">
                                    {{ $summary['activities']['pending_count'] ?? 0 }}
                                </div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Aktiviteter</div>
                                @if(($summary['activities']['overdue_count'] ?? 0) > 0)
                                    <flux:badge size="sm" color="red" class="mt-2">
                                        {{ $summary['activities']['overdue_count'] }} forfalt
                                    </flux:badge>
                                @endif
                            </div>
                        </flux:card>

                        <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                            <div class="p-4 text-center">
                                <div class="text-2xl font-bold text-zinc-900 dark:text-white">
                                    {{ ($summary['quotes']['draft_count'] ?? 0) + ($summary['quotes']['sent_not_converted_count'] ?? 0) }}
                                </div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Tilbud</div>
                                @if(($summary['quotes']['draft_count'] ?? 0) > 0)
                                    <flux:badge size="sm" color="yellow" class="mt-2">
                                        {{ $summary['quotes']['draft_count'] }} utkast
                                    </flux:badge>
                                @endif
                            </div>
                        </flux:card>

                        <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                            <div class="p-4 text-center">
                                <div class="text-2xl font-bold text-zinc-900 dark:text-white">
                                    {{ $summary['work_orders']['pending_count'] ?? 0 }}
                                </div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Arbeidsordrer</div>
                                @if(($summary['work_orders']['overdue_count'] ?? 0) > 0)
                                    <flux:badge size="sm" color="red" class="mt-2">
                                        {{ $summary['work_orders']['overdue_count'] }} forfalt
                                    </flux:badge>
                                @endif
                            </div>
                        </flux:card>

                        <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                            <div class="p-4 text-center">
                                <div class="text-2xl font-bold text-zinc-900 dark:text-white">
                                    {{ $summary['projects']['active_count'] ?? 0 }}
                                </div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Prosjekter</div>
                                @if(($summary['projects']['overdue_count'] ?? 0) > 0)
                                    <flux:badge size="sm" color="red" class="mt-2">
                                        {{ $summary['projects']['overdue_count'] }} forfalt
                                    </flux:badge>
                                @endif
                            </div>
                        </flux:card>

                        <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                            <div class="p-4 text-center">
                                <div class="text-2xl font-bold text-zinc-900 dark:text-white">
                                    {{ $summary['invoices']['unpaid_count'] ?? 0 }}
                                </div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Ubetalte fakturaer</div>
                                @if(($summary['invoices']['overdue_count'] ?? 0) > 0)
                                    <flux:badge size="sm" color="red" class="mt-2">
                                        {{ $summary['invoices']['overdue_count'] }} forfalt
                                    </flux:badge>
                                @endif
                            </div>
                        </flux:card>
                    </div>
                @endif

                {{-- Suggestions List --}}
                @if(!empty($suggestions['suggestions']))
                    <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-xl flex items-center justify-center">
                                    <flux:icon.light-bulb class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Prioriterte forslag</flux:heading>
                            </div>

                            <div class="space-y-4">
                                @foreach($suggestions['suggestions'] as $suggestion)
                                    @php
                                        $priorityColors = [
                                            'high' => 'red',
                                            'medium' => 'yellow',
                                            'low' => 'green',
                                        ];
                                        $priorityLabels = [
                                            'high' => 'Høy',
                                            'medium' => 'Medium',
                                            'low' => 'Lav',
                                        ];
                                        $actionLabels = [
                                            'follow_up' => 'Følg opp',
                                            'complete' => 'Fullfør',
                                            'review' => 'Gjennomgå',
                                            'send' => 'Send',
                                            'call' => 'Ring',
                                        ];
                                        $entityRoutes = [
                                            'quote' => 'quotes.edit',
                                            'invoice' => 'invoices.edit',
                                            'work_order' => 'work-orders.edit',
                                            'project' => 'projects.edit',
                                            'activity' => 'activities.edit',
                                        ];
                                        $color = $priorityColors[$suggestion['priority'] ?? 'medium'] ?? 'zinc';
                                    @endphp
                                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 hover:border-{{ $color }}-300 dark:hover:border-{{ $color }}-700 transition-colors">
                                        <div class="flex items-start gap-4">
                                            <div class="shrink-0">
                                                <flux:badge size="sm" :color="$color">
                                                    {{ $priorityLabels[$suggestion['priority'] ?? 'medium'] ?? 'Medium' }}
                                                </flux:badge>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div>
                                                        <div class="font-semibold text-zinc-900 dark:text-white mb-1">
                                                            {{ $suggestion['title'] }}
                                                        </div>
                                                        <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                                            {{ $suggestion['description'] }}
                                                        </div>
                                                    </div>
                                                    @if(!empty($suggestion['entity_type']) && !empty($suggestion['entity_id']) && isset($entityRoutes[$suggestion['entity_type']]))
                                                        <flux:button
                                                            href="{{ route($entityRoutes[$suggestion['entity_type']], $suggestion['entity_id']) }}"
                                                            variant="ghost"
                                                            size="sm"
                                                            class="shrink-0"
                                                        >
                                                            {{ $actionLabels[$suggestion['action_type'] ?? 'review'] ?? 'Se' }}
                                                            <flux:icon.arrow-right class="w-4 h-4 ml-1" />
                                                        </flux:button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </flux:card>
                @endif

                {{-- Quick Wins --}}
                @if(!empty($suggestions['quick_wins']))
                    <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                                    <flux:icon.bolt class="w-5 h-5 text-green-600 dark:text-green-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Raske gevinster</flux:heading>
                            </div>

                            <ul class="space-y-2">
                                @foreach($suggestions['quick_wins'] as $quickWin)
                                    <li class="flex items-start gap-3 text-zinc-700 dark:text-zinc-300">
                                        <flux:icon.check-circle class="w-5 h-5 text-green-500 shrink-0 mt-0.5" />
                                        <span>{{ $quickWin }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </flux:card>
                @endif

                {{-- Focus Areas --}}
                @if(!empty($suggestions['focus_areas']))
                    <flux:card class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-xl flex items-center justify-center">
                                    <flux:icon.eye class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                                </div>
                                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Fokusområder</flux:heading>
                            </div>

                            <div class="space-y-3">
                                @foreach($suggestions['focus_areas'] as $area)
                                    <div class="p-4 bg-violet-50 dark:bg-zinc-800 rounded-xl border border-violet-200 dark:border-zinc-700">
                                        <div class="font-medium text-violet-900 dark:text-violet-300 mb-1">
                                            {{ $area['area'] }}
                                        </div>
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ $area['reason'] }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </flux:card>
                @endif

                {{-- Footer --}}
                <div class="flex items-center justify-center pt-4">
                    <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 flex items-center gap-2">
                        <flux:icon.sparkles class="w-4 h-4" />
                        Forslagene er generert med AI basert på dine data
                    </flux:text>
                </div>
            </div>
        @endif
    </div>

    {{-- Notes Tab --}}
    <div x-show="$wire.activeTab === 'notes'" x-cloak>
        <div class="space-y-6">
            {{-- Add Note Button --}}
            <div class="flex justify-end">
                <flux:button wire:click="openNoteModal" variant="primary">
                    <flux:icon.plus class="w-4 h-4 mr-2" />
                    Nytt notat
                </flux:button>
            </div>

            {{-- Notes List --}}
            @if($notes->isEmpty())
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-zinc-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <flux:icon.document-text class="w-8 h-8 text-zinc-400 dark:text-zinc-500" />
                    </div>
                    <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-2">
                        Ingen notater ennå
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mb-6">
                        Opprett ditt første notat for å holde orden på viktige ting.
                    </flux:text>
                    <flux:button wire:click="openNoteModal" variant="primary">
                        <flux:icon.plus class="w-4 h-4 mr-2" />
                        Opprett notat
                    </flux:button>
                </div>
            @else
                <div class="grid gap-4">
                    @foreach($notes as $note)
                        <flux:card wire:key="note-{{ $note->id }}" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 {{ $note->is_pinned ? 'ring-2 ring-amber-400 dark:ring-amber-500' : '' }}">
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            @if($note->is_pinned)
                                                <flux:icon.bookmark class="w-4 h-4 text-amber-500" />
                                            @endif
                                            @if($note->title)
                                                <flux:heading size="base" class="text-zinc-900 dark:text-white truncate">
                                                    {{ $note->title }}
                                                </flux:heading>
                                            @else
                                                <flux:text class="text-zinc-500 dark:text-zinc-400 italic">
                                                    Uten tittel
                                                </flux:text>
                                            @endif
                                        </div>
                                        <div class="prose prose-sm dark:prose-invert max-w-none text-zinc-600 dark:text-zinc-400 line-clamp-3">
                                            {!! nl2br(e($note->content)) !!}
                                        </div>
                                        <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-3">
                                            Oppdatert {{ $note->updated_at->diffForHumans() }}
                                        </flux:text>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <flux:button wire:click="togglePinNote({{ $note->id }})" variant="ghost" size="sm" title="{{ $note->is_pinned ? 'Fjern fra festet' : 'Fest notat' }}">
                                            <flux:icon.bookmark class="w-4 h-4 {{ $note->is_pinned ? 'text-amber-500' : '' }}" />
                                        </flux:button>
                                        <flux:button wire:click="openNoteModal({{ $note->id }})" variant="ghost" size="sm">
                                            <flux:icon.pencil class="w-4 h-4" />
                                        </flux:button>
                                        <flux:button wire:click="deleteNote({{ $note->id }})" wire:confirm="Er du sikker på at du vil slette dette notatet?" variant="ghost" size="sm">
                                            <flux:icon.trash class="w-4 h-4 text-red-500" />
                                        </flux:button>
                                    </div>
                                </div>
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Note Flyout --}}
    <flux:modal wire:model="showNoteModal" name="note-modal" variant="flyout" class="w-full max-w-lg">
        <div class="flex flex-col h-full">
            {{-- Header --}}
            <div class="flex items-center gap-4 pb-6 border-b border-zinc-200 dark:border-zinc-700">
                <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                    <flux:icon.document-text class="w-6 h-6 text-white" />
                </div>
                <div>
                    <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                        {{ $editingNoteId ? 'Rediger notat' : 'Nytt notat' }}
                    </flux:heading>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $editingNoteId ? 'Oppdater innholdet i notatet' : 'Skriv ned det du vil huske' }}
                    </flux:text>
                </div>
            </div>

            {{-- Content --}}
            <div class="flex-1 py-6 space-y-6 overflow-y-auto">
                <flux:field>
                    <flux:label class="text-zinc-700 dark:text-zinc-300">Tittel (valgfritt)</flux:label>
                    <flux:input
                        wire:model="noteTitle"
                        placeholder="Gi notatet en tittel..."
                        class="mt-1"
                    />
                </flux:field>

                <flux:field>
                    <flux:label class="text-zinc-700 dark:text-zinc-300">Innhold</flux:label>
                    <div class="mt-1">
                        <flux:editor
                            wire:model="noteContent"
                            toolbar="heading | bold italic underline | bullet ordered | link"
                            placeholder="Skriv ditt notat her..."
                        />
                    </div>
                    <flux:error name="noteContent" />
                </flux:field>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between pt-6 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button wire:click="closeNoteModal" variant="ghost">
                    <flux:icon.x-mark class="w-4 h-4 mr-2" />
                    Avbryt
                </flux:button>
                <flux:button wire:click="saveNote" variant="primary" class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600">
                    <flux:icon.check class="w-4 h-4 mr-2" />
                    {{ $editingNoteId ? 'Oppdater notat' : 'Lagre notat' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
