<?php

namespace App\Livewire;

use App\Mail\CompanyUserInvitation;
use App\Models\AccountingSettings;
use App\Models\Company;
use App\Models\User;
use App\Services\CompanyService;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Mail;
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

    public ?int $editingDepartmentId = null;

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
            Flux::toast(text: 'Du har ikke tilgang til å invitere brukere.', variant: 'danger');

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

            // Send invitation email
            $this->sendInvitationEmail($result['user'], $company, $this->inviteRole);

            $message = $result['is_new']
                ? 'Bruker opprettet og invitasjon sendt.'
                : 'Bruker lagt til og invitasjon sendt.';

            Flux::toast(text: $message, variant: 'success');
            $this->closeInviteModal();
        } catch (\Exception $e) {
            Flux::toast(text: 'Kunne ikke invitere bruker: '.$e->getMessage(), variant: 'danger');
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
        $this->editingDepartmentId = $user->pivot->department_id;
        $this->showEditRoleModal = true;
    }

    public function closeEditRoleModal(): void
    {
        $this->showEditRoleModal = false;
        $this->reset(['editingUserId', 'editingRole', 'editingDepartmentId']);
    }

    public function updateRole(): void
    {
        $company = app('current.company');

        if (! auth()->user()->canManage($company)) {
            Flux::toast(text: 'Du har ikke tilgang til å endre roller.', variant: 'danger');

            return;
        }

        $user = User::find($this->editingUserId);

        if (! $user) {
            return;
        }

        $companyService = app(CompanyService::class);

        $roleUpdated = $companyService->updateUserRole($company, $user, $this->editingRole);

        if (! $roleUpdated) {
            Flux::toast(text: 'Kunne ikke oppdatere rolle. Selskapet må ha minst én eier.', variant: 'danger');

            return;
        }

        // Update department if departments are enabled
        $settings = AccountingSettings::forCompany($company->id);
        if ($settings?->isDepartmentsEnabled()) {
            $departmentId = $this->editingDepartmentId ?: null;
            $companyService->updateUserDepartment($company, $user, $departmentId);
        }

        Flux::toast(text: 'Bruker oppdatert.', variant: 'success');
        $this->closeEditRoleModal();
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
            Flux::toast(text: 'Du har ikke tilgang til å fjerne brukere.', variant: 'danger');

            return;
        }

        $user = User::find($this->removingUserId);

        if (! $user) {
            $this->cancelRemove();

            return;
        }

        // Don't allow removing yourself
        if ($user->id === auth()->id()) {
            Flux::toast(text: 'Du kan ikke fjerne deg selv fra selskapet.', variant: 'danger');
            $this->cancelRemove();

            return;
        }

        $companyService = app(CompanyService::class);

        if ($companyService->removeUser($company, $user)) {
            Flux::toast(text: 'Bruker fjernet fra selskapet.', variant: 'success');
        } else {
            Flux::toast(text: 'Kunne ikke fjerne bruker. Eiere kan ikke fjernes.', variant: 'danger');
        }

        $this->cancelRemove();
    }

    public function resendInvitation(int $userId): void
    {
        $company = app('current.company');

        if (! auth()->user()->canManage($company)) {
            Flux::toast(text: 'Du har ikke tilgang til å sende invitasjoner.', variant: 'danger');

            return;
        }

        $user = $company->users()->find($userId);

        if (! $user) {
            Flux::toast(text: 'Bruker ikke funnet.', variant: 'danger');

            return;
        }

        $role = $user->pivot->role ?? 'member';
        $this->sendInvitationEmail($user, $company, $role);

        Flux::toast(text: 'Invitasjon sendt på nytt til '.$user->email, variant: 'success');
    }

    protected function sendInvitationEmail(User $user, Company $company, string $role): void
    {
        $token = $user->generateInvitationToken();

        Mail::to($user->email)->send(new CompanyUserInvitation(
            user: $user,
            company: $company,
            token: $token,
            role: $role,
            invitedBy: auth()->user(),
        ));
    }

    public function render()
    {
        $company = app('current.company');
        $users = $company ? $company->users()->orderBy('name')->get() : collect();

        $settings = $company ? AccountingSettings::forCompany($company->id) : null;
        $departmentsEnabled = $settings?->isDepartmentsEnabled() ?? false;
        $departments = $departmentsEnabled ? $company->departments()->active()->ordered()->get() : collect();

        return view('livewire.company-user-manager', [
            'users' => $users,
            'canManage' => $company && auth()->user()->canManage($company),
            'isOwner' => $company && $company->isOwner(auth()->user()),
            'departmentsEnabled' => $departmentsEnabled,
            'departments' => $departments,
        ]);
    }
}
