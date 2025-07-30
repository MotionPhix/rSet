<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\CompanyController;
use App\Http\Controllers\Settings\TeamController;
use App\Http\Controllers\Settings\UserController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\LeaveTypeController;
use App\Http\Controllers\Settings\SystemController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'company.context'])->group(function () {

  // ============================================
  // SUPER ADMIN SETTINGS (System-wide settings)
  // ============================================
  Route::middleware(['can:manage_system_settings'])->prefix('settings')->group(function () {
    Route::redirect('/', '/settings/system');

    Route::get('/system', [SystemController::class, 'index'])->name('settings.system');
    Route::patch('/system', [SystemController::class, 'update'])->name('settings.system.update');

    Route::get('/companies', function () {
      return Inertia::render('settings/Companies');
    })->name('settings.companies')->middleware('can:manage_all_companies');

    Route::get('/logs', function () {
      return Inertia::render('settings/SystemLogs');
    })->name('settings.logs')->middleware('can:view_system_logs');
  });

  // ============================================
  // GENERAL SETTINGS (Profile, Password, Appearance)
  // These are available to all users but render different views based on role
  // ============================================
  Route::prefix('settings')->group(function () {
    // Redirect to appropriate settings based on user role
    Route::get('/', function () {
      $user = auth()->user();
      if ($user->can('manage_system_settings')) {
        return redirect()->route('settings.system');
      }
      return redirect()->route('settings.profile');
    });

    // Personal settings available to all users
    Route::get('/profile', [ProfileController::class, 'edit'])->name('settings.profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/password', [PasswordController::class, 'edit'])->name('settings.password');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('/appearance', function () {
      $user = auth()->user();
      $userRoles = $user->roles->pluck('name')->toArray();

      if ($user->can('manage_system_settings')) {
        return Inertia::render('settings/Appearance');
      } elseif (in_array('admin', $userRoles) || in_array('hr', $userRoles)) {
        return Inertia::render('admin/settings/Appearance');
      } else {
        return Inertia::render('employee/settings/Appearance');
      }
    })->name('settings.appearance');
  });
});
