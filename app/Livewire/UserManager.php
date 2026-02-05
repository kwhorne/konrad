<?php

namespace App\Livewire;

use App\Mail\UserInvitation;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    public string $filterRole = '';

    // Modal states
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showPasswordModal = false;

    public bool $showInviteModal = false;

    // Form fields
    public ?int $editingUserId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $title = '';

    public bool $is_admin = false;

    public bool $is_economy = false;

    public bool $is_payroll = false;

    public bool $is_active = true;

    public string $password = '';

    public string $password_confirmation = '';

    public bool $send_invitation = true;

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:100',
            'is_admin' => 'boolean',
            'is_economy' => 'boolean',
            'is_payroll' => 'boolean',
            'is_active' => 'boolean',
        ];

        if ($this->editingUserId) {
            $rules['email'] .= '|unique:users,email,'.$this->editingUserId;
        } else {
            $rules['email'] .= '|unique:users,email';
        }

        return $rules;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterRole(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->title = $user->title ?? '';
        $this->is_admin = $user->is_admin;
        $this->is_economy = $user->is_economy;
        $this->is_payroll = $user->is_payroll;
        $this->is_active = $user->is_active;
        $this->showEditModal = true;
    }

    public function openPasswordModal(int $userId): void
    {
        $this->editingUserId = $userId;
        $this->password = '';
        $this->password_confirmation = '';
        $this->showPasswordModal = true;
    }

    public function openInviteModal(int $userId): void
    {
        $this->editingUserId = $userId;
        $this->showInviteModal = true;
    }

    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showPasswordModal = false;
        $this->showInviteModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingUserId = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->title = '';
        $this->is_admin = false;
        $this->is_economy = false;
        $this->is_payroll = false;
        $this->is_active = true;
        $this->password = '';
        $this->password_confirmation = '';
        $this->send_invitation = true;
        $this->resetValidation();
    }

    public function createUser(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'title' => $this->title ?: null,
            'is_admin' => $this->is_admin,
            'is_economy' => $this->is_economy,
            'is_payroll' => $this->is_payroll,
            'is_active' => $this->is_active,
            'password' => Hash::make(str()->random(32)), // Temporary password
        ]);

        if ($this->send_invitation) {
            $this->sendInvitationEmail($user);
        }

        $this->closeModals();
        Flux::toast(text: 'Bruker opprettet', variant: 'success');
    }

    public function updateUser(): void
    {
        $this->validate();

        $user = User::findOrFail($this->editingUserId);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'title' => $this->title ?: null,
            'is_admin' => $this->is_admin,
            'is_economy' => $this->is_economy,
            'is_payroll' => $this->is_payroll,
            'is_active' => $this->is_active,
        ]);

        $this->closeModals();
        Flux::toast(text: 'Bruker oppdatert', variant: 'success');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::findOrFail($this->editingUserId);
        $user->update(['password' => Hash::make($this->password)]);

        $this->closeModals();
        Flux::toast(text: 'Passord oppdatert', variant: 'success');
    }

    public function sendInvitation(): void
    {
        $user = User::findOrFail($this->editingUserId);
        $this->sendInvitationEmail($user);

        $this->closeModals();
        Flux::toast(text: 'Invitasjon sendt til '.$user->email, variant: 'success');
    }

    public function resendInvitation(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->sendInvitationEmail($user);

        Flux::toast(text: 'Invitasjon sendt på nytt til '.$user->email, variant: 'success');
    }

    protected function sendInvitationEmail(User $user): void
    {
        $token = $user->generateInvitationToken();

        Mail::to($user->email)->send(new UserInvitation($user, $token));
    }

    public function toggleActive(int $userId): void
    {
        $user = User::findOrFail($userId);

        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            Flux::toast(text: 'Du kan ikke deaktivere din egen konto', variant: 'danger');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'aktivert' : 'deaktivert';
        Flux::toast(text: "Bruker {$status}", variant: 'success');
    }

    public function toggleAdmin(int $userId): void
    {
        $user = User::findOrFail($userId);

        // Prevent removing admin from yourself
        if ($user->id === auth()->id()) {
            Flux::toast(text: 'Du kan ikke endre din egen adminrolle', variant: 'danger');

            return;
        }

        $user->update(['is_admin' => ! $user->is_admin]);

        $role = $user->is_admin ? 'administrator' : 'bruker';
        Flux::toast(text: "Bruker er nå {$role}", variant: 'success');
    }

    public function deleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            Flux::toast(text: 'Du kan ikke slette din egen konto', variant: 'danger');

            return;
        }

        $user->delete();
        Flux::toast(text: 'Bruker slettet', variant: 'success');
    }

    public function render()
    {
        $query = User::query()
            ->with('companies')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterStatus === 'active', fn ($q) => $q->where('is_active', true)->whereNull('invitation_token'))
            ->when($this->filterStatus === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->filterStatus === 'invited', fn ($q) => $q->whereNotNull('invitation_token')->whereNull('invitation_accepted_at'))
            ->when($this->filterRole === 'admin', fn ($q) => $q->where('is_admin', true))
            ->when($this->filterRole === 'economy', fn ($q) => $q->where('is_economy', true))
            ->when($this->filterRole === 'payroll', fn ($q) => $q->where('is_payroll', true))
            ->when($this->filterRole === 'user', fn ($q) => $q->where('is_admin', false)->where('is_economy', false)->where('is_payroll', false))
            ->orderBy('name');

        return view('livewire.user-manager', [
            'users' => $query->paginate(15),
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'pendingInvitations' => User::whereNotNull('invitation_token')->whereNull('invitation_accepted_at')->count(),
        ]);
    }
}
