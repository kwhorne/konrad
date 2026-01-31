<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CompanyUserManager extends Component
{
    use AuthorizesRequests;

    public bool $showInviteModal = false;

    public bool $showEditRoleModal = false;

    public bool $showRemoveConfirmation = false;

    #[Validate('required|email')]
    public string $inviteEmail = '';

    #[Validate('nullable|string|max:255')]
    public ?string $inviteName = '';

    #[Validate('required|in:member,manager')]
    public string $inviteRole = 'member';

    public ?int $editingUserId = null;

    public string $editingRole = '';

    public ?int $removingUserId = null;

    public function openInviteModal(): void
    {
        $this->showInviteModal = true;
        $this->reset(['inviteEmail', 'inviteName', 'inviteRole']);
        $this->inviteRole = 'member';
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
        $this->reset(['inviteEmail', 'inviteName', 'inviteRole']);
    }

    public function inviteUser(): void
    {
        $this->validate([
            'inviteEmail' => 'required|email',
            'inviteRole' => 'required|in:member,manager',
        ]);

        $company = app('current.company');

        if (! auth()->user()->canManage($company)) {
            $this->dispatch('toast', message: 'Du har ikke tilgang til å invitere brukere.', variant: 'danger');

            return;
        }

        $companyService = app(CompanyService::class);

        try {
            $result = $companyService->inviteUser(
                $company,
                $this->inviteEmail,
                $this->inviteRole,
                $this->inviteName ?: null
            );

            $message = $result['is_new']
                ? 'Bruker opprettet og lagt til i selskapet.'
                : 'Bruker lagt til i selskapet.';

            $this->dispatch('toast', message: $message, variant: 'success');
            $this->closeInviteModal();
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Kunne ikke invitere bruker: '.$e->getMessage(), variant: 'danger');
        }
    }

    public function openEditRoleModal(int $userId): void
    {
        $company = app('current.company');
        $user = $company->users()->find($userId);

        if (! $user) {
            return;
        }

        $this->editingUserId = $userId;
        $this->editingRole = $user->pivot->role;
        $this->showEditRoleModal = true;
    }

    public function closeEditRoleModal(): void
    {
        $this->showEditRoleModal = false;
        $this->reset(['editingUserId', 'editingRole']);
    }

    public function updateRole(): void
    {
        $company = app('current.company');

        if (! auth()->user()->canManage($company)) {
            $this->dispatch('toast', message: 'Du har ikke tilgang til å endre roller.', variant: 'danger');

            return;
        }

        $user = User::find($this->editingUserId);

        if (! $user) {
            return;
        }

        $companyService = app(CompanyService::class);

        if ($companyService->updateUserRole($company, $user, $this->editingRole)) {
            $this->dispatch('toast', message: 'Rolle oppdatert.', variant: 'success');
            $this->closeEditRoleModal();
        } else {
            $this->dispatch('toast', message: 'Kunne ikke oppdatere rolle. Selskapet må ha minst én eier.', variant: 'danger');
        }
    }

    public function confirmRemoveUser(int $userId): void
    {
        $this->removingUserId = $userId;
        $this->showRemoveConfirmation = true;
    }

    public function cancelRemove(): void
    {
        $this->showRemoveConfirmation = false;
        $this->removingUserId = null;
    }

    public function removeUser(): void
    {
        $company = app('current.company');

        if (! auth()->user()->canManage($company)) {
            $this->dispatch('toast', message: 'Du har ikke tilgang til å fjerne brukere.', variant: 'danger');

            return;
        }

        $user = User::find($this->removingUserId);

        if (! $user) {
            $this->cancelRemove();

            return;
        }

        // Don't allow removing yourself
        if ($user->id === auth()->id()) {
            $this->dispatch('toast', message: 'Du kan ikke fjerne deg selv fra selskapet.', variant: 'danger');
            $this->cancelRemove();

            return;
        }

        $companyService = app(CompanyService::class);

        if ($companyService->removeUser($company, $user)) {
            $this->dispatch('toast', message: 'Bruker fjernet fra selskapet.', variant: 'success');
        } else {
            $this->dispatch('toast', message: 'Kunne ikke fjerne bruker. Eiere kan ikke fjernes.', variant: 'danger');
        }

        $this->cancelRemove();
    }

    public function render()
    {
        $company = app('current.company');
        $users = $company ? $company->users()->orderBy('name')->get() : collect();

        return view('livewire.company-user-manager', [
            'users' => $users,
            'canManage' => $company && auth()->user()->canManage($company),
            'isOwner' => $company && $company->isOwner(auth()->user()),
        ]);
    }
}
