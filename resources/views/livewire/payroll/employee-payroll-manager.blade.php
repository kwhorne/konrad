<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Sok etter ansatt..."
            icon="magnifying-glass"
            class="w-full sm:w-80"
        />
        <flux:button wire:click="openCreateModal" icon="plus" variant="primary">
            Legg til ansatt
        </flux:button>
    </div>

    <!-- Employees Table -->
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Ansatt</flux:table.column>
                <flux:table.column>Ansattnr</flux:table.column>
                <flux:table.column>Stilling</flux:table.column>
                <flux:table.column>Lonnstype</flux:table.column>
                <flux:table.column>Lonn</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($employees as $employee)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar size="sm" name="{{ $employee->user->name }}" />
                                <div>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $employee->user->name }}</div>
                                    <div class="text-sm text-zinc-500">{{ $employee->user->email }}</div>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $employee->ansattnummer ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            {{ $employee->stilling ?? '-' }}
                            @if($employee->stillingsprosent < 100)
                                <span class="text-zinc-500">({{ $employee->stillingsprosent }}%)</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $employee->lonn_type_label }}</flux:table.cell>
                        <flux:table.cell>
                            @if($employee->lonn_type === 'fast')
                                {{ number_format($employee->maanedslonn ?? 0, 0, ',', ' ') }} kr/mnd
                            @else
                                {{ number_format($employee->timelonn ?? 0, 0, ',', ' ') }} kr/time
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($employee->is_active)
                                <flux:badge size="sm" color="green">Aktiv</flux:badge>
                            @else
                                <flux:badge size="sm" color="red">Inaktiv</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item wire:click="openEditModal({{ $employee->id }})" icon="pencil">
                                        Rediger
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item wire:click="delete({{ $employee->id }})" wire:confirm="Er du sikker pa at du vil slette dette oppsettet?" icon="trash" variant="danger">
                                        Slett
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8">
                            <flux:icon.users class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-600 mb-3" />
                            <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen ansatte i lonnsystemet enna</flux:text>
                            <flux:button wire:click="openCreateModal" variant="ghost" size="sm" class="mt-2">
                                Legg til forste ansatt
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($employees->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                {{ $employees->links() }}
            </div>
        @endif
    </flux:card>

    <!-- Create/Edit Modal -->
    <flux:modal wire:model="showModal" class="max-w-2xl">
        <div class="p-6">
            <flux:heading size="lg" class="mb-6">
                {{ $isEditing ? 'Rediger ansattoppsett' : 'Legg til ansatt i lonnsystemet' }}
            </flux:heading>

            <form wire:submit="save" class="space-y-6">
                @if(!$isEditing)
                    <flux:field>
                        <flux:label>Velg ansatt</flux:label>
                        <flux:select wire:model="userId">
                            <flux:select.option value="">Velg en ansatt...</flux:select.option>
                            @foreach($availableUsers as $user)
                                <flux:select.option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="userId" />
                    </flux:field>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Ansattnummer</flux:label>
                        <flux:input wire:model="ansattnummer" placeholder="f.eks. 1001" />
                        <flux:error name="ansattnummer" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Stilling</flux:label>
                        <flux:input wire:model="stilling" placeholder="f.eks. Utvikler" />
                        <flux:error name="stilling" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>Ansatt fra</flux:label>
                        <flux:input type="date" wire:model="ansattFra" />
                        <flux:error name="ansattFra" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Ansatt til</flux:label>
                        <flux:input type="date" wire:model="ansattTil" />
                        <flux:error name="ansattTil" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Stillingsprosent</flux:label>
                        <flux:input type="number" wire:model="stillingsprosent" step="0.01" min="0" max="100" />
                        <flux:error name="stillingsprosent" />
                    </flux:field>
                </div>

                <flux:separator />

                <flux:heading size="sm">Lonn</flux:heading>
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Lonnstype</flux:label>
                        <flux:select wire:model.live="lonnType">
                            <flux:select.option value="fast">Fastlonn</flux:select.option>
                            <flux:select.option value="time">Timelonn</flux:select.option>
                        </flux:select>
                        <flux:error name="lonnType" />
                    </flux:field>
                    @if($lonnType === 'fast')
                        <flux:field>
                            <flux:label>Manedslonn (kr)</flux:label>
                            <flux:input type="number" wire:model="maanedslonn" step="0.01" min="0" />
                            <flux:error name="maanedslonn" />
                        </flux:field>
                    @else
                        <flux:field>
                            <flux:label>Timelonn (kr)</flux:label>
                            <flux:input type="number" wire:model="timelonn" step="0.01" min="0" />
                            <flux:error name="timelonn" />
                        </flux:field>
                    @endif
                </div>

                <flux:separator />

                <flux:heading size="sm">Skatt</flux:heading>
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Skattetype</flux:label>
                        <flux:select wire:model.live="skattType">
                            <flux:select.option value="tabelltrekk">Tabelltrekk</flux:select.option>
                            <flux:select.option value="prosenttrekk">Prosenttrekk</flux:select.option>
                            <flux:select.option value="kildeskatt">Kildeskatt</flux:select.option>
                            <flux:select.option value="frikort">Frikort</flux:select.option>
                        </flux:select>
                        <flux:error name="skattType" />
                    </flux:field>
                    @if($skattType === 'tabelltrekk')
                        <flux:field>
                            <flux:label>Skattetabell</flux:label>
                            <flux:input wire:model="skattetabell" placeholder="f.eks. 7100" />
                            <flux:error name="skattetabell" />
                        </flux:field>
                    @elseif($skattType === 'prosenttrekk')
                        <flux:field>
                            <flux:label>Skatteprosent</flux:label>
                            <flux:input type="number" wire:model="skatteprosent" step="0.01" min="0" max="100" />
                            <flux:error name="skatteprosent" />
                        </flux:field>
                    @elseif($skattType === 'frikort')
                        <flux:field>
                            <flux:label>Frikortbelop (kr)</flux:label>
                            <flux:input type="number" wire:model="frikortBelop" step="0.01" min="0" />
                            <flux:error name="frikortBelop" />
                        </flux:field>
                    @endif
                </div>

                <flux:separator />

                <flux:heading size="sm">Feriepenger og pensjon</flux:heading>
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Feriepengeprosent</flux:label>
                        <flux:input type="number" wire:model="feriepengerProsent" step="0.1" min="0" max="20" />
                        <flux:error name="feriepengerProsent" />
                    </flux:field>
                    <flux:field>
                        <flux:label>OTP-prosent</flux:label>
                        <flux:input type="number" wire:model="otpProsent" step="0.1" min="2" max="7" />
                        <flux:error name="otpProsent" />
                    </flux:field>
                </div>
                <div class="flex flex-wrap gap-6">
                    <flux:checkbox wire:model="ferie5Uker" label="5 ukers ferie (tariffestet)" />
                    <flux:checkbox wire:model="over60" label="Over 60 ar" />
                    <flux:checkbox wire:model="otpEnabled" label="OTP aktivert" />
                </div>

                <flux:separator />

                <flux:field>
                    <flux:label>Kontonummer</flux:label>
                    <flux:input wire:model="kontonummer" placeholder="11 siffer" maxlength="11" />
                    <flux:error name="kontonummer" />
                </flux:field>

                <flux:checkbox wire:model="isActive" label="Aktiv i lonnsystemet" />

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="closeModal" variant="ghost">Avbryt</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $isEditing ? 'Lagre endringer' : 'Legg til ansatt' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
