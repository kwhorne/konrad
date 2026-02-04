<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Sok etter lonnsart..."
            icon="magnifying-glass"
            class="w-full sm:w-80"
        />
        <div class="flex gap-2">
            <flux:button wire:click="seedDefaultPayTypes" variant="ghost">
                Opprett standard lonnsarter
            </flux:button>
            <flux:button wire:click="openCreateModal" icon="plus" variant="primary">
                Ny lonnsart
            </flux:button>
        </div>
    </div>

    <!-- Pay Types Table -->
    <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Kode</flux:table.column>
                <flux:table.column>Navn</flux:table.column>
                <flux:table.column>Kategori</flux:table.column>
                <flux:table.column class="text-center">Skatt</flux:table.column>
                <flux:table.column class="text-center">AGA</flux:table.column>
                <flux:table.column class="text-center">Feriep.</flux:table.column>
                <flux:table.column class="text-center">OTP</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($payTypes as $payType)
                    <flux:table.row>
                        <flux:table.cell class="font-mono">{{ $payType->code }}</flux:table.cell>
                        <flux:table.cell class="font-medium">{{ $payType->name }}</flux:table.cell>
                        <flux:table.cell>{{ $payType->category_label }}</flux:table.cell>
                        <flux:table.cell class="text-center">
                            @if($payType->is_taxable)
                                <flux:icon.check class="w-4 h-4 text-green-500 mx-auto" />
                            @else
                                <flux:icon.x-mark class="w-4 h-4 text-zinc-400 mx-auto" />
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-center">
                            @if($payType->is_aga_basis)
                                <flux:icon.check class="w-4 h-4 text-green-500 mx-auto" />
                            @else
                                <flux:icon.x-mark class="w-4 h-4 text-zinc-400 mx-auto" />
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-center">
                            @if($payType->is_vacation_basis)
                                <flux:icon.check class="w-4 h-4 text-green-500 mx-auto" />
                            @else
                                <flux:icon.x-mark class="w-4 h-4 text-zinc-400 mx-auto" />
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-center">
                            @if($payType->is_otp_basis)
                                <flux:icon.check class="w-4 h-4 text-green-500 mx-auto" />
                            @else
                                <flux:icon.x-mark class="w-4 h-4 text-zinc-400 mx-auto" />
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($payType->is_active)
                                <flux:badge size="sm" color="green">Aktiv</flux:badge>
                            @else
                                <flux:badge size="sm" color="red">Inaktiv</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item wire:click="openEditModal({{ $payType->id }})" icon="pencil">
                                        Rediger
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item wire:click="delete({{ $payType->id }})" wire:confirm="Er du sikker pa at du vil slette denne lonnsarten?" icon="trash" variant="danger">
                                        Slett
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="text-center py-8">
                            <flux:icon.list-bullet class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-600 mb-3" />
                            <flux:text class="text-zinc-500 dark:text-zinc-400">Ingen lonnsarter opprettet enna</flux:text>
                            <flux:button wire:click="seedDefaultPayTypes" variant="ghost" size="sm" class="mt-2">
                                Opprett standard lonnsarter
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($payTypes->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                {{ $payTypes->links() }}
            </div>
        @endif
    </flux:card>

    <!-- Create/Edit Modal -->
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="p-6">
            <flux:heading size="lg" class="mb-6">
                {{ $isEditing ? 'Rediger lonnsart' : 'Ny lonnsart' }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Kode</flux:label>
                        <flux:input wire:model="code" placeholder="f.eks. 100" />
                        <flux:error name="code" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Sortering</flux:label>
                        <flux:input type="number" wire:model="sortOrder" min="0" />
                        <flux:error name="sortOrder" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Navn</flux:label>
                    <flux:input wire:model="name" placeholder="f.eks. Fastlonn" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Kategori</flux:label>
                    <flux:select wire:model="category">
                        @foreach($categories as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category" />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Standard sats (kr)</flux:label>
                        <flux:input type="number" wire:model="defaultRate" step="0.01" min="0" placeholder="Valgfritt" />
                        <flux:error name="defaultRate" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Overtidsfaktor</flux:label>
                        <flux:input type="number" wire:model="overtidFaktor" step="0.1" min="1" max="3" placeholder="f.eks. 1.5" />
                        <flux:error name="overtidFaktor" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>A-melding kode</flux:label>
                    <flux:input wire:model="aMeldingCode" placeholder="Valgfritt" />
                    <flux:error name="aMeldingCode" />
                </flux:field>

                <flux:separator />

                <div class="space-y-3">
                    <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Grunnlag for</flux:text>
                    <div class="grid grid-cols-2 gap-3">
                        <flux:checkbox wire:model="isTaxable" label="Skattepliktig" />
                        <flux:checkbox wire:model="isAgaBasis" label="Arbeidsgiveravgift" />
                        <flux:checkbox wire:model="isVacationBasis" label="Feriepenger" />
                        <flux:checkbox wire:model="isOtpBasis" label="OTP" />
                    </div>
                </div>

                <flux:checkbox wire:model="isActive" label="Aktiv" />

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="closeModal" variant="ghost">Avbryt</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $isEditing ? 'Lagre endringer' : 'Opprett lonnsart' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
