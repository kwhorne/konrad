<?php

namespace App\Livewire\Admin;

use App\Mail\PlatformInvoiceMail;
use App\Models\Company;
use App\Models\CompanyModule;
use App\Models\Module;
use App\Models\PlatformInvoice;
use App\Services\ModuleService;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class CompanyDetailManager extends Component
{
    use AuthorizesRequests;

    public int $companyId;

    // Info form
    public string $name = '';

    public string $organizationNumber = '';

    public string $vatNumber = '';

    public string $address = '';

    public string $postalCode = '';

    public string $city = '';

    public string $country = '';

    public string $phone = '';

    public string $email = '';

    public string $billingEmail = '';

    public string $website = '';

    public bool $isActive = true;

    // Module modal
    public bool $showModuleModal = false;

    /** @var array<int, bool> */
    public array $moduleStates = [];

    /** @var array<int, string> */
    public array $moduleExpiries = [];

    // Invoice form
    public bool $showInvoiceModal = false;

    public string $invoiceDescription = '';

    public string $invoiceAmount = '';

    public string $invoiceDueDate = '';

    public string $invoiceNotes = '';

    public function mount(int $companyId): void
    {
        $company = Company::withoutGlobalScopes()->findOrFail($companyId);
        $this->companyId = $companyId;
        $this->fillForm($company);
    }

    private function fillForm(Company $company): void
    {
        $this->name = $company->name ?? '';
        $this->organizationNumber = $company->organization_number ?? '';
        $this->vatNumber = $company->vat_number ?? '';
        $this->address = $company->address ?? '';
        $this->postalCode = $company->postal_code ?? '';
        $this->city = $company->city ?? '';
        $this->country = $company->country ?? '';
        $this->phone = $company->phone ?? '';
        $this->email = $company->email ?? '';
        $this->billingEmail = $company->billing_email ?? '';
        $this->website = $company->website ?? '';
        $this->isActive = (bool) $company->is_active;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'organizationNumber' => ['nullable', 'string', 'max:20'],
            'vatNumber' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'postalCode' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'billingEmail' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        $company = Company::withoutGlobalScopes()->findOrFail($this->companyId);
        $company->update([
            'name' => $this->name,
            'organization_number' => $this->organizationNumber,
            'vat_number' => $this->vatNumber,
            'address' => $this->address,
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'billing_email' => $this->billingEmail ?: null,
            'website' => $this->website,
            'is_active' => $this->isActive,
        ]);

        Flux::toast(text: 'Selskap oppdatert', variant: 'success');
    }

    public function openModuleModal(): void
    {
        $modules = Module::active()->premium()->ordered()->get();
        $companyModules = CompanyModule::where('company_id', $this->companyId)->get()->keyBy('module_id');

        $this->moduleStates = [];
        $this->moduleExpiries = [];

        foreach ($modules as $module) {
            $companyModule = $companyModules->get($module->id);
            $this->moduleStates[$module->id] = $companyModule?->isActive() ?? false;
            $this->moduleExpiries[$module->id] = $companyModule?->expires_at?->format('Y-m-d') ?? '';
        }

        $this->showModuleModal = true;
    }

    public function closeModuleModal(): void
    {
        $this->showModuleModal = false;
        $this->moduleStates = [];
        $this->moduleExpiries = [];
    }

    public function saveModules(): void
    {
        $company = Company::withoutGlobalScopes()->findOrFail($this->companyId);
        $moduleService = app(ModuleService::class);

        foreach ($this->moduleStates as $moduleId => $enabled) {
            $module = Module::find($moduleId);
            if (! $module) {
                continue;
            }

            $expiresAt = ! empty($this->moduleExpiries[$moduleId])
                ? Carbon::parse($this->moduleExpiries[$moduleId])->endOfDay()
                : null;

            if ($enabled) {
                $moduleService->enableForCompany($company, $module, 'admin', $expiresAt);
            } else {
                $moduleService->disableForCompany($company, $module);
            }
        }

        Flux::toast(text: 'Moduler oppdatert', variant: 'success');
        $this->closeModuleModal();
    }

    public function openInvoiceModal(): void
    {
        // Pre-fill description and amount from active modules
        $companyModules = CompanyModule::where('company_id', $this->companyId)
            ->with('module')
            ->get()
            ->filter(fn ($cm) => $cm->isActive() && $cm->module?->price_monthly > 0);

        if ($companyModules->isNotEmpty()) {
            $lines = $companyModules->map(fn ($cm) => $cm->module->name)->join(', ');
            $month = now()->isoFormat('MMMM YYYY');
            $this->invoiceDescription = "Månedlig lisens — {$lines}, {$month}";
            $this->invoiceAmount = number_format(
                $companyModules->sum(fn ($cm) => $cm->module->price_monthly) / 100,
                2, '.', ''
            );
        } else {
            $this->invoiceDescription = '';
            $this->invoiceAmount = '';
        }

        $this->invoiceDueDate = now()->addDays(14)->format('Y-m-d');
        $this->invoiceNotes = '';
        $this->showInvoiceModal = true;
    }

    public function closeInvoiceModal(): void
    {
        $this->showInvoiceModal = false;
    }

    public function createInvoice(): void
    {
        $this->validate([
            'invoiceDescription' => ['required', 'string', 'max:500'],
            'invoiceAmount' => ['required', 'numeric', 'min:1'],
            'invoiceDueDate' => ['required', 'date'],
            'invoiceNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        PlatformInvoice::create([
            'company_id' => $this->companyId,
            'invoice_number' => PlatformInvoice::generateInvoiceNumber(),
            'description' => $this->invoiceDescription,
            'amount' => (int) round((float) str_replace(',', '.', $this->invoiceAmount) * 100),
            'due_date' => $this->invoiceDueDate,
            'sent_at' => now(),
            'notes' => $this->invoiceNotes ?: null,
        ]);

        Flux::toast(text: 'Faktura opprettet', variant: 'success');
        $this->closeInvoiceModal();
    }

    public function markAsPaid(int $invoiceId): void
    {
        $invoice = PlatformInvoice::where('company_id', $this->companyId)->findOrFail($invoiceId);
        $invoice->update(['paid_at' => now()]);

        Flux::toast(text: 'Faktura markert som betalt', variant: 'success');
    }

    public function sendInvoice(int $invoiceId): void
    {
        $invoice = PlatformInvoice::where('company_id', $this->companyId)
            ->with('company')
            ->findOrFail($invoiceId);

        $recipient = $invoice->company->effective_billing_email;

        if (! $recipient) {
            Flux::toast(text: 'Ingen e-postadresse registrert på selskapet', variant: 'danger');

            return;
        }

        Mail::to($recipient)->send(new PlatformInvoiceMail($invoice));

        $invoice->update(['sent_at' => now()]);

        Flux::toast(text: "Faktura sendt til {$recipient}", variant: 'success');
    }

    public function markAsUnpaid(int $invoiceId): void
    {
        $invoice = PlatformInvoice::where('company_id', $this->companyId)->findOrFail($invoiceId);
        $invoice->update(['paid_at' => null]);

        Flux::toast(text: 'Faktura markert som ubetalt', variant: 'warning');
    }

    public function render(): \Illuminate\View\View
    {
        $company = Company::withoutGlobalScopes()
            ->with(['users'])
            ->findOrFail($this->companyId);

        $companyModules = CompanyModule::where('company_id', $this->companyId)
            ->with('module')
            ->get();

        $totalMonthlyOre = $companyModules
            ->filter(fn ($cm) => $cm->isActive())
            ->sum(fn ($cm) => $cm->module?->price_monthly ?? 0);

        $premiumModules = Module::active()->premium()->ordered()->get();

        $invoices = PlatformInvoice::where('company_id', $this->companyId)
            ->orderByDesc('created_at')
            ->get();

        $outstandingOre = $invoices->filter(fn ($i) => ! $i->isPaid())->sum('amount');

        return view('livewire.admin.company-detail-manager', compact(
            'company',
            'companyModules',
            'totalMonthlyOre',
            'premiumModules',
            'invoices',
            'outstandingOre',
        ));
    }
}
