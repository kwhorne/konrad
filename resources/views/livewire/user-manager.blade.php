<div>
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-indigo-100 dark:bg-indigo-900/30">
                    <flux:icon.users class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Totalt brukere</flux:text>
                    <flux:heading size="xl">{{ $totalUsers }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-green-100 dark:bg-green-900/30">
                    <flux:icon.check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Aktive brukere</flux:text>
                    <flux:heading size="xl">{{ $activeUsers }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card class="bg-white dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-yellow-100 dark:bg-yellow-900/30">
                    <flux:icon.envelope class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Ventende invitasjoner</flux:text>
                    <flux:heading size="xl">{{ $pendingInvitations }}</flux:heading>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Filters and Actions --}}
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Sok etter navn, e-post eller telefon..." icon="magnifying-glass" />
        </div>
        <div class="flex gap-4">
            <flux:select wire:model.live="filterStatus" placeholder="Alle statuser">
                <flux:select.option value="">Alle statuser</flux:select.option>
                <flux:select.option value="active">Aktive</flux:select.option>
                <flux:select.option value="inactive">Inaktive</flux:select.option>
                <flux:select.option value="invited">Invitert</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="filterRole" placeholder="Alle roller">
                <flux:select.option value="">Alle roller</flux:select.option>
                <flux:select.option value="admin">Administratorer</flux:select.option>
                <flux:select.option value="economy">Økonomi</flux:select.option>
                <flux:select.option value="user">Brukere</flux:select.option>
            </flux:select>

            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                Ny bruker
            </flux:button>
        </div>
    </div>

    {{-- Users Table --}}
    <flux:card class="bg-white dark:bg-zinc-900">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Bruker</flux:table.column>
                <flux:table.column>Kontakt</flux:table.column>
                <flux:table.column>Rolle</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Siste innlogging</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($users as $user)
                    <flux:table.row wire:key="user-{{ $user->id }}">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar size="sm" name="{{ $user->name }}" />
                                <div>
                                    <flux:text class="font-medium">{{ $user->name }}</flux:text>
                                    @if($user->title)
                                        <flux:text class="text-sm text-zinc-500">{{ $user->title }}</flux:text>
                                    @endif
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="space-y-1">
                                <flux:text class="text-sm">{{ $user->email }}</flux:text>
                                @if($user->phone)
                                    <flux:text class="text-sm text-zinc-500">{{ $user->phone }}</flux:text>
                                @endif
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-wrap gap-1">
                                @if($user->is_admin)
                                    <flux:badge color="indigo">Admin</flux:badge>
                                @endif
                                @if($user->is_economy)
                                    <flux:badge color="emerald">Økonomi</flux:badge>
                                @endif
                                @if(!$user->is_admin && !$user->is_economy)
                                    <flux:badge color="zinc">Bruker</flux:badge>
                                @endif
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="{{ $user->status_color }}">{{ $user->status_label }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($user->last_login_at)
                                <flux:text class="text-sm text-zinc-500">{{ $user->last_login_at->diffForHumans() }}</flux:text>
                            @else
                                <flux:text class="text-sm text-zinc-400">Aldri</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>
                                    <flux:menu.item wire:click="openEditModal({{ $user->id }})" icon="pencil">
                                        Rediger
                                    </flux:menu.item>

                                    <flux:menu.item wire:click="openPasswordModal({{ $user->id }})" icon="key">
                                        Endre passord
                                    </flux:menu.item>

                                    @if($user->hasPendingInvitation())
                                        <flux:menu.item wire:click="resendInvitation({{ $user->id }})" icon="envelope">
                                            Send invitasjon på nytt
                                        </flux:menu.item>
                                    @endif

                                    <flux:menu.separator />

                                    @if($user->id !== auth()->id())
                                        <flux:menu.item wire:click="toggleAdmin({{ $user->id }})" icon="shield-check">
                                            {{ $user->is_admin ? 'Fjern admin' : 'Gjor til admin' }}
                                        </flux:menu.item>

                                        <flux:menu.item wire:click="toggleActive({{ $user->id }})" icon="{{ $user->is_active ? 'x-circle' : 'check-circle' }}">
                                            {{ $user->is_active ? 'Deaktiver' : 'Aktiver' }}
                                        </flux:menu.item>

                                        <flux:menu.separator />

                                        <flux:menu.item wire:click="deleteUser({{ $user->id }})" wire:confirm="Er du sikker på at du vil slette denne brukeren?" icon="trash" variant="danger">
                                            Slett bruker
                                        </flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-8">
                            <flux:text class="text-zinc-500">Ingen brukere funnet</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($users->hasPages())
            <div class="mt-4 px-4 pb-4">
                {{ $users->links() }}
            </div>
        @endif
    </flux:card>

    {{-- Create User Modal --}}
    <flux:modal wire:model="showCreateModal" class="max-w-lg">
        <form wire:submit="createUser">
            <flux:heading size="lg">Opprett ny bruker</flux:heading>
            <flux:text class="mt-1 mb-6">Fyll ut informasjon for den nye brukeren.</flux:text>

            <div class="space-y-4">
                <flux:input wire:model="name" label="Navn" placeholder="Ola Nordmann" required />
                <flux:input wire:model="email" label="E-post" type="email" placeholder="ola@firma.no" required />
                <flux:input wire:model="phone" label="Telefon" type="tel" placeholder="+47 123 45 678" />
                <flux:input wire:model="title" label="Stilling" placeholder="Daglig leder" />

                <div class="flex flex-col gap-4">
                    <flux:checkbox wire:model="is_admin" label="Administrator" description="Gi tilgang til admin-panelet" />
                    <flux:checkbox wire:model="is_economy" label="Økonomi" description="Gi tilgang til økonomi-panelet" />
                    <flux:checkbox wire:model="is_active" label="Aktiv" description="Brukeren kan logge inn" />
                </div>

                <flux:separator />

                <flux:checkbox wire:model="send_invitation" label="Send invitasjon" description="Send e-post med invitasjonslenke" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <flux:button wire:click="closeModals" variant="ghost">Avbryt</flux:button>
                <flux:button type="submit" variant="primary">Opprett bruker</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit User Modal --}}
    <flux:modal wire:model="showEditModal" class="max-w-lg">
        <form wire:submit="updateUser">
            <flux:heading size="lg">Rediger bruker</flux:heading>
            <flux:text class="mt-1 mb-6">Oppdater brukerinformasjon.</flux:text>

            <div class="space-y-4">
                <flux:input wire:model="name" label="Navn" placeholder="Ola Nordmann" required />
                <flux:input wire:model="email" label="E-post" type="email" placeholder="ola@firma.no" required />
                <flux:input wire:model="phone" label="Telefon" type="tel" placeholder="+47 123 45 678" />
                <flux:input wire:model="title" label="Stilling" placeholder="Daglig leder" />

                <div class="flex flex-col gap-4">
                    <flux:checkbox wire:model="is_admin" label="Administrator" description="Gi tilgang til admin-panelet" />
                    <flux:checkbox wire:model="is_economy" label="Økonomi" description="Gi tilgang til økonomi-panelet" />
                    <flux:checkbox wire:model="is_active" label="Aktiv" description="Brukeren kan logge inn" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <flux:button wire:click="closeModals" variant="ghost">Avbryt</flux:button>
                <flux:button type="submit" variant="primary">Lagre endringer</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Change Password Modal --}}
    <flux:modal wire:model="showPasswordModal" class="max-w-md">
        <form wire:submit="updatePassword">
            <flux:heading size="lg">Endre passord</flux:heading>
            <flux:text class="mt-1 mb-6">Angi et nytt passord for brukeren.</flux:text>

            <div class="space-y-4">
                <flux:input wire:model="password" label="Nytt passord" type="password" required />
                <flux:input wire:model="password_confirmation" label="Bekreft passord" type="password" required />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <flux:button wire:click="closeModals" variant="ghost">Avbryt</flux:button>
                <flux:button type="submit" variant="primary">Oppdater passord</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
