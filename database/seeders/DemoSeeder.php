<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use App\Models\Company;
use App\Models\InvoiceStatus;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\ProductGroup;
use App\Models\ProductType;
use App\Models\ProjectStatus;
use App\Models\ProjectType;
use App\Models\QuoteStatus;
use App\Models\ShareClass;
use App\Models\Unit;
use App\Models\User;
use App\Models\VatCode;
use App\Models\VatRate;
use App\Models\WorkOrderPriority;
use App\Models\WorkOrderStatus;
use App\Models\WorkOrderType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    private Company $company;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create demo admin user
        $user = User::firstOrCreate(
            ['email' => 'kh@gets.no'],
            [
                'name' => 'Knut W. Horne',
                'password' => Hash::make('prenneo3'),
                'is_admin' => true,
                'email_verified_at' => now(),
                'onboarding_completed' => true,
            ]
        );

        // 2. Create demo company
        $this->company = Company::firstOrCreate(
            ['organization_number' => '999888777'],
            [
                'name' => 'Demoselskap AS',
                'vat_number' => 'NO999888777MVA',
                'address' => 'Demogate 1',
                'postal_code' => '0123',
                'city' => 'Oslo',
                'country' => 'Norge',
                'phone' => '+47 22 33 44 55',
                'email' => 'post@demoselskap.no',
                'website' => 'https://demoselskap.no',
                'bank_name' => 'DNB',
                'bank_account' => '1234.56.78901',
                'default_payment_days' => 14,
                'default_quote_validity_days' => 30,
                'is_active' => true,
            ]
        );

        // 3. Attach user to company as owner (if not already attached)
        if (! $this->company->users()->where('user_id', $user->id)->exists()) {
            $this->company->users()->attach($user->id, [
                'role' => 'owner',
                'is_default' => true,
                'joined_at' => now(),
            ]);
        }

        // 4. Set user's current company
        $user->update(['current_company_id' => $this->company->id]);

        // 5. Bind company to container for auto company_id assignment
        app()->instance('current.company', $this->company);

        // 6. Seed all master data
        $this->seedVatRates();
        $this->seedVatCodes();
        $this->seedUnits();
        $this->seedProductTypes();
        $this->seedProductGroups();
        $this->seedProjectTypes();
        $this->seedProjectStatuses();
        $this->seedWorkOrderTypes();
        $this->seedWorkOrderStatuses();
        $this->seedWorkOrderPriorities();
        $this->seedQuoteStatuses();
        $this->seedOrderStatuses();
        $this->seedInvoiceStatuses();
        $this->seedPaymentMethods();
        $this->seedShareClasses();
        $this->seedActivityTypes();

        $this->command->info('Demo data seeded successfully for Demoselskap AS!');
    }

    private function seedVatRates(): void
    {
        $rates = [
            ['name' => 'Ingen moms', 'code' => 'MVA0', 'rate' => 0, 'description' => '0% moms - fritatt', 'is_default' => false, 'sort_order' => 1],
            ['name' => 'Lav sats', 'code' => 'MVA12', 'rate' => 12, 'description' => '12% moms - transport, kino', 'is_default' => false, 'sort_order' => 2],
            ['name' => 'Middels sats', 'code' => 'MVA15', 'rate' => 15, 'description' => '15% moms - mat og drikke', 'is_default' => false, 'sort_order' => 3],
            ['name' => 'Standard sats', 'code' => 'MVA25', 'rate' => 25, 'description' => '25% moms - standard sats', 'is_default' => true, 'sort_order' => 4],
        ];

        foreach ($rates as $rate) {
            VatRate::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $rate['code']],
                $rate
            );
        }
    }

    private function seedVatCodes(): void
    {
        $codes = [
            ['code' => '3', 'name' => 'Utgående mva høy sats', 'description' => 'Salg av varer og tjenester med 25% mva', 'category' => 'salg_norge', 'direction' => 'output', 'rate' => 25.00, 'affects_base' => true, 'affects_tax' => true, 'sign' => 1, 'sort_order' => 10],
            ['code' => '31', 'name' => 'Utgående mva middels sats', 'description' => 'Salg av næringsmidler med 15% mva', 'category' => 'salg_norge', 'direction' => 'output', 'rate' => 15.00, 'affects_base' => true, 'affects_tax' => true, 'sign' => 1, 'sort_order' => 20],
            ['code' => '33', 'name' => 'Utgående mva lav sats', 'description' => 'Salg av persontransport, kino, etc. med 12% mva', 'category' => 'salg_norge', 'direction' => 'output', 'rate' => 12.00, 'affects_base' => true, 'affects_tax' => true, 'sign' => 1, 'sort_order' => 30],
            ['code' => '5', 'name' => 'Utgående mva null sats', 'description' => 'Omsetning fritatt for mva (innenlands)', 'category' => 'salg_norge', 'direction' => 'output', 'rate' => 0.00, 'affects_base' => true, 'affects_tax' => false, 'sign' => 1, 'sort_order' => 40],
            ['code' => '1', 'name' => 'Inngående mva høy sats', 'description' => 'Fradrag for kjøp av varer og tjenester med 25% mva', 'category' => 'kjop_norge', 'direction' => 'input', 'rate' => 25.00, 'affects_base' => false, 'affects_tax' => true, 'sign' => -1, 'sort_order' => 100],
            ['code' => '11', 'name' => 'Inngående mva middels sats', 'description' => 'Fradrag for kjøp med 15% mva', 'category' => 'kjop_norge', 'direction' => 'input', 'rate' => 15.00, 'affects_base' => false, 'affects_tax' => true, 'sign' => -1, 'sort_order' => 110],
            ['code' => '13', 'name' => 'Inngående mva lav sats', 'description' => 'Fradrag for kjøp med 12% mva', 'category' => 'kjop_norge', 'direction' => 'input', 'rate' => 12.00, 'affects_base' => false, 'affects_tax' => true, 'sign' => -1, 'sort_order' => 120],
            ['code' => '52', 'name' => 'Utførsel av varer og tjenester', 'description' => 'Eksport - avgiftsfri omsetning', 'category' => 'export', 'direction' => 'output', 'rate' => 0.00, 'affects_base' => true, 'affects_tax' => false, 'sign' => 1, 'sort_order' => 300],
        ];

        foreach ($codes as $code) {
            VatCode::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $code['code']],
                $code
            );
        }
    }

    private function seedUnits(): void
    {
        $units = [
            ['name' => 'Stykk', 'code' => 'STK', 'symbol' => 'stk', 'description' => 'Enkelt enhet/stykk', 'sort_order' => 1],
            ['name' => 'Time', 'code' => 'TIME', 'symbol' => 't', 'description' => 'Time (60 minutter)', 'sort_order' => 2],
            ['name' => 'Dag', 'code' => 'DAG', 'symbol' => 'd', 'description' => 'Dag (24 timer)', 'sort_order' => 3],
            ['name' => 'Uke', 'code' => 'UKE', 'symbol' => 'u', 'description' => 'Uke (7 dager)', 'sort_order' => 4],
            ['name' => 'Måned', 'code' => 'MND', 'symbol' => 'mnd', 'description' => 'Måned', 'sort_order' => 5],
            ['name' => 'Kvartal', 'code' => 'KVARTAL', 'symbol' => 'kv', 'description' => 'Kvartal (3 måneder)', 'sort_order' => 6],
            ['name' => 'År', 'code' => 'AAR', 'symbol' => 'år', 'description' => 'År (12 måneder)', 'sort_order' => 7],
            ['name' => 'Kilometer', 'code' => 'KM', 'symbol' => 'km', 'description' => 'Kilometer', 'sort_order' => 8],
            ['name' => 'Kvadratmeter', 'code' => 'KVM', 'symbol' => 'm²', 'description' => 'Kvadratmeter', 'sort_order' => 9],
            ['name' => 'Liter', 'code' => 'LITER', 'symbol' => 'l', 'description' => 'Liter', 'sort_order' => 10],
            ['name' => 'Kilogram', 'code' => 'KG', 'symbol' => 'kg', 'description' => 'Kilogram', 'sort_order' => 11],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $unit['code']],
                $unit
            );
        }
    }

    private function seedProductTypes(): void
    {
        // Get the default VAT rate (25%)
        $defaultVatRate = VatRate::where('company_id', $this->company->id)
            ->where('code', 'MVA25')
            ->first();

        $types = [
            ['name' => 'Vare', 'code' => 'VARE', 'description' => 'Fysiske varer', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Tjeneste', 'code' => 'TJENESTE', 'description' => 'Tjenester og konsulentarbeid', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Timer', 'code' => 'TIMER', 'description' => 'Timebaserte tjenester', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Materiell', 'code' => 'MATERIELL', 'description' => 'Materiell og forbruksvarer', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'Reisekostnad', 'code' => 'REISE', 'description' => 'Reise- og diettutgifter', 'is_active' => true, 'sort_order' => 5],
        ];

        foreach ($types as $type) {
            ProductType::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $type['code']],
                array_merge($type, ['vat_rate_id' => $defaultVatRate?->id])
            );
        }
    }

    private function seedProductGroups(): void
    {
        $groups = [
            ['name' => 'Konsulenttjenester', 'code' => 'KONSULENT', 'description' => 'Rådgivning og konsulentarbeid', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Utviklingstjenester', 'code' => 'UTVIKLING', 'description' => 'Programvareutvikling', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Support', 'code' => 'SUPPORT', 'description' => 'Support og vedlikehold', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Materiell', 'code' => 'MATERIELL', 'description' => 'Fysisk materiell og utstyr', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'Lisenser', 'code' => 'LISENSER', 'description' => 'Programvarelisenser', 'is_active' => true, 'sort_order' => 5],
        ];

        foreach ($groups as $group) {
            ProductGroup::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $group['code']],
                $group
            );
        }
    }

    private function seedProjectTypes(): void
    {
        $types = [
            ['name' => 'Konsulentoppdrag', 'code' => 'CONSULTING', 'description' => 'Rådgivnings- og konsulentoppdrag', 'sort_order' => 1],
            ['name' => 'Utviklingsprosjekt', 'code' => 'DEVELOPMENT', 'description' => 'Programvareutvikling og tekniske prosjekter', 'sort_order' => 2],
            ['name' => 'Supportavtale', 'code' => 'SUPPORT', 'description' => 'Løpende support- og vedlikeholdsavtaler', 'sort_order' => 3],
            ['name' => 'Implementering', 'code' => 'IMPLEMENTATION', 'description' => 'Implementering og utrulling av systemer', 'sort_order' => 4],
            ['name' => 'Opplæring', 'code' => 'TRAINING', 'description' => 'Kurs og opplæringsprosjekter', 'sort_order' => 5],
        ];

        foreach ($types as $type) {
            ProjectType::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $type['code']],
                $type
            );
        }
    }

    private function seedProjectStatuses(): void
    {
        $statuses = [
            ['name' => 'Planlegging', 'code' => 'PLANNING', 'color' => 'blue', 'description' => 'Prosjektet er under planlegging', 'sort_order' => 1],
            ['name' => 'Pågår', 'code' => 'IN_PROGRESS', 'color' => 'yellow', 'description' => 'Prosjektet er aktivt og pågår', 'sort_order' => 2],
            ['name' => 'Fullført', 'code' => 'COMPLETED', 'color' => 'green', 'description' => 'Prosjektet er fullført', 'sort_order' => 3],
            ['name' => 'Pause', 'code' => 'ON_HOLD', 'color' => 'gray', 'description' => 'Prosjektet er satt på pause', 'sort_order' => 4],
            ['name' => 'Kansellert', 'code' => 'CANCELLED', 'color' => 'red', 'description' => 'Prosjektet er kansellert', 'sort_order' => 5],
        ];

        foreach ($statuses as $status) {
            ProjectStatus::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $status['code']],
                $status
            );
        }
    }

    private function seedWorkOrderTypes(): void
    {
        $types = [
            ['name' => 'Service', 'code' => 'SERVICE', 'description' => 'Generell service og vedlikehold', 'sort_order' => 1],
            ['name' => 'Reparasjon', 'code' => 'REPAIR', 'description' => 'Reparasjon av utstyr eller systemer', 'sort_order' => 2],
            ['name' => 'Installasjon', 'code' => 'INSTALLATION', 'description' => 'Installasjon av nytt utstyr eller systemer', 'sort_order' => 3],
            ['name' => 'Vedlikehold', 'code' => 'MAINTENANCE', 'description' => 'Planlagt vedlikehold og inspeksjon', 'sort_order' => 4],
            ['name' => 'Konsultasjon', 'code' => 'CONSULTATION', 'description' => 'Rådgivning og konsultasjonstjenester', 'sort_order' => 5],
        ];

        foreach ($types as $type) {
            WorkOrderType::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $type['code']],
                $type
            );
        }
    }

    private function seedWorkOrderStatuses(): void
    {
        $statuses = [
            ['name' => 'Ny', 'code' => 'NEW', 'color' => 'blue', 'sort_order' => 1],
            ['name' => 'Planlagt', 'code' => 'SCHEDULED', 'color' => 'indigo', 'sort_order' => 2],
            ['name' => 'Pågår', 'code' => 'IN_PROGRESS', 'color' => 'yellow', 'sort_order' => 3],
            ['name' => 'Venter', 'code' => 'ON_HOLD', 'color' => 'gray', 'sort_order' => 4],
            ['name' => 'Fullført', 'code' => 'COMPLETED', 'color' => 'green', 'sort_order' => 5],
            ['name' => 'Godkjent', 'code' => 'APPROVED', 'color' => 'emerald', 'sort_order' => 6],
            ['name' => 'Fakturert', 'code' => 'INVOICED', 'color' => 'purple', 'sort_order' => 7],
            ['name' => 'Kansellert', 'code' => 'CANCELLED', 'color' => 'red', 'sort_order' => 8],
        ];

        foreach ($statuses as $status) {
            WorkOrderStatus::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $status['code']],
                $status
            );
        }
    }

    private function seedWorkOrderPriorities(): void
    {
        $priorities = [
            ['name' => 'Lav', 'code' => 'LOW', 'color' => 'gray', 'sort_order' => 1],
            ['name' => 'Normal', 'code' => 'NORMAL', 'color' => 'blue', 'sort_order' => 2],
            ['name' => 'Høy', 'code' => 'HIGH', 'color' => 'yellow', 'sort_order' => 3],
            ['name' => 'Kritisk', 'code' => 'CRITICAL', 'color' => 'red', 'sort_order' => 4],
        ];

        foreach ($priorities as $priority) {
            WorkOrderPriority::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $priority['code']],
                $priority
            );
        }
    }

    private function seedQuoteStatuses(): void
    {
        $statuses = [
            ['name' => 'Utkast', 'code' => 'draft', 'color' => 'zinc', 'sort_order' => 1],
            ['name' => 'Sendt', 'code' => 'sent', 'color' => 'blue', 'sort_order' => 2],
            ['name' => 'Akseptert', 'code' => 'accepted', 'color' => 'green', 'sort_order' => 3],
            ['name' => 'Avslått', 'code' => 'rejected', 'color' => 'red', 'sort_order' => 4],
            ['name' => 'Utløpt', 'code' => 'expired', 'color' => 'amber', 'sort_order' => 5],
            ['name' => 'Konvertert', 'code' => 'converted', 'color' => 'purple', 'sort_order' => 6],
        ];

        foreach ($statuses as $status) {
            QuoteStatus::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $status['code']],
                $status
            );
        }
    }

    private function seedOrderStatuses(): void
    {
        $statuses = [
            ['name' => 'Utkast', 'code' => 'draft', 'color' => 'zinc', 'sort_order' => 1],
            ['name' => 'Bekreftet', 'code' => 'confirmed', 'color' => 'blue', 'sort_order' => 2],
            ['name' => 'Under arbeid', 'code' => 'in_progress', 'color' => 'yellow', 'sort_order' => 3],
            ['name' => 'Fullført', 'code' => 'completed', 'color' => 'green', 'sort_order' => 4],
            ['name' => 'Kansellert', 'code' => 'cancelled', 'color' => 'red', 'sort_order' => 5],
            ['name' => 'Fakturert', 'code' => 'invoiced', 'color' => 'purple', 'sort_order' => 6],
        ];

        foreach ($statuses as $status) {
            OrderStatus::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $status['code']],
                $status
            );
        }
    }

    private function seedInvoiceStatuses(): void
    {
        $statuses = [
            ['name' => 'Utkast', 'code' => 'draft', 'color' => 'zinc', 'sort_order' => 1],
            ['name' => 'Sendt', 'code' => 'sent', 'color' => 'blue', 'sort_order' => 2],
            ['name' => 'Delvis betalt', 'code' => 'partially_paid', 'color' => 'yellow', 'sort_order' => 3],
            ['name' => 'Betalt', 'code' => 'paid', 'color' => 'green', 'sort_order' => 4],
            ['name' => 'Forfalt', 'code' => 'overdue', 'color' => 'red', 'sort_order' => 5],
            ['name' => 'Kreditert', 'code' => 'credited', 'color' => 'purple', 'sort_order' => 6],
        ];

        foreach ($statuses as $status) {
            InvoiceStatus::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $status['code']],
                $status
            );
        }
    }

    private function seedPaymentMethods(): void
    {
        $methods = [
            ['name' => 'Bankoverføring', 'code' => 'bank_transfer', 'sort_order' => 1],
            ['name' => 'Kort', 'code' => 'card', 'sort_order' => 2],
            ['name' => 'Kontant', 'code' => 'cash', 'sort_order' => 3],
            ['name' => 'Vipps', 'code' => 'vipps', 'sort_order' => 4],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $method['code']],
                $method
            );
        }
    }

    private function seedShareClasses(): void
    {
        $classes = [
            [
                'name' => 'Ordinære aksjer',
                'code' => 'A',
                'par_value' => 100.00,
                'total_shares' => 1000,
                'has_voting_rights' => true,
                'has_dividend_rights' => true,
                'voting_weight' => 1.00,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'B-aksjer',
                'code' => 'B',
                'par_value' => 100.00,
                'total_shares' => 0,
                'has_voting_rights' => false,
                'has_dividend_rights' => true,
                'voting_weight' => 0.00,
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($classes as $class) {
            ShareClass::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $class['code']],
                $class
            );
        }
    }

    private function seedActivityTypes(): void
    {
        $types = [
            ['name' => 'Telefon inn', 'icon' => 'phone-arrow-down-left', 'color' => 'green', 'description' => 'Innkommende telefonsamtale', 'sort_order' => 1],
            ['name' => 'Telefon ut', 'icon' => 'phone-arrow-up-right', 'color' => 'blue', 'description' => 'Utgående telefonsamtale', 'sort_order' => 2],
            ['name' => 'E-post inn', 'icon' => 'inbox-arrow-down', 'color' => 'green', 'description' => 'Innkommende e-post', 'sort_order' => 3],
            ['name' => 'E-post ut', 'icon' => 'paper-airplane', 'color' => 'blue', 'description' => 'Utgående e-post', 'sort_order' => 4],
            ['name' => 'Møte', 'icon' => 'calendar', 'color' => 'purple', 'description' => 'Fysisk møte med kontakt', 'sort_order' => 5],
            ['name' => 'Videomøte', 'icon' => 'video-camera', 'color' => 'indigo', 'description' => 'Videomøte (Teams, Zoom, etc.)', 'sort_order' => 6],
            ['name' => 'Besøk', 'icon' => 'map-pin', 'color' => 'amber', 'description' => 'Kundebesøk', 'sort_order' => 7],
            ['name' => 'Oppgave', 'icon' => 'clipboard-document-check', 'color' => 'cyan', 'description' => 'Intern oppgave relatert til kontakt', 'sort_order' => 8],
            ['name' => 'Notat', 'icon' => 'document-text', 'color' => 'zinc', 'description' => 'Generelt notat', 'sort_order' => 9],
        ];

        foreach ($types as $type) {
            ActivityType::updateOrCreate(
                ['company_id' => $this->company->id, 'name' => $type['name']],
                $type
            );
        }
    }
}
