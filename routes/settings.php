<?php

use App\Http\Controllers\SuperAdmin\PasswordController as SuperAdminPasswordController;
use App\Http\Controllers\SuperAdmin\ProfileController as SuperAdminProfileController;
use App\Http\Controllers\Employee\PasswordController as EmployeePasswordController;
use App\Http\Controllers\Employee\ProfileController as EmployeeProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'company.context'])->group(function () {

  // ============================================
  // GENERAL SETTINGS (Profile, Password, Appearance)
  // These are available to all users but render different views based on role
  // ============================================
  Route::prefix('settings')->group(function () {
    // Redirect to appropriate settings based on user role
    Route::get('/', function () {
      $user = auth()->user();
      if ($user->can('manage_system_settings')) {
        return redirect()->route('super-admin.dashboard');
      }
      return redirect()->route('settings.profile');
    });

    // Personal settings available to all users
    Route::get('/profile', function () {
      $user = auth()->user();
      if ($user->can('manage_system_settings')) {
        return app(SuperAdminProfileController::class)->edit(request());
      } else {
        return app(EmployeeProfileController::class)->edit(request());
      }
    })->name('settings.profile');
    
    // Profile update route - primarily for employees with avatar upload support
    Route::match(['patch', 'post'], '/profile', [EmployeeProfileController::class, 'update'])
      ->name('settings.profile.update');
    
    Route::delete('/profile', function () {
      $user = auth()->user();
      if ($user->can('manage_system_settings')) {
        return app(SuperAdminProfileController::class)->destroy(request());
      } else {
        return app(EmployeeProfileController::class)->destroy(request());
      }
    })->name('settings.profile.destroy');

    Route::delete('/profile/avatar', function () {
      $user = auth()->user();
      if (!$user->can('manage_system_settings')) {
        return app(EmployeeProfileController::class)->removeAvatar(request());
      }
      return redirect()->back();
    })->name('settings.profile.avatar.remove');

    Route::get('/password', function () {
      $user = auth()->user();
      if ($user->can('manage_system_settings')) {
        return app(SuperAdminPasswordController::class)->edit(request());
      } else {
        return app(EmployeePasswordController::class)->edit(request());
      }
    })->name('settings.password');
    
    Route::put('/password', function () {
      $user = auth()->user();
      if ($user->can('manage_system_settings')) {
        return app(SuperAdminPasswordController::class)->update(request());
      } else {
        return app(EmployeePasswordController::class)->update(request());
      }
    })->name('settings.password.update');

    Route::get('/appearance', function () {
      $user = auth()->user();
      $userRoles = $user->roles->pluck('name')->toArray();

      if ($user->can('manage_system_settings')) {
        return Inertia::render('super-admin/settings/Appearance');
      } elseif (in_array('admin', $userRoles) || in_array('hr', $userRoles)) {
        return Inertia::render('admin/settings/Appearance');
      } else {
        return Inertia::render('employee/settings/Appearance');
      }
    })->name('settings.appearance');
  });
});
