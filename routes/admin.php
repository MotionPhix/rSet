<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'company.context', 'role:admin,hr'])->prefix('admin')->group(function () {

  // Admin dashboard route (admin only)
  Route::get('/dashboard', function () {
    return Inertia::render('admin/Dashboard');
  })->name('admin.dashboard')->middleware('role:admin');

  // User management routes (admin, hr)
  Route::middleware(['role:admin,hr'])->group(function () {
    Route::get('/users', function () {
      return Inertia::render('admin/users/Index');
    })->name('admin.users.index');

    Route::get('/users/create', function () {
      return Inertia::render('admin/users/Create');
    })->name('admin.users.create');

    Route::post('/users', function () {
      // Handle user creation
      return redirect()->route('admin.users.index');
    })->name('admin.users.store');

    Route::get('/users/{user}/edit', function () {
      return Inertia::render('admin/users/Edit');
    })->name('admin.users.edit');

    Route::put('/users/{user}', function () {
      // Handle user update
      return redirect()->route('admin.users.index');
    })->name('admin.users.update');

    Route::delete('/users/{user}', function () {
      // Handle user deletion
      return redirect()->route('admin.users.index');
    })->name('admin.users.destroy');
  });

  // Team management routes (admin, hr)
  Route::middleware(['role:admin,hr'])->group(function () {
    Route::get('/teams', function () {
      return Inertia::render('admin/teams/Index');
    })->name('admin.teams.index');

    Route::get('/teams/create', function () {
      return Inertia::render('admin/teams/Create');
    })->name('admin.teams.create');

    Route::post('/teams', function () {
      // Handle team creation
      return redirect()->route('admin.teams.index');
    })->name('admin.teams.store');

    Route::get('/teams/{team}/edit', function () {
      return Inertia::render('admin/teams/Edit');
    })->name('admin.teams.edit');

    Route::put('/teams/{team}', function () {
      // Handle team update
      return redirect()->route('admin.teams.index');
    })->name('admin.teams.update');

    Route::delete('/teams/{team}', function () {
      // Handle team deletion
      return redirect()->route('admin.teams.index');
    })->name('admin.teams.destroy');
  });

  // Leave type management routes (admin, hr)
  Route::middleware(['role:admin,hr'])->group(function () {
    Route::get('/leave-types', function () {
      return Inertia::render('admin/leave-types/Index');
    })->name('admin.leave-types.index');

    Route::get('/leave-types/create', function () {
      return Inertia::render('admin/leave-types/Create');
    })->name('admin.leave-types.create');

    Route::post('/leave-types', function () {
      // Handle leave type creation
      return redirect()->route('admin.leave-types.index');
    })->name('admin.leave-types.store');

    Route::get('/leave-types/{leaveType}/edit', function () {
      return Inertia::render('admin/leave-types/Edit');
    })->name('admin.leave-types.edit');

    Route::put('/leave-types/{leaveType}', function () {
      // Handle leave type update
      return redirect()->route('admin.leave-types.index');
    })->name('admin.leave-types.update');

    Route::delete('/leave-types/{leaveType}', function () {
      // Handle leave type deletion
      return redirect()->route('admin.leave-types.index');
    })->name('admin.leave-types.destroy');
  });

  // Company management routes (admin only)
  Route::middleware(['role:admin'])->group(function () {
    Route::get('/company/profile', function () {
      return Inertia::render('admin/company/Profile');
    })->name('admin.company.profile');

    Route::get('/company/employees', function () {
      return Inertia::render('admin/company/Employees');
    })->name('admin.company.employees');
  });

  // Reports routes (admin, hr)
  Route::middleware(['role:admin,hr'])->group(function () {
    Route::get('/reports', function () {
      return Inertia::render('admin/reports/Index');
    })->name('admin.reports.index');
  });

  // Analytics routes (admin only)
  Route::middleware(['role:admin'])->group(function () {
    Route::get('/analytics', function () {
      return Inertia::render('admin/analytics/Index');
    })->name('admin.analytics.index');
  });

});
