<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Contact;
use App\Models\ContactPerson;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoiceStatus;
use App\Models\Module;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductType;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ProjectType;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\QuoteStatus;
use App\Models\ShareClass;
use App\Models\Shareholder;
use App\Models\Shareholding;
use App\Models\StockLevel;
use App\Models\StockLocation;
use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use App\Models\Unit;
use App\Models\User;
use App\Models\VatCode;
use App\Models\VatRate;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use App\Models\WorkOrderPriority;
use App\Models\WorkOrderStatus;
use App\Models\WorkOrderType;
use App\Services\ModuleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    private Company $company;

    private User $user;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create demo admin user
        $this->user = User::firstOrCreate(
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
        if (! $this->company->users()->where('user_id', $this->user->id)->exists()) {
            $this->company->users()->attach($this->user->id, [
                'role' => 'owner',
                'is_default' => true,
                'joined_at' => now(),
            ]);
        }

        // 4. Set user's current company
        $this->user->update(['current_company_id' => $this->company->id]);

        // 5. Bind company to container for auto company_id assignment
        app()->instance('current.company', $this->company);

        // 6. Enable ALL modules for demo company
        $this->enableAllModules();

        // 7. Seed all master data
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

        // 8. Seed demo transaction data
        $this->seedContacts();
        $this->seedProducts();
        $this->seedProjects();
        $this->seedWorkOrders();
        $this->seedQuotes();
        $this->seedOrders();
        $this->seedInvoices();
        $this->seedContracts();
        $this->seedAssets();
        $this->seedShareholders();
        $this->seedStockLocations();
        $this->seedActivities();

        $this->command->info('Demo data seeded successfully for Demoselskap AS!');
        $this->command->info('Login: kh@gets.no / prenneo3');
    }

    private function enableAllModules(): void
    {
        $moduleService = app(ModuleService::class);
        $modules = Module::all();

        foreach ($modules as $module) {
            $moduleService->enableForCompany($this->company, $module, 'admin');
        }

        $this->command->info('Enabled '.count($modules).' modules for demo company');
    }

    private function seedContacts(): void
    {
        $contacts = [
            [
                'type' => 'customer',
                'company_name' => 'Norsk Teknologi AS',
                'organization_number' => '912345678',
                'email' => 'post@norskteknologi.no',
                'phone' => '+47 21 00 00 01',
                'address' => 'Teknologiveien 1',
                'postal_code' => '0251',
                'city' => 'Oslo',
                'payment_terms_days' => 14,
                'persons' => [
                    ['name' => 'Ola Nordmann', 'email' => 'ola@norskteknologi.no', 'phone' => '+47 900 00 001', 'title' => 'Daglig leder'],
                    ['name' => 'Kari Hansen', 'email' => 'kari@norskteknologi.no', 'phone' => '+47 900 00 002', 'title' => 'Innkjøpssjef'],
                ],
            ],
            [
                'type' => 'customer',
                'company_name' => 'Bergen Bygg & Anlegg AS',
                'organization_number' => '923456789',
                'email' => 'post@bergenbygg.no',
                'phone' => '+47 55 00 00 01',
                'address' => 'Byggveien 10',
                'postal_code' => '5003',
                'city' => 'Bergen',
                'payment_terms_days' => 30,
                'persons' => [
                    ['name' => 'Per Berge', 'email' => 'per@bergenbygg.no', 'phone' => '+47 901 00 001', 'title' => 'Prosjektleder'],
                ],
            ],
            [
                'type' => 'customer',
                'company_name' => 'Trondheim Industri AS',
                'organization_number' => '934567890',
                'email' => 'kontakt@trondheimind.no',
                'phone' => '+47 73 00 00 01',
                'address' => 'Industrigata 25',
                'postal_code' => '7030',
                'city' => 'Trondheim',
                'payment_terms_days' => 21,
                'persons' => [
                    ['name' => 'Lars Olsen', 'email' => 'lars@trondheimind.no', 'phone' => '+47 902 00 001', 'title' => 'Driftsleder'],
                ],
            ],
            [
                'type' => 'supplier',
                'company_name' => 'Materialleveransen AS',
                'organization_number' => '945678901',
                'email' => 'ordre@materialleveransen.no',
                'phone' => '+47 22 11 00 01',
                'address' => 'Lagerveien 50',
                'postal_code' => '0581',
                'city' => 'Oslo',
                'payment_terms_days' => 30,
                'persons' => [
                    ['name' => 'Eva Lager', 'email' => 'eva@materialleveransen.no', 'phone' => '+47 903 00 001', 'title' => 'Salgssjef'],
                ],
            ],
            [
                'type' => 'supplier',
                'company_name' => 'IT Utstyr Norge AS',
                'organization_number' => '956789012',
                'email' => 'salg@itutstyr.no',
                'phone' => '+47 22 22 00 01',
                'address' => 'Digitalveien 8',
                'postal_code' => '0454',
                'city' => 'Oslo',
                'payment_terms_days' => 14,
                'persons' => [
                    ['name' => 'Morten IT', 'email' => 'morten@itutstyr.no', 'phone' => '+47 904 00 001', 'title' => 'Key Account Manager'],
                ],
            ],
        ];

        foreach ($contacts as $contactData) {
            $persons = $contactData['persons'] ?? [];
            unset($contactData['persons']);

            $contact = Contact::where('company_id', $this->company->id)
                ->where('organization_number', $contactData['organization_number'])
                ->first();

            if (! $contact) {
                // Generate unique contact number manually to avoid conflicts
                $maxNumber = (int) DB::table('contacts')
                    ->whereYear('created_at', date('Y'))
                    ->selectRaw('MAX(CAST(SUBSTRING(contact_number, 8) AS UNSIGNED)) as max_num')
                    ->value('max_num') ?? 0;

                $contact = Contact::create(array_merge($contactData, [
                    'company_id' => $this->company->id,
                    'country' => 'Norge',
                    'status' => 'active',
                    'is_active' => true,
                    'created_by' => $this->user->id,
                    'contact_number' => 'CON'.date('Y').str_pad($maxNumber + 1, 4, '0', STR_PAD_LEFT),
                ]));
            }

            foreach ($persons as $person) {
                ContactPerson::updateOrCreate(
                    ['contact_id' => $contact->id, 'email' => $person['email']],
                    array_merge($person, ['is_primary' => false])
                );
            }
        }

        $this->command->info('Created '.count($contacts).' demo contacts');
    }

    private function seedProducts(): void
    {
        $unit = Unit::where('company_id', $this->company->id)->where('code', 'TIME')->first();
        $unitStk = Unit::where('company_id', $this->company->id)->where('code', 'STK')->first();
        $productType = ProductType::where('company_id', $this->company->id)->where('code', 'TJENESTE')->first();
        $productTypeVare = ProductType::where('company_id', $this->company->id)->where('code', 'VARE')->first();
        $groupKonsulent = ProductGroup::where('company_id', $this->company->id)->where('code', 'KONSULENT')->first();
        $groupMateriell = ProductGroup::where('company_id', $this->company->id)->where('code', 'MATERIELL')->first();

        $products = [
            ['name' => 'Konsulenttjenester Senior', 'sku' => 'KONS-SR', 'price' => 1650, 'cost_price' => 800, 'unit_id' => $unit?->id, 'product_type_id' => $productType?->id, 'product_group_id' => $groupKonsulent?->id],
            ['name' => 'Konsulenttjenester Junior', 'sku' => 'KONS-JR', 'price' => 1150, 'cost_price' => 550, 'unit_id' => $unit?->id, 'product_type_id' => $productType?->id, 'product_group_id' => $groupKonsulent?->id],
            ['name' => 'Prosjektledelse', 'sku' => 'PROJ-LED', 'price' => 1850, 'cost_price' => 950, 'unit_id' => $unit?->id, 'product_type_id' => $productType?->id, 'product_group_id' => $groupKonsulent?->id],
            ['name' => 'Systemutvikling', 'sku' => 'SYS-DEV', 'price' => 1450, 'cost_price' => 700, 'unit_id' => $unit?->id, 'product_type_id' => $productType?->id, 'product_group_id' => $groupKonsulent?->id],
            ['name' => 'Supporttjenester', 'sku' => 'SUPPORT', 'price' => 950, 'cost_price' => 450, 'unit_id' => $unit?->id, 'product_type_id' => $productType?->id, 'product_group_id' => $groupKonsulent?->id],
            ['name' => 'Nettverkskabel CAT6', 'sku' => 'KABEL-CAT6', 'price' => 89, 'cost_price' => 35, 'unit_id' => $unitStk?->id, 'product_type_id' => $productTypeVare?->id, 'product_group_id' => $groupMateriell?->id],
            ['name' => 'USB-C Adapter', 'sku' => 'USB-C-ADP', 'price' => 249, 'cost_price' => 95, 'unit_id' => $unitStk?->id, 'product_type_id' => $productTypeVare?->id, 'product_group_id' => $groupMateriell?->id],
            ['name' => 'HDMI Kabel 2m', 'sku' => 'HDMI-2M', 'price' => 149, 'cost_price' => 55, 'unit_id' => $unitStk?->id, 'product_type_id' => $productTypeVare?->id, 'product_group_id' => $groupMateriell?->id],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['company_id' => $this->company->id, 'sku' => $product['sku']],
                array_merge($product, ['is_active' => true, 'sort_order' => 0])
            );
        }

        $this->command->info('Created '.count($products).' demo products');
    }

    private function seedProjects(): void
    {
        $contact = Contact::where('company_id', $this->company->id)->where('type', 'customer')->first();
        $projectType = ProjectType::where('company_id', $this->company->id)->first();
        $statusInProgress = ProjectStatus::where('company_id', $this->company->id)->where('code', 'IN_PROGRESS')->first();
        $statusPlanning = ProjectStatus::where('company_id', $this->company->id)->where('code', 'PLANNING')->first();
        $statusCompleted = ProjectStatus::where('company_id', $this->company->id)->where('code', 'COMPLETED')->first();

        $projects = [
            ['name' => 'Systemintegrasjon 2024', 'description' => 'Integrasjon av nytt ERP-system', 'budget' => 450000, 'estimated_hours' => 300, 'status' => $statusInProgress],
            ['name' => 'Nettverksoppgradering', 'description' => 'Oppgradering av hele nettverksinfrastrukturen', 'budget' => 180000, 'estimated_hours' => 120, 'status' => $statusPlanning],
            ['name' => 'Skymigrering', 'description' => 'Migrering av tjenester til Azure', 'budget' => 320000, 'estimated_hours' => 200, 'status' => $statusInProgress],
            ['name' => 'Sikkerhetsprojekt Q1', 'description' => 'Implementering av nye sikkerhetsrutiner', 'budget' => 95000, 'estimated_hours' => 60, 'status' => $statusCompleted],
        ];

        foreach ($projects as $projectData) {
            $status = $projectData['status'];
            unset($projectData['status']);

            $project = Project::where('company_id', $this->company->id)
                ->where('name', $projectData['name'])
                ->first();

            if (! $project) {
                // Generate unique project number
                $maxNumber = (int) DB::table('projects')
                    ->whereYear('created_at', date('Y'))
                    ->selectRaw('MAX(CAST(SUBSTRING(project_number, 8) AS UNSIGNED)) as max_num')
                    ->value('max_num') ?? 0;

                $project = Project::create(array_merge($projectData, [
                    'company_id' => $this->company->id,
                    'contact_id' => $contact?->id,
                    'project_type_id' => $projectType?->id,
                    'project_status_id' => $status?->id,
                    'manager_id' => $this->user->id,
                    'start_date' => now()->subMonths(2),
                    'end_date' => now()->addMonths(4),
                    'is_active' => true,
                    'project_number' => 'P-'.date('Y').'-'.str_pad($maxNumber + 1, 4, '0', STR_PAD_LEFT),
                ]));
            }

            // Add timesheet entries for active projects
            if ($status?->code === 'IN_PROGRESS') {
                $this->seedTimesheetForProject($project);
            }
        }

        $this->command->info('Created '.count($projects).' demo projects with timesheets');
    }

    private function seedTimesheetForProject(Project $project): void
    {
        // Create timesheet for current week
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $timesheet = Timesheet::firstOrCreate(
            [
                'company_id' => $this->company->id,
                'user_id' => $this->user->id,
                'week_start' => $weekStart,
            ],
            [
                'week_end' => $weekEnd,
                'status' => 'draft',
                'notes' => 'Demo timesheet',
            ]
        );

        // Add entries for the week
        for ($i = 0; $i < 5; $i++) {
            $date = $weekStart->copy()->addDays($i);
            TimesheetEntry::updateOrCreate(
                [
                    'timesheet_id' => $timesheet->id,
                    'entry_date' => $date,
                    'project_id' => $project->id,
                ],
                [
                    'hours' => fake()->randomFloat(1, 4, 8),
                    'description' => fake()->sentence(4),
                    'is_billable' => true,
                ]
            );
        }
    }

    private function seedWorkOrders(): void
    {
        $contact = Contact::where('company_id', $this->company->id)->where('type', 'customer')->first();
        $type = WorkOrderType::where('company_id', $this->company->id)->first();
        $statusNew = WorkOrderStatus::where('company_id', $this->company->id)->where('code', 'NEW')->first();
        $statusInProgress = WorkOrderStatus::where('company_id', $this->company->id)->where('code', 'IN_PROGRESS')->first();
        $statusCompleted = WorkOrderStatus::where('company_id', $this->company->id)->where('code', 'COMPLETED')->first();
        $priorityNormal = WorkOrderPriority::where('company_id', $this->company->id)->where('code', 'NORMAL')->first();
        $priorityHigh = WorkOrderPriority::where('company_id', $this->company->id)->where('code', 'HIGH')->first();

        $workOrders = [
            ['title' => 'Servervedlikehold', 'description' => 'Planlagt vedlikehold av produksjonsservere', 'status' => $statusNew, 'priority' => $priorityNormal, 'estimated_hours' => 4],
            ['title' => 'Nettverksfeil hos kunde', 'description' => 'Ustabil nettverksforbindelse rapportert', 'status' => $statusInProgress, 'priority' => $priorityHigh, 'estimated_hours' => 8],
            ['title' => 'PC-installasjon', 'description' => 'Installasjon av 5 nye arbeidsstasjoner', 'status' => $statusNew, 'priority' => $priorityNormal, 'estimated_hours' => 10],
            ['title' => 'Backup-verifisering', 'description' => 'Månedlig sjekk av backup-rutiner', 'status' => $statusCompleted, 'priority' => $priorityNormal, 'estimated_hours' => 2],
            ['title' => 'Printerproblemer', 'description' => 'Nettverksprinter fungerer ikke', 'status' => $statusInProgress, 'priority' => $priorityNormal, 'estimated_hours' => 2],
        ];

        $product = Product::where('company_id', $this->company->id)->where('sku', 'SUPPORT')->first();

        foreach ($workOrders as $woData) {
            $status = $woData['status'];
            $priority = $woData['priority'];
            unset($woData['status'], $woData['priority']);

            $workOrder = WorkOrder::where('company_id', $this->company->id)
                ->where('title', $woData['title'])
                ->first();

            if (! $workOrder) {
                // Generate unique work order number
                $maxNumber = (int) DB::table('work_orders')
                    ->whereYear('created_at', date('Y'))
                    ->selectRaw('MAX(CAST(SUBSTRING(work_order_number, 9) AS UNSIGNED)) as max_num')
                    ->value('max_num') ?? 0;

                $workOrder = WorkOrder::create(array_merge($woData, [
                    'company_id' => $this->company->id,
                    'contact_id' => $contact?->id,
                    'work_order_type_id' => $type?->id,
                    'work_order_status_id' => $status?->id,
                    'work_order_priority_id' => $priority?->id,
                    'assigned_to' => $this->user->id,
                    'created_by' => $this->user->id,
                    'scheduled_date' => now()->addDays(fake()->numberBetween(-5, 10)),
                    'due_date' => now()->addDays(fake()->numberBetween(5, 20)),
                    'is_active' => true,
                    'work_order_number' => 'WO-'.date('Y').'-'.str_pad($maxNumber + 1, 4, '0', STR_PAD_LEFT),
                ]));
            }

            // Add work order line
            if ($product) {
                WorkOrderLine::updateOrCreate(
                    ['work_order_id' => $workOrder->id, 'product_id' => $product->id],
                    [
                        'line_type' => 'product',
                        'description' => $product->name,
                        'quantity' => $woData['estimated_hours'],
                        'unit_price' => $product->price,
                        'discount_percent' => 0,
                        'sort_order' => 1,
                    ]
                );
            }
        }

        $this->command->info('Created '.count($workOrders).' demo work orders');
    }

    private function seedQuotes(): void
    {
        $contacts = Contact::where('company_id', $this->company->id)->where('type', 'customer')->take(3)->get();
        $statusDraft = QuoteStatus::where('company_id', $this->company->id)->where('code', 'draft')->first();
        $statusSent = QuoteStatus::where('company_id', $this->company->id)->where('code', 'sent')->first();
        $statusAccepted = QuoteStatus::where('company_id', $this->company->id)->where('code', 'accepted')->first();
        $products = Product::where('company_id', $this->company->id)->take(3)->get();
        $vatRate25 = VatRate::where('company_id', $this->company->id)->where('rate', 25)->first();

        $quotes = [
            ['title' => 'Tilbud systemutvikling', 'status' => $statusSent],
            ['title' => 'Tilbud konsulenttjenester Q1', 'status' => $statusDraft],
            ['title' => 'Tilbud nettverksoppgradering', 'status' => $statusAccepted],
        ];

        foreach ($quotes as $index => $quoteData) {
            $contact = $contacts[$index % count($contacts)] ?? $contacts->first();
            $status = $quoteData['status'];

            $quote = Quote::where('company_id', $this->company->id)
                ->where('title', $quoteData['title'])
                ->first();

            if (! $quote) {
                $maxNumber = (int) DB::table('quotes')
                    ->whereYear('created_at', date('Y'))
                    ->selectRaw('MAX(CAST(SUBSTRING(quote_number, 8) AS UNSIGNED)) as max_num')
                    ->value('max_num') ?? 0;

                $quote = Quote::create([
                    'company_id' => $this->company->id,
                    'title' => $quoteData['title'],
                    'quote_number' => 'T-'.date('Y').'-'.str_pad($maxNumber + 1, 4, '0', STR_PAD_LEFT),
                    'description' => fake()->paragraph(),
                    'contact_id' => $contact?->id,
                    'quote_status_id' => $status?->id,
                    'created_by' => $this->user->id,
                    'quote_date' => now()->subDays(fake()->numberBetween(1, 14)),
                    'valid_until' => now()->addDays(30),
                    'payment_terms_days' => $contact?->payment_terms_days ?? 14,
                    'customer_name' => $contact?->company_name,
                    'customer_address' => $contact?->address,
                    'customer_postal_code' => $contact?->postal_code,
                    'customer_city' => $contact?->city,
                    'customer_country' => 'Norge',
                    'subtotal' => 0,
                    'discount_total' => 0,
                    'vat_total' => 0,
                    'total' => 0,
                    'is_active' => true,
                ]);
            }

            // Add lines
            $subtotal = 0;
            foreach ($products->take(2) as $sortOrder => $product) {
                $quantity = fake()->numberBetween(5, 20);
                $lineTotal = $quantity * $product->price;
                $subtotal += $lineTotal;

                QuoteLine::updateOrCreate(
                    ['quote_id' => $quote->id, 'product_id' => $product->id],
                    [
                        'description' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'discount_percent' => 0,
                        'vat_rate_id' => $vatRate25?->id,
                        'vat_percent' => 25,
                        'sort_order' => $sortOrder + 1,
                    ]
                );
            }

            $vatTotal = $subtotal * 0.25;
            $quote->update([
                'subtotal' => $subtotal,
                'vat_total' => $vatTotal,
                'total' => $subtotal + $vatTotal,
            ]);
        }

        $this->command->info('Created '.count($quotes).' demo quotes');
    }

    private function seedOrders(): void
    {
        $contact = Contact::where('company_id', $this->company->id)->where('type', 'customer')->first();
        $statusConfirmed = OrderStatus::where('company_id', $this->company->id)->where('code', 'confirmed')->first();
        $statusInProgress = OrderStatus::where('company_id', $this->company->id)->where('code', 'in_progress')->first();
        $products = Product::where('company_id', $this->company->id)->take(2)->get();
        $vatRate25 = VatRate::where('company_id', $this->company->id)->where('rate', 25)->first();

        $orders = [
            ['title' => 'Ordre - Konsulenttjenester', 'status' => $statusInProgress],
            ['title' => 'Ordre - IT-utstyr', 'status' => $statusConfirmed],
        ];

        foreach ($orders as $orderData) {
            $status = $orderData['status'];

            $order = Order::where('company_id', $this->company->id)
                ->where('title', $orderData['title'])
                ->first();

            if (! $order) {
                $maxNumber = (int) DB::table('orders')
                    ->whereYear('created_at', date('Y'))
                    ->selectRaw('MAX(CAST(SUBSTRING(order_number, 8) AS UNSIGNED)) as max_num')
                    ->value('max_num') ?? 0;

                $order = Order::create([
                    'company_id' => $this->company->id,
                    'title' => $orderData['title'],
                    'order_number' => 'O-'.date('Y').'-'.str_pad($maxNumber + 1, 4, '0', STR_PAD_LEFT),
                    'contact_id' => $contact?->id,
                    'order_status_id' => $status?->id,
                    'created_by' => $this->user->id,
                    'order_date' => now()->subDays(fake()->numberBetween(1, 7)),
                    'delivery_date' => now()->addDays(fake()->numberBetween(7, 30)),
                    'payment_terms_days' => $contact?->payment_terms_days ?? 14,
                    'customer_name' => $contact?->company_name,
                    'customer_address' => $contact?->address,
                    'customer_postal_code' => $contact?->postal_code,
                    'customer_city' => $contact?->city,
                    'customer_country' => 'Norge',
                    'subtotal' => 0,
                    'discount_total' => 0,
                    'vat_total' => 0,
                    'total' => 0,
                    'is_active' => true,
                ]);
            }

            $subtotal = 0;
            foreach ($products as $sortOrder => $product) {
                $quantity = fake()->numberBetween(2, 10);
                $lineTotal = $quantity * $product->price;
                $subtotal += $lineTotal;

                OrderLine::updateOrCreate(
                    ['order_id' => $order->id, 'product_id' => $product->id],
                    [
                        'description' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'discount_percent' => 0,
                        'vat_rate_id' => $vatRate25?->id,
                        'vat_percent' => 25,
                        'sort_order' => $sortOrder + 1,
                    ]
                );
            }

            $vatTotal = $subtotal * 0.25;
            $order->update([
                'subtotal' => $subtotal,
                'vat_total' => $vatTotal,
                'total' => $subtotal + $vatTotal,
            ]);
        }

        $this->command->info('Created '.count($orders).' demo orders');
    }

    private function seedInvoices(): void
    {
        $contact = Contact::where('company_id', $this->company->id)->where('type', 'customer')->first();
        $statusSent = InvoiceStatus::where('company_id', $this->company->id)->where('code', 'sent')->first();
        $statusPaid = InvoiceStatus::where('company_id', $this->company->id)->where('code', 'paid')->first();
        $statusDraft = InvoiceStatus::where('company_id', $this->company->id)->where('code', 'draft')->first();
        $products = Product::where('company_id', $this->company->id)->take(2)->get();
        $vatRate25 = VatRate::where('company_id', $this->company->id)->where('rate', 25)->first();

        $invoices = [
            ['title' => 'Faktura - Konsulentarbeid januar', 'status' => $statusPaid, 'days_ago' => 45],
            ['title' => 'Faktura - Prosjektarbeid februar', 'status' => $statusSent, 'days_ago' => 14],
            ['title' => 'Faktura - Supporttimer mars', 'status' => $statusDraft, 'days_ago' => 2],
        ];

        foreach ($invoices as $invoiceData) {
            $status = $invoiceData['status'];
            $daysAgo = $invoiceData['days_ago'];

            $invoiceDate = now()->subDays($daysAgo);
            $dueDate = $invoiceDate->copy()->addDays($contact?->payment_terms_days ?? 14);

            $invoice = Invoice::where('company_id', $this->company->id)
                ->where('title', $invoiceData['title'])
                ->first();

            if (! $invoice) {
                $maxNumber = (int) DB::table('invoices')
                    ->whereYear('created_at', date('Y'))
                    ->selectRaw('MAX(CAST(SUBSTRING(invoice_number, 8) AS UNSIGNED)) as max_num')
                    ->value('max_num') ?? 0;

                $invoice = Invoice::create([
                    'company_id' => $this->company->id,
                    'title' => $invoiceData['title'],
                    'invoice_number' => 'F-'.date('Y').'-'.str_pad($maxNumber + 1, 4, '0', STR_PAD_LEFT),
                    'contact_id' => $contact?->id,
                    'invoice_status_id' => $status?->id,
                    'created_by' => $this->user->id,
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'payment_terms_days' => $contact?->payment_terms_days ?? 14,
                    'customer_name' => $contact?->company_name,
                    'customer_address' => $contact?->address,
                    'customer_postal_code' => $contact?->postal_code,
                    'customer_city' => $contact?->city,
                    'customer_country' => 'Norge',
                    'subtotal' => 0,
                    'discount_total' => 0,
                    'vat_total' => 0,
                    'total' => 0,
                    'is_active' => true,
                ]);
            }

            $subtotal = 0;
            foreach ($products as $sortOrder => $product) {
                $quantity = fake()->numberBetween(10, 40);
                $lineTotal = $quantity * $product->price;
                $subtotal += $lineTotal;

                InvoiceLine::updateOrCreate(
                    ['invoice_id' => $invoice->id, 'product_id' => $product->id],
                    [
                        'description' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'discount_percent' => 0,
                        'vat_rate_id' => $vatRate25?->id,
                        'vat_percent' => 25,
                        'sort_order' => $sortOrder + 1,
                    ]
                );
            }

            $vatTotal = $subtotal * 0.25;
            $invoice->update([
                'subtotal' => $subtotal,
                'vat_total' => $vatTotal,
                'total' => $subtotal + $vatTotal,
            ]);
        }

        $this->command->info('Created '.count($invoices).' demo invoices');
    }

    private function seedContracts(): void
    {
        $contracts = [
            [
                'title' => 'Serviceavtale - Norsk Teknologi AS',
                'company_name' => 'Norsk Teknologi AS',
                'type' => 'service',
                'status' => 'active',
                'value' => 120000,
                'payment_frequency' => 'monthly',
            ],
            [
                'title' => 'Leieavtale - Kontormaskiner',
                'company_name' => 'Office Rental AS',
                'type' => 'lease',
                'status' => 'active',
                'value' => 48000,
                'payment_frequency' => 'quarterly',
            ],
            [
                'title' => 'Programvarelisens - Microsoft 365',
                'company_name' => 'Microsoft Norge AS',
                'type' => 'software',
                'status' => 'active',
                'value' => 36000,
                'payment_frequency' => 'yearly',
            ],
            [
                'title' => 'Forsikring - Bedriftsforsikring',
                'company_name' => 'If Skadeforsikring',
                'type' => 'insurance',
                'status' => 'active',
                'value' => 85000,
                'payment_frequency' => 'yearly',
            ],
            [
                'title' => 'Vedlikeholdsavtale - Serverrom',
                'company_name' => 'Cooling Systems AS',
                'type' => 'maintenance',
                'status' => 'expiring_soon',
                'value' => 24000,
                'payment_frequency' => 'quarterly',
            ],
        ];

        foreach ($contracts as $contractData) {
            $startDate = now()->subMonths(fake()->numberBetween(6, 24));
            $durationMonths = fake()->randomElement([12, 24, 36]);
            $endDate = $startDate->copy()->addMonths($durationMonths);
            $noticePeriodDays = 90;

            Contract::updateOrCreate(
                ['company_id' => $this->company->id, 'title' => $contractData['title']],
                array_merge($contractData, [
                    'description' => fake()->paragraph(),
                    'established_date' => $startDate,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'duration_months' => $durationMonths,
                    'notice_period_days' => $noticePeriodDays,
                    'notice_date' => $endDate->copy()->subDays($noticePeriodDays),
                    'company_contact' => fake()->name(),
                    'company_email' => fake()->companyEmail(),
                    'company_phone' => fake()->phoneNumber(),
                    'currency' => 'NOK',
                    'auto_renewal' => true,
                    'renewal_period_months' => 12,
                    'created_by' => $this->user->id,
                    'responsible_user_id' => $this->user->id,
                ])
            );
        }

        $this->command->info('Created '.count($contracts).' demo contracts');
    }

    private function seedAssets(): void
    {
        $assets = [
            ['title' => 'MacBook Pro 14" M3', 'serial_number' => 'MBP-2024-001', 'manufacturer' => 'Apple', 'purchase_price' => 32990, 'location' => 'Kontor Oslo', 'department' => 'IT', 'status' => 'in_use'],
            ['title' => 'MacBook Pro 14" M3', 'serial_number' => 'MBP-2024-002', 'manufacturer' => 'Apple', 'purchase_price' => 32990, 'location' => 'Kontor Oslo', 'department' => 'Salg', 'status' => 'in_use'],
            ['title' => 'Dell XPS 15', 'serial_number' => 'DELL-XPS-001', 'manufacturer' => 'Dell', 'purchase_price' => 18990, 'location' => 'Kontor Oslo', 'department' => 'Økonomi', 'status' => 'in_use'],
            ['title' => 'iPhone 15 Pro', 'serial_number' => 'IPH15-001', 'manufacturer' => 'Apple', 'purchase_price' => 14990, 'location' => 'Kontor Oslo', 'department' => 'Ledelse', 'status' => 'in_use'],
            ['title' => 'iPhone 15 Pro', 'serial_number' => 'IPH15-002', 'manufacturer' => 'Apple', 'purchase_price' => 14990, 'location' => 'Kontor Oslo', 'department' => 'Salg', 'status' => 'in_use'],
            ['title' => 'Dell Monitor 27" 4K', 'serial_number' => 'MON-DELL-001', 'manufacturer' => 'Dell', 'purchase_price' => 5990, 'location' => 'Kontor Oslo', 'department' => 'IT', 'status' => 'in_use'],
            ['title' => 'Dell Monitor 27" 4K', 'serial_number' => 'MON-DELL-002', 'manufacturer' => 'Dell', 'purchase_price' => 5990, 'location' => 'Kontor Oslo', 'department' => 'IT', 'status' => 'in_use'],
            ['title' => 'Herman Miller Aeron', 'serial_number' => 'CHAIR-HM-001', 'manufacturer' => 'Herman Miller', 'purchase_price' => 15900, 'location' => 'Kontor Oslo', 'department' => 'Ledelse', 'status' => 'in_use'],
            ['title' => 'Projektor Epson EB-2265U', 'serial_number' => 'PROJ-001', 'manufacturer' => 'Epson', 'purchase_price' => 12500, 'location' => 'Møterom A', 'department' => 'Drift', 'status' => 'in_use'],
            ['title' => 'NAS Synology DS923+', 'serial_number' => 'NAS-SYN-001', 'manufacturer' => 'Synology', 'purchase_price' => 8900, 'location' => 'Serverrom', 'department' => 'IT', 'status' => 'in_use'],
        ];

        foreach ($assets as $assetData) {
            $purchaseDate = now()->subMonths(fake()->numberBetween(1, 36));

            Asset::updateOrCreate(
                ['company_id' => $this->company->id, 'serial_number' => $assetData['serial_number']],
                array_merge($assetData, [
                    'currency' => 'NOK',
                    'purchase_date' => $purchaseDate,
                    'supplier' => fake()->company(),
                    'warranty_from' => $purchaseDate,
                    'warranty_until' => $purchaseDate->copy()->addYears(2),
                    'condition' => 'good',
                    'is_active' => true,
                    'created_by' => $this->user->id,
                    'responsible_user_id' => $this->user->id,
                ])
            );
        }

        $this->command->info('Created '.count($assets).' demo assets');
    }

    private function seedShareholders(): void
    {
        $shareClass = ShareClass::where('company_id', $this->company->id)->where('code', 'A')->first();

        $shareholders = [
            ['name' => 'Knut W. Horne', 'shareholder_type' => 'person', 'national_id' => '12345678901', 'shares' => 600],
            ['name' => 'Investering AS', 'shareholder_type' => 'company', 'organization_number' => '987654321', 'shares' => 300],
            ['name' => 'Mari Investorsen', 'shareholder_type' => 'person', 'national_id' => '98765432101', 'shares' => 100],
        ];

        foreach ($shareholders as $shData) {
            $shares = $shData['shares'];
            unset($shData['shares']);

            $shareholder = Shareholder::updateOrCreate(
                ['company_id' => $this->company->id, 'name' => $shData['name']],
                array_merge($shData, [
                    'country_code' => 'NO',
                    'address' => fake()->streetAddress(),
                    'postal_code' => fake()->postcode(),
                    'city' => fake()->city(),
                    'email' => fake()->safeEmail(),
                    'is_active' => true,
                ])
            );

            // Create shareholding
            if ($shareClass) {
                Shareholding::updateOrCreate(
                    ['shareholder_id' => $shareholder->id, 'share_class_id' => $shareClass->id],
                    [
                        'company_id' => $this->company->id,
                        'number_of_shares' => $shares,
                        'acquired_date' => now()->subYears(2),
                        'cost_per_share' => 100,
                        'acquisition_cost' => $shares * 100,
                        'acquisition_type' => 'purchase',
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('Created '.count($shareholders).' demo shareholders');
    }

    private function seedStockLocations(): void
    {
        $locations = [
            ['code' => 'MAIN', 'name' => 'Hovedlager Oslo', 'location_type' => 'warehouse', 'address' => 'Lagerveien 1, 0581 Oslo'],
            ['code' => 'SEC', 'name' => 'Sekundærlager', 'location_type' => 'warehouse', 'address' => 'Lagerveien 2, 0581 Oslo'],
        ];

        $products = Product::where('company_id', $this->company->id)
            ->whereHas('productType', fn ($q) => $q->where('code', 'VARE'))
            ->get();

        foreach ($locations as $locData) {
            $location = StockLocation::updateOrCreate(
                ['company_id' => $this->company->id, 'code' => $locData['code']],
                array_merge($locData, [
                    'is_active' => true,
                    'sort_order' => 0,
                ])
            );

            // Add stock levels for products
            foreach ($products as $product) {
                StockLevel::updateOrCreate(
                    ['product_id' => $product->id, 'stock_location_id' => $location->id],
                    [
                        'company_id' => $this->company->id,
                        'quantity_on_hand' => fake()->numberBetween(10, 100),
                        'quantity_reserved' => fake()->numberBetween(0, 5),
                        'average_cost' => $product->cost_price ?? fake()->randomFloat(2, 50, 500),
                        'last_counted_at' => now()->subDays(fake()->numberBetween(1, 30)),
                    ]
                );
            }
        }

        $this->command->info('Created '.count($locations).' demo stock locations with stock levels');
    }

    private function seedActivities(): void
    {
        $contacts = Contact::where('company_id', $this->company->id)->take(3)->get();
        $activityTypes = ActivityType::where('company_id', $this->company->id)->get();

        if ($contacts->isEmpty() || $activityTypes->isEmpty()) {
            return;
        }

        foreach ($contacts as $contact) {
            for ($i = 0; $i < 3; $i++) {
                $type = $activityTypes->random();
                $isCompleted = fake()->boolean(70);
                $dueDate = now()->subDays(fake()->numberBetween(1, 30));

                Activity::updateOrCreate(
                    [
                        'company_id' => $this->company->id,
                        'contact_id' => $contact->id,
                        'activity_type_id' => $type->id,
                        'subject' => $type->name.' - '.$contact->company_name.' #'.($i + 1),
                    ],
                    [
                        'description' => fake()->paragraph(),
                        'created_by' => $this->user->id,
                        'assigned_to' => $this->user->id,
                        'due_date' => $dueDate,
                        'is_completed' => $isCompleted,
                        'completed_at' => $isCompleted ? $dueDate->copy()->addHours(fake()->numberBetween(1, 48)) : null,
                    ]
                );
            }
        }

        $this->command->info('Created demo activities for contacts');
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
