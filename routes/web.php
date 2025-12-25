<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/app', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/app/settings', [AuthController::class, 'settings'])->name('settings');
    Route::post('/app/settings/password', [AuthController::class, 'updatePassword'])->name('settings.password');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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
        Route::get('/vouchers', [\App\Http\Controllers\AccountingController::class, 'vouchers'])->name('vouchers');
        Route::get('/customer-ledger', [\App\Http\Controllers\AccountingController::class, 'customerLedger'])->name('customer-ledger');
        Route::get('/supplier-ledger', [\App\Http\Controllers\AccountingController::class, 'supplierLedger'])->name('supplier-ledger');
    });
    Route::resource('accounts', \App\Http\Controllers\AccountController::class);

    // Report routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::get('/general-ledger', [\App\Http\Controllers\ReportController::class, 'generalLedger'])->name('general-ledger');
        Route::get('/voucher-journal', [\App\Http\Controllers\ReportController::class, 'voucherJournal'])->name('voucher-journal');
        Route::get('/trial-balance', [\App\Http\Controllers\ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/income-statement', [\App\Http\Controllers\ReportController::class, 'incomeStatement'])->name('income-statement');
        Route::get('/balance-sheet', [\App\Http\Controllers\ReportController::class, 'balanceSheet'])->name('balance-sheet');
    });

    // Admin routes - only accessible by admin users
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AuthController::class, 'adminUsers'])->name('users');
        Route::get('/analytics', [AuthController::class, 'adminAnalytics'])->name('analytics');
        Route::get('/system', [AuthController::class, 'adminSystem'])->name('system');
    });
});
