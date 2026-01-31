<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="lg">Brukere</flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                Administrer brukere som har tilgang til dette selskapet.
            </flux:text>
        </div>
        @if($canManage)
            <flux:button wire:click="openInviteModal" variant="primary">
                <flux:icon.plus class="w-4 h-4 mr-2" />
                Inviter bruker
            </flux:button>
        @endif
    </div>

    <!-- Users Table -->
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Bruker</flux:table.column>
            <flux:table.column>Rolle</flux:table.column>
            <flux:table.column>Ble med</flux:table.column>
            @if($canManage)
                <flux:table.column class="text-right">Handlinger</flux:table.column>
            @endif
        </flux:table.columns>
        <flux:table.rows>
            @forelse($users as $user)
                <flux:table.row wire:key="user-{{ $user->id }}">
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar size="sm" name="{{ $user->name }}" />
                            <div>
                                <div class="font-medium text-zinc-900 dark:text-white">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                        <flux:badge variant="outline" size="sm" class="ml-1">Deg</flux:badge>
                                    @endif
                                </div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $user->email }}</div>
                            </div>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        @php
                            $role = $user->pivot->role;
                            $roleLabel = match($role) {
                                'owner' => 'Eier',
                                'manager' => 'Administrator',
                                'member' => 'Medlem',
                                default => $role,
                            };
                            $roleVariant = match($role) {
                                'owner' => 'success',
                                'manager' => 'warning',
                                'member' => 'outline',
                                default => 'outline',
                            };
                        @endphp
                        <flux:badge :variant="$roleVariant">{{ $roleLabel }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($user->pivot->joined_at)
                            {{ \Carbon\Carbon::parse($user->pivot->joined_at)->format('d.m.Y') }}
                        @else
                            -
                        @endif
                    </flux:table.cell>
                    @if($canManage)
                        <flux:table.cell class="text-right">
                            @if($user->id !== auth()->id() && $user->pivot->role !== 'owner')
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item wire:click="openEditRoleModal({{ $user->id }})" icon="pencil">
                                            Endre rolle
                                        </flux:menu.item>
                                        <flux:menu.item wire:click="confirmRemoveUser({{ $user->id }})" icon="trash" variant="danger">
                                            Fjern fra selskap
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            @endif
                        </flux:table.cell>
                    @endif
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="{{ $canManage ? 4 : 3 }}" class="text-center py-8">
                        <flux:icon.users class="w-10 h-10 text-zinc-400 mx-auto mb-2" />
                        <flux:text class="text-zinc-500">Ingen brukere funnet.</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <!-- Invite User Modal -->
    <flux:modal wire:model="showInviteModal" name="invite-user-modal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Inviter bruker</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    Legg til en ny bruker i selskapet.
                </flux:text>
            </div>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>E-postadresse *</flux:label>
                    <flux:input wire:model="inviteEmail" type="email" placeholder="bruker@eksempel.no" />
                    <flux:error name="inviteEmail" />
                </flux:field>

                <flux:field>
                    <flux:label>Navn (valgfritt)</flux:label>
                    <flux:input wire:model="inviteName" placeholder="Ola Nordmann" />
                    <flux:description>Hvis brukeren ikke finnes, vil de bli opprettet med dette navnet.</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Rolle *</flux:label>
                    <flux:select wire:model="inviteRole">
                        <flux:select.option value="member">Medlem - Standard tilgang til selskapsdata</flux:select.option>
                        <flux:select.option value="manager">Administrator - Kan invitere og administrere brukere</flux:select.option>
                    </flux:select>
                    <flux:error name="inviteRole" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeInviteModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="inviteUser" variant="primary">
                    <flux:icon.paper-airplane class="w-4 h-4 mr-2" />
                    Send invitasjon
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Edit Role Modal -->
    <flux:modal wire:model="showEditRoleModal" name="edit-role-modal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Endre rolle</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    Endre rollen til denne brukeren.
                </flux:text>
            </div>

            <flux:field>
                <flux:label>Rolle</flux:label>
                <flux:select wire:model="editingRole">
                    <flux:select.option value="member">Medlem</flux:select.option>
                    <flux:select.option value="manager">Administrator</flux:select.option>
                    @if($isOwner)
                        <flux:select.option value="owner">Eier</flux:select.option>
                    @endif
                </flux:select>
            </flux:field>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeEditRoleModal" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="updateRole" variant="primary">Lagre</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Remove Confirmation Modal -->
    <flux:modal wire:model="showRemoveConfirmation" name="remove-confirmation-modal" variant="danger">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Fjern bruker</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    Er du sikker på at du vil fjerne denne brukeren fra selskapet? De vil miste tilgang til all selskapsdata.
                </flux:text>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="cancelRemove" variant="ghost">Avbryt</flux:button>
                <flux:button wire:click="removeUser" variant="danger">
                    <flux:icon.trash class="w-4 h-4 mr-2" />
                    Fjern bruker
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
