<?php

use App\Http\Controllers\SuperAdmin\SystemController;
use App\Http\Controllers\SuperAdmin\ProfileController;
use App\Http\Controllers\SuperAdmin\PasswordController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\CompaniesController;
use App\Http\Controllers\SuperAdmin\SubscriptionsController;
use App\Http\Controllers\SuperAdmin\AnalyticsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Test route
Route::get('/super-admin-test', function () {
    return 'Super Admin routes working!';
})->name('super-admin.test');

Route::middleware(['auth', 'verified'])->prefix('super-admin')->group(function () {

    // ============================================
    // SUPER ADMIN DASHBOARD
    // ============================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('super-admin.dashboard');

    // ============================================
    // SYSTEM MANAGEMENT
    // ============================================
    Route::prefix('system')->group(function () {
        Route::get('/settings', [SystemController::class, 'settings'])->name('super-admin.system.settings');
        Route::patch('/settings/{section}', [SystemController::class, 'updateSettings'])->name('super-admin.system.settings.update');
        Route::get('/logs', [SystemController::class, 'logs'])->name('super-admin.system.logs');
        Route::post('/clear-cache', [SystemController::class, 'clearCache'])->name('super-admin.system.clear-cache');
        Route::post('/backup', [SystemController::class, 'createBackup'])->name('super-admin.system.backup');
        Route::post('/maintenance', [SystemController::class, 'toggleMaintenance'])->name('super-admin.system.maintenance');
    });

    // ============================================
    // COMPANIES MANAGEMENT
    // ============================================
    Route::prefix('companies')->group(function () {
        Route::get('/', [CompaniesController::class, 'index'])->name('super-admin.companies.index');
        Route::get('/{company}', [CompaniesController::class, 'show'])->name('super-admin.companies.show');
        Route::post('/', [CompaniesController::class, 'store'])->name('super-admin.companies.store');
        Route::patch('/{company}', [CompaniesController::class, 'update'])->name('super-admin.companies.update');
        Route::delete('/{company}', [CompaniesController::class, 'destroy'])->name('super-admin.companies.destroy');
        Route::patch('/{company}/toggle-status', [CompaniesController::class, 'toggleStatus'])->name('super-admin.companies.toggle-status');
    });

    // ============================================
    // SUBSCRIPTION MANAGEMENT
    // ============================================
    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [SubscriptionsController::class, 'index'])->name('super-admin.subscriptions.index');
        Route::get('/plans', [SubscriptionsController::class, 'plans'])->name('super-admin.subscriptions.plans');
        Route::post('/plans', [SubscriptionsController::class, 'storePlan'])->name('super-admin.subscriptions.plans.store');
        Route::get('/{subscription}', [SubscriptionsController::class, 'show'])->name('super-admin.subscriptions.show');
        Route::patch('/{subscription}', [SubscriptionsController::class, 'update'])->name('super-admin.subscriptions.update');
        Route::delete('/{subscription}', [SubscriptionsController::class, 'destroy'])->name('super-admin.subscriptions.destroy');
    });

    // ============================================
    // SYSTEM ANALYTICS
    // ============================================
    Route::prefix('analytics')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('super-admin.analytics.index');
        Route::get('/companies', [AnalyticsController::class, 'companies'])->name('super-admin.analytics.companies');
        Route::get('/subscriptions', [AnalyticsController::class, 'subscriptions'])->name('super-admin.analytics.subscriptions');
    });

    // ============================================
    // SYSTEM LOGS
    // ============================================
    Route::get('/logs/download', [SystemController::class, 'downloadLogs'])->name('super-admin.system.logs.download');

    // ============================================
    // SUPER ADMIN SETTINGS
    // ============================================
    Route::prefix('settings')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('super-admin.settings.profile');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('super-admin.settings.profile.update');
        
        Route::get('/password', [PasswordController::class, 'edit'])->name('super-admin.settings.password');
        Route::put('/password', [PasswordController::class, 'update'])->name('super-admin.settings.password.update');
        
        Route::get('/appearance', function () {
            return Inertia::render('super-admin/settings/Appearance');
        })->name('super-admin.settings.appearance');
    });
});
