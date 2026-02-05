<?php

namespace App\Livewire\Payroll;

use App\Models\EmployeePayrollSettings;
use App\Models\User;
use App\Services\Payroll\SkattekortService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeePayrollManager extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $editingId = null;

    // Form fields
    public ?int $userId = null;

    public ?string $ansattnummer = null;

    public ?string $personnummer = null;

    // Personal info
    public ?string $personalEmail = null;

    public ?string $phone = null;

    public ?string $addressStreet = null;

    public ?string $addressPostalCode = null;

    public ?string $addressCity = null;

    public string $addressCountry = 'NO';

    public ?string $birthDate = null;

    // Emergency contact
    public ?string $emergencyContactName = null;

    public ?string $emergencyContactRelation = null;

    public ?string $emergencyContactPhone = null;

    // Current tab in modal
    public string $activeTab = 'employment';

    public ?string $ansattFra = null;

    public ?string $ansattTil = null;

    public float $stillingsprosent = 100.00;

    public ?string $stilling = null;

    public string $lonnType = 'fast';

    public ?float $maanedslonn = null;

    public ?float $timelonn = null;

    public ?float $aarslonn = null;

    public string $skattType = 'tabelltrekk';

    public ?string $skattetabell = '7100';

    public ?float $skatteprosent = null;

    public ?float $frikortBelop = null;

    public float $feriepengerProsent = 10.2;

    public bool $ferie5Uker = false;

    public bool $over60 = false;

    public bool $otpEnabled = true;

    public float $otpProsent = 2.0;

    public ?string $kontonummer = null;

    public bool $isActive = true;

    protected function rules(): array
    {
        return [
            'userId' => 'required|exists:users,id',
            'ansattnummer' => 'nullable|string|max:20',
            'personnummer' => ['nullable', 'string', 'size:11', 'regex:/^\d{11}$/'],
            'personalEmail' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'addressStreet' => 'nullable|string|max:255',
            'addressPostalCode' => 'nullable|string|max:10',
            'addressCity' => 'nullable|string|max:100',
            'addressCountry' => 'nullable|string|size:2',
            'birthDate' => 'nullable|date|before:today',
            'emergencyContactName' => 'nullable|string|max:255',
            'emergencyContactRelation' => 'nullable|string|max:100',
            'emergencyContactPhone' => 'nullable|string|max:20',
            'ansattFra' => 'nullable|date',
            'ansattTil' => 'nullable|date|after_or_equal:ansattFra',
            'stillingsprosent' => 'required|numeric|min:0|max:100',
            'stilling' => 'nullable|string|max:255',
            'lonnType' => 'required|in:fast,time',
            'maanedslonn' => 'nullable|numeric|min:0',
            'timelonn' => 'nullable|numeric|min:0',
            'aarslonn' => 'nullable|numeric|min:0',
            'skattType' => 'required|in:tabelltrekk,prosenttrekk,kildeskatt,frikort',
            'skattetabell' => 'nullable|string|max:10',
            'skatteprosent' => 'nullable|numeric|min:0|max:100',
            'frikortBelop' => 'nullable|numeric|min:0',
            'feriepengerProsent' => 'required|numeric|min:0|max:20',
            'ferie5Uker' => 'boolean',
            'over60' => 'boolean',
            'otpEnabled' => 'boolean',
            'otpProsent' => 'required|numeric|min:2|max:7',
            'kontonummer' => 'nullable|string|max:11',
            'isActive' => 'boolean',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset([
            'editingId', 'userId', 'ansattnummer', 'personnummer',
            'personalEmail', 'phone', 'addressStreet', 'addressPostalCode', 'addressCity',
            'birthDate', 'emergencyContactName', 'emergencyContactRelation', 'emergencyContactPhone',
            'ansattFra', 'ansattTil', 'stillingsprosent', 'stilling', 'lonnType', 'maanedslonn', 'timelonn',
            'aarslonn', 'skattType', 'skattetabell', 'skatteprosent', 'frikortBelop',
            'feriepengerProsent', 'ferie5Uker', 'over60', 'otpEnabled', 'otpProsent',
            'kontonummer', 'isActive',
        ]);
        $this->stillingsprosent = 100.00;
        $this->feriepengerProsent = 10.2;
        $this->otpProsent = 2.0;
        $this->otpEnabled = true;
        $this->isActive = true;
        $this->addressCountry = 'NO';
        $this->activeTab = 'employment';
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $settings = EmployeePayrollSettings::findOrFail($id);

        $this->editingId = $id;
        $this->userId = $settings->user_id;
        $this->ansattnummer = $settings->ansattnummer;
        $this->personnummer = $settings->personnummer;
        $this->personalEmail = $settings->personal_email;
        $this->phone = $settings->phone;
        $this->addressStreet = $settings->address_street;
        $this->addressPostalCode = $settings->address_postal_code;
        $this->addressCity = $settings->address_city;
        $this->addressCountry = $settings->address_country ?? 'NO';
        $this->birthDate = $settings->birth_date?->format('Y-m-d');
        $this->emergencyContactName = $settings->emergency_contact_name;
        $this->emergencyContactRelation = $settings->emergency_contact_relation;
        $this->emergencyContactPhone = $settings->emergency_contact_phone;
        $this->ansattFra = $settings->ansatt_fra?->format('Y-m-d');
        $this->ansattTil = $settings->ansatt_til?->format('Y-m-d');
        $this->stillingsprosent = (float) $settings->stillingsprosent;
        $this->stilling = $settings->stilling;
        $this->lonnType = $settings->lonn_type;
        $this->maanedslonn = $settings->maanedslonn ? (float) $settings->maanedslonn : null;
        $this->timelonn = $settings->timelonn ? (float) $settings->timelonn : null;
        $this->aarslonn = $settings->aarslonn ? (float) $settings->aarslonn : null;
        $this->skattType = $settings->skatt_type;
        $this->skattetabell = $settings->skattetabell;
        $this->skatteprosent = $settings->skatteprosent ? (float) $settings->skatteprosent : null;
        $this->frikortBelop = $settings->frikort_belop ? (float) $settings->frikort_belop : null;
        $this->feriepengerProsent = (float) $settings->feriepenger_prosent;
        $this->ferie5Uker = $settings->ferie_5_uker;
        $this->over60 = $settings->over_60;
        $this->otpEnabled = $settings->otp_enabled;
        $this->otpProsent = (float) $settings->otp_prosent;
        $this->kontonummer = $settings->kontonummer;
        $this->isActive = $settings->is_active;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->authorize('create', EmployeePayrollSettings::class);
        $this->validate();

        $data = [
            'user_id' => $this->userId,
            'ansattnummer' => $this->ansattnummer,
            'personnummer' => $this->personnummer,
            'personal_email' => $this->personalEmail,
            'phone' => $this->phone,
            'address_street' => $this->addressStreet,
            'address_postal_code' => $this->addressPostalCode,
            'address_city' => $this->addressCity,
            'address_country' => $this->addressCountry,
            'birth_date' => $this->birthDate,
            'emergency_contact_name' => $this->emergencyContactName,
            'emergency_contact_relation' => $this->emergencyContactRelation,
            'emergency_contact_phone' => $this->emergencyContactPhone,
            'ansatt_fra' => $this->ansattFra,
            'ansatt_til' => $this->ansattTil,
            'stillingsprosent' => $this->stillingsprosent,
            'stilling' => $this->stilling,
            'lonn_type' => $this->lonnType,
            'maanedslonn' => $this->maanedslonn,
            'timelonn' => $this->timelonn,
            'aarslonn' => $this->aarslonn,
            'skatt_type' => $this->skattType,
            'skattetabell' => $this->skattetabell,
            'skatteprosent' => $this->skatteprosent,
            'frikort_belop' => $this->frikortBelop,
            'feriepenger_prosent' => $this->feriepengerProsent,
            'ferie_5_uker' => $this->ferie5Uker,
            'over_60' => $this->over60,
            'otp_enabled' => $this->otpEnabled,
            'otp_prosent' => $this->otpProsent,
            'kontonummer' => $this->kontonummer,
            'is_active' => $this->isActive,
        ];

        if ($this->isEditing) {
            $settings = EmployeePayrollSettings::findOrFail($this->editingId);
            $settings->update($data);
            session()->flash('success', 'Ansattoppsett oppdatert.');
        } else {
            EmployeePayrollSettings::create($data);
            session()->flash('success', 'Ansatt lagt til i lonnsystemet.');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $settings = EmployeePayrollSettings::findOrFail($id);
        $this->authorize('delete', $settings);
        $settings->delete();
        session()->flash('success', 'Ansattoppsett slettet.');
    }

    public function fetchTaxCard(int $id): void
    {
        $settings = EmployeePayrollSettings::findOrFail($id);
        $this->authorize('update', $settings);

        try {
            $service = app(SkattekortService::class);
            $result = $service->fetchTaxCard($settings);

            session()->flash('success', 'Skattekort hentet. Trekktype: '.ucfirst($result['trekktype'] ?? 'ukjent'));

            // Refresh the form data if we're editing this employee
            if ($this->editingId === $id) {
                $this->openEditModal($id);
            }
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function fetchTaxCardForCurrent(): void
    {
        if (! $this->editingId) {
            return;
        }

        $this->fetchTaxCard($this->editingId);
    }

    public function fetchAllTaxCards(): void
    {
        $this->authorize('viewAny', EmployeePayrollSettings::class);

        try {
            $service = app(SkattekortService::class);

            if (! $service->isAvailable()) {
                session()->flash('error', 'Skattekort API er ikke konfigurert. Kontakt administrator.');

                return;
            }

            $result = $service->syncAllEmployees();

            if ($result['success'] > 0 && $result['failed'] === 0) {
                session()->flash('success', "Hentet skattekort for {$result['success']} ansatte.");
            } elseif ($result['success'] > 0 && $result['failed'] > 0) {
                session()->flash('warning', "Hentet skattekort for {$result['success']} ansatte. {$result['failed']} feilet.");
            } elseif ($result['failed'] > 0) {
                session()->flash('error', "Kunne ikke hente skattekort. {$result['failed']} feilet.");
            } else {
                session()->flash('info', 'Ingen ansatte med personnummer å hente skattekort for.');
            }
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $company = app('current.company');

        $employees = EmployeePayrollSettings::where('company_id', $company->id)
            ->with('user')
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                })
                    ->orWhere('ansattnummer', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get users not yet in payroll system
        $existingUserIds = EmployeePayrollSettings::where('company_id', $company->id)
            ->pluck('user_id');

        $availableUsers = User::whereHas('companies', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        })
            ->whereNotIn('id', $existingUserIds)
            ->active()
            ->orderBy('name')
            ->get();

        return view('livewire.payroll.employee-payroll-manager', [
            'employees' => $employees,
            'availableUsers' => $availableUsers,
        ]);
    }
}
