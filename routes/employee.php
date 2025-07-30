<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'company.context'])->group(function () {

  // Employee dashboard (all authenticated users)
  Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
  })->name('dashboard');

  // Leave request routes (all users can manage their own requests)
  Route::get('/leave-requests', function () {
    return Inertia::render('leave-requests/Index');
  })->name('leave-requests.index');

  Route::get('/leave-requests/create', function () {
    return Inertia::render('leave-requests/Create');
  })->name('leave-requests.create');

  Route::post('/leave-requests', function () {
    // Handle leave request creation
    return redirect()->route('leave-requests.index');
  })->name('leave-requests.store');

  Route::get('/leave-requests/{leaveRequest}', function () {
    return Inertia::render('leave-requests/Show');
  })->name('leave-requests.show');

  Route::get('/leave-requests/{leaveRequest}/edit', function () {
    return Inertia::render('leave-requests/Edit');
  })->name('leave-requests.edit')->middleware('permission:edit_own_leave_request');

  Route::put('/leave-requests/{leaveRequest}', function () {
    // Handle leave request update
    return redirect()->route('leave-requests.index');
  })->name('leave-requests.update')->middleware('permission:edit_own_leave_request');

  Route::delete('/leave-requests/{leaveRequest}', function () {
    // Handle leave request deletion
    return redirect()->route('leave-requests.index');
  })->name('leave-requests.destroy')->middleware('permission:delete_own_leave_request');

  // Leave request approval routes (managers, hr, admin only)
  Route::middleware(['role:manager,hr,admin'])->group(function () {
    Route::post('/leave-requests/{leaveRequest}/approve', function () {
      // Handle leave request approval
      return redirect()->route('leave-requests.index');
    })->name('leave-requests.approve');

    Route::post('/leave-requests/{leaveRequest}/reject', function () {
      // Handle leave request rejection
      return redirect()->route('leave-requests.index');
    })->name('leave-requests.reject');
  });

  // Leave types (view only for all users)
  Route::get('/leave-types', function () {
    return Inertia::render('leave-types/Index');
  })->name('leave-types.index');

  // Profile routes (all authenticated users)
  Route::get('/profile', function () {
    return Inertia::render('profile/Show');
  })->name('profile.show');

  Route::get('/profile/edit', function () {
    return Inertia::render('profile/Edit');
  })->name('profile.edit');

  Route::put('/profile', function () {
    // Handle profile update
    return redirect()->route('profile.show');
  })->name('profile.update');

});
