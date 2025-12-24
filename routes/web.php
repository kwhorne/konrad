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

    // Admin routes - only accessible by admin users
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AuthController::class, 'adminUsers'])->name('users');
        Route::get('/analytics', [AuthController::class, 'adminAnalytics'])->name('analytics');
        Route::get('/system', [AuthController::class, 'adminSystem'])->name('system');
    });
});
