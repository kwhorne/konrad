<?php

use App\Http\Controllers\AltinnController;
use App\Http\Controllers\AnnualAccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\EconomyController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TaxController;
use Illuminate\Support\Facades\Route;

// SEO routes
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/llms.txt', [SitemapController::class, 'llmsTxt'])->name('llms-txt');

Route::view('/', 'welcome')->name('welcome');
Route::view('/priser', 'pricing')->name('pricing');
Route::view('/kontakt', 'contact')->name('contact');
Route::view('/om-oss', 'about')->name('about');
Route::view('/bestill', 'order')->name('order');

// Blog routes
Route::get('/innsikt', [BlogController::class, 'index'])->name('blog.index');
Route::get('/innsikt/kategori/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/innsikt/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Legal pages
Route::get('/personvern', [LegalController::class, 'privacy'])->name('privacy');
Route::get('/vilkar', [LegalController::class, 'terms'])->name('terms');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Invitation routes (registration only via invitation)
    Route::get('/invitation/{token}', [InvitationController::class, 'show'])->name('invitation.accept');
    Route::post('/invitation/{token}', [InvitationController::class, 'accept']);
});

Route::middleware('auth')->group(function () {
    // Onboarding routes (must be before company middleware)
    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::view('/', 'app.onboarding')->name('index');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Routes that require a company
    Route::middleware('company')->group(function () {
        Route::get('/app', [AuthController::class, 'dashboard'])->name('dashboard');
        Route::view('/app/mine-aktiviteter', 'app.my-activities')->name('my-activities');
        Route::get('/app/settings', [AuthController::class, 'settings'])->name('settings');
        Route::post('/app/settings/password', [AuthController::class, 'updatePassword'])->name('settings.password');

        // Company settings routes - redirects to settings page with tabs
        Route::prefix('company')->name('company.')->middleware('company.manager')->group(function () {
            Route::redirect('/settings', '/app/settings')->name('settings');
            Route::redirect('/users', '/app/settings')->name('users');
        });

        // Subscription routes (for company owners/managers)
        Route::prefix('subscription')->name('subscription.')->middleware('company.manager')->group(function () {
            Route::get('/', [SubscriptionController::class, 'index'])->name('index');
            Route::post('/checkout/{module}', [SubscriptionController::class, 'checkout'])->name('checkout');
            Route::get('/success/{module}', [SubscriptionController::class, 'success'])->name('success');
            Route::get('/manage', [SubscriptionController::class, 'manage'])->name('manage');
        });

        // Contract routes
        Route::middleware('feature:contracts')->group(function () {
            Route::resource('app/contracts', \App\Http\Controllers\ContractController::class)->names('contracts');
        });

        // Asset routes
        Route::middleware(['feature:assets'])->group(function () {
            Route::resource('assets', \App\Http\Controllers\AssetController::class);
        });

        // Contact routes
        Route::middleware(['feature:contacts'])->group(function () {
            Route::resource('contacts', \App\Http\Controllers\ContactController::class);
            Route::resource('activity-types', \App\Http\Controllers\ActivityTypeController::class);
        });

        // Product routes
        Route::middleware(['feature:products'])->group(function () {
            Route::resource('products', \App\Http\Controllers\ProductController::class);
            Route::resource('product-groups', \App\Http\Controllers\ProductGroupController::class);
            Route::resource('product-types', \App\Http\Controllers\ProductTypeController::class);
            Route::resource('vat-rates', \App\Http\Controllers\VatRateController::class);
            Route::resource('units', \App\Http\Controllers\UnitController::class);
        });

        // Project routes
        Route::middleware(['feature:projects'])->group(function () {
            Route::resource('projects', \App\Http\Controllers\ProjectController::class);
            Route::resource('project-types', \App\Http\Controllers\ProjectTypeController::class);
            Route::resource('project-statuses', \App\Http\Controllers\ProjectStatusController::class);
        });

        // Work Order routes
        Route::middleware(['feature:work_orders'])->group(function () {
            Route::resource('work-orders', \App\Http\Controllers\WorkOrderController::class);
        });

        // Sales routes (Quotes, Orders, Invoices)
        Route::middleware(['feature:sales'])->group(function () {
            Route::resource('quotes', \App\Http\Controllers\QuoteController::class);
            Route::get('quotes/{quote}/pdf', [\App\Http\Controllers\QuoteController::class, 'pdf'])->name('quotes.pdf');
            Route::get('quotes/{quote}/preview', [\App\Http\Controllers\QuoteController::class, 'preview'])->name('quotes.preview');
            Route::post('quotes/{quote}/send', [\App\Http\Controllers\QuoteController::class, 'send'])->name('quotes.send');

            Route::resource('orders', \App\Http\Controllers\OrderController::class);
            Route::get('orders/{order}/pdf', [\App\Http\Controllers\OrderController::class, 'pdf'])->name('orders.pdf');
            Route::get('orders/{order}/preview', [\App\Http\Controllers\OrderController::class, 'preview'])->name('orders.preview');
            Route::post('orders/{order}/send', [\App\Http\Controllers\OrderController::class, 'send'])->name('orders.send');

            Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
            Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\InvoiceController::class, 'pdf'])->name('invoices.pdf');
            Route::get('invoices/{invoice}/preview', [\App\Http\Controllers\InvoiceController::class, 'preview'])->name('invoices.preview');
            Route::post('invoices/{invoice}/send', [\App\Http\Controllers\InvoiceController::class, 'send'])->name('invoices.send');
        });

        // Accounting routes
        Route::prefix('accounting')->name('accounting.')->group(function () {
            Route::get('/', [\App\Http\Controllers\AccountingController::class, 'index'])->name('index');
            Route::get('/incoming', [\App\Http\Controllers\AccountingController::class, 'incoming'])->name('incoming');
            Route::get('/vouchers', [\App\Http\Controllers\AccountingController::class, 'vouchers'])->name('vouchers');
            Route::get('/customer-ledger', [\App\Http\Controllers\AccountingController::class, 'customerLedger'])->name('customer-ledger');
            Route::get('/supplier-ledger', [\App\Http\Controllers\AccountingController::class, 'supplierLedger'])->name('supplier-ledger');
        });
        Route::resource('accounts', \App\Http\Controllers\AccountController::class);

        // VAT Reports
        Route::get('/vat-reports', [\App\Http\Controllers\VatReportController::class, 'index'])->name('vat-reports.index');
        Route::get('/vat-reports/{vatReport}', [\App\Http\Controllers\VatReportController::class, 'show'])->name('vat-reports.show');

        // Help / Documentation
        Route::get('/help', [\App\Http\Controllers\HelpController::class, 'index'])->name('help');
        Route::get('/help/{section}', [\App\Http\Controllers\HelpController::class, 'section'])->name('help.section');

        // Report routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
            Route::get('/general-ledger', [\App\Http\Controllers\ReportController::class, 'generalLedger'])->name('general-ledger');
            Route::get('/voucher-journal', [\App\Http\Controllers\ReportController::class, 'voucherJournal'])->name('voucher-journal');
            Route::get('/trial-balance', [\App\Http\Controllers\ReportController::class, 'trialBalance'])->name('trial-balance');
            Route::get('/income-statement', [\App\Http\Controllers\ReportController::class, 'incomeStatement'])->name('income-statement');
            Route::get('/balance-sheet', [\App\Http\Controllers\ReportController::class, 'balanceSheet'])->name('balance-sheet');
        });

        // Shareholder routes
        Route::middleware('feature:shareholders')->prefix('shareholders')->name('shareholders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ShareholderController::class, 'index'])->name('index');
            Route::get('/register', [\App\Http\Controllers\ShareholderController::class, 'register'])->name('register');
            Route::get('/classes', [\App\Http\Controllers\ShareholderController::class, 'classes'])->name('classes');
            Route::get('/transactions', [\App\Http\Controllers\ShareholderController::class, 'transactions'])->name('transactions');
            Route::get('/dividends', [\App\Http\Controllers\ShareholderController::class, 'dividends'])->name('dividends');
            Route::get('/reports', [\App\Http\Controllers\ShareholderController::class, 'reports'])->name('reports');
            Route::get('/capital-changes', [\App\Http\Controllers\ShareholderController::class, 'capitalChanges'])->name('capital-changes');
        });

        // Tax routes
        Route::prefix('tax')->name('tax.')->group(function () {
            Route::get('/', [TaxController::class, 'returns'])->name('returns');
            Route::get('/adjustments', [TaxController::class, 'adjustments'])->name('adjustments');
            Route::get('/deferred', [TaxController::class, 'deferred'])->name('deferred');
            Route::get('/depreciation', [TaxController::class, 'depreciation'])->name('depreciation');
        });

        // Annual accounts routes
        Route::prefix('annual-accounts')->name('annual-accounts.')->group(function () {
            Route::get('/', [AnnualAccountController::class, 'index'])->name('index');
            Route::get('/{annualAccount}/notes', [AnnualAccountController::class, 'notes'])->name('notes');
            Route::get('/{annualAccount}/cash-flow', [AnnualAccountController::class, 'cashFlow'])->name('cash-flow');
        });

        // Altinn routes
        Route::get('/altinn', [AltinnController::class, 'index'])->name('altinn.index');

        // Admin routes - only accessible by admin users
        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/users', [AuthController::class, 'adminUsers'])->name('users');
            Route::get('/analytics', [AuthController::class, 'adminAnalytics'])->name('analytics');
            Route::get('/system', [AuthController::class, 'adminSystem'])->name('system');
            Route::view('/companies', 'admin.companies')->name('companies');
            Route::view('/modules', 'admin.modules')->name('modules');
            Route::view('/two-factor-whitelist', 'admin.two-factor-whitelist')->name('two-factor-whitelist');
            Route::get('/help', [AuthController::class, 'adminHelp'])->name('help');
            Route::view('/posts', 'admin.posts')->name('posts');
        });

        // Timesheet routes
        Route::prefix('timer')->name('timesheets.')->group(function () {
            Route::view('/', 'timesheets.index')->name('index');
            Route::view('/historikk', 'timesheets.history')->name('history');
            Route::view('/godkjenning', 'timesheets.approval')->name('approval')->middleware('company.manager');
            Route::view('/rapporter', 'timesheets.reports')->name('reports')->middleware('company.manager');
        });

        // Inventory routes
        Route::middleware(['feature:inventory'])->prefix('inventory')->name('inventory.')->group(function () {
            Route::view('/', 'inventory.dashboard')->name('dashboard');
            Route::view('/stock-levels', 'inventory.stock-levels')->name('stock-levels');
            Route::view('/transactions', 'inventory.transactions')->name('transactions');
            Route::view('/locations', 'inventory.locations')->name('locations');
            Route::view('/adjustments', 'inventory.adjustments')->name('adjustments');
            Route::view('/stock-counts', 'inventory.stock-counts.index')->name('stock-counts.index');
            Route::get('/stock-counts/{stockCount}', fn ($stockCount) => view('inventory.stock-counts.show', compact('stockCount')))->name('stock-counts.show');
        });

        // Purchasing routes
        Route::middleware(['feature:inventory'])->prefix('purchasing')->name('purchasing.')->group(function () {
            Route::view('/purchase-orders', 'purchasing.purchase-orders.index')->name('purchase-orders.index');
            Route::view('/purchase-orders/create', 'purchasing.purchase-orders.create')->name('purchase-orders.create');
            Route::get('/purchase-orders/{purchaseOrder}', fn ($purchaseOrder) => view('purchasing.purchase-orders.show', compact('purchaseOrder')))->name('purchase-orders.show');
            Route::get('/purchase-orders/{purchaseOrder}/edit', fn ($purchaseOrder) => view('purchasing.purchase-orders.edit', compact('purchaseOrder')))->name('purchase-orders.edit');

            Route::view('/goods-receipts', 'purchasing.goods-receipts.index')->name('goods-receipts.index');
            Route::view('/goods-receipts/create', 'purchasing.goods-receipts.create')->name('goods-receipts.create');
            Route::get('/goods-receipts/{goodsReceipt}', fn ($goodsReceipt) => view('purchasing.goods-receipts.show', compact('goodsReceipt')))->name('goods-receipts.show');
        });

        // Payroll routes - accessible by payroll users and admins
        Route::middleware('payroll')->prefix('lonn')->name('payroll.')->group(function () {
            Route::get('/', [PayrollController::class, 'dashboard'])->name('dashboard');
            Route::get('/ansatte', [PayrollController::class, 'employees'])->name('employees');
            Route::get('/lonnsarter', [PayrollController::class, 'payTypes'])->name('pay-types');
            Route::get('/lonnskjoring', [PayrollController::class, 'runs'])->name('runs');
            Route::get('/lonnskjoring/{payrollRun}', [PayrollController::class, 'showRun'])->name('runs.show');
            Route::get('/lonnsslipper', [PayrollController::class, 'payslips'])->name('payslips');
            Route::get('/feriepenger', [PayrollController::class, 'holidayPay'])->name('holiday-pay');
            Route::get('/a-melding', [PayrollController::class, 'aMelding'])->name('a-melding');
            Route::get('/rapporter', [PayrollController::class, 'reports'])->name('reports');
            Route::get('/innstillinger', [PayrollController::class, 'settings'])->name('settings');
        });

        // Economy routes - accessible by economy users and admins
        Route::middleware('economy')->prefix('economy')->name('economy.')->group(function () {
            Route::get('/', [EconomyController::class, 'dashboard'])->name('dashboard');
            Route::get('/accounting', [EconomyController::class, 'accounting'])->name('accounting');
            Route::get('/incoming', [EconomyController::class, 'incoming'])->name('incoming');
            Route::get('/vouchers', [EconomyController::class, 'vouchers'])->name('vouchers');
            Route::get('/customer-ledger', [EconomyController::class, 'customerLedger'])->name('customer-ledger');
            Route::get('/supplier-ledger', [EconomyController::class, 'supplierLedger'])->name('supplier-ledger');
            Route::get('/reports', [EconomyController::class, 'reports'])->name('reports');
            Route::get('/vat-reports', [EconomyController::class, 'vatReports'])->name('vat-reports');
            Route::get('/accounts', [EconomyController::class, 'accounts'])->name('accounts');
            Route::get('/shareholders', [EconomyController::class, 'shareholders'])->name('shareholders');
            Route::get('/tax', [EconomyController::class, 'tax'])->name('tax');
            Route::get('/annual-accounts', [EconomyController::class, 'annualAccounts'])->name('annual-accounts');
            Route::get('/altinn', [EconomyController::class, 'altinn'])->name('altinn');
            Route::view('/analysis', 'economy.analysis')->name('analysis');
            Route::view('/bank-reconciliation', 'economy.bank-reconciliation')->name('bank-reconciliation');
        });
    }); // End of company middleware group
});
