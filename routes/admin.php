<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('admin')->group(function () {

    // Admin dashboard route
    Route::get('/dashboard', function () {

      return Inertia::render('admin/Dashboard');

    })->name('admin.dashboard');

  })->middleware(['auth']);
