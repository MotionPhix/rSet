<?php

use App\Http\Controllers\Admin\CompanyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Company setup routes (for users without a company)
Route::middleware(['auth', 'verified'])->group(function () {
  Route::get('/company/setup', [CompanyController::class, 'setup'])->name('company.setup');
  Route::post('/company', [CompanyController::class, 'store'])->name('company.store');
});

// Company management routes (admin only)
Route::middleware(['auth', 'verified', 'company.context', 'role:admin'])->group(function () {
  Route::put('/company', [CompanyController::class, 'update'])->name('company.update');
  Route::delete('/company/employees/{user}', [CompanyController::class, 'removeEmployee'])->name('company.employees.remove');
});

// Subscription related routes
Route::middleware(['auth'])->group(function () {
  Route::get('/subscription/expired', function () {
    return Inertia::render('company/SubscriptionExpired');
  })->name('subscription.expired');
});
