<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
  return Inertia::render('Welcome');
})->name('home');

// Test route to check user roles (remove in production)
Route::get('/test-roles', function () {
  $user = auth()->user();
  if (!$user) {
    return response()->json(['error' => 'Not authenticated']);
  }

  return response()->json([
    'user' => $user->load(['roles.permissions', 'company', 'team']),
    'roles' => $user->roles->pluck('name'),
    'permissions' => $user->getAllPermissions()->pluck('name'),
    'company' => $user->company,
  ]);
})->middleware('auth');

require __DIR__ . '/admin.php';
require __DIR__ . '/employee.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/company.php';
require __DIR__ . '/auth.php';
