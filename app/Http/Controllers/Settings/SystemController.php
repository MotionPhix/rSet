<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SystemController extends Controller
{
  /**
   * Show the system settings page.
   */
  public function index(Request $request)
  {
    $this->authorize('manage_system_settings');

    // System-wide settings would go here
    // This is primarily for super-admin users

    return Inertia::render('settings/system/Index', [
      'systemSettings' => [
        'app_name' => config('app.name'),
        'app_timezone' => config('app.timezone'),
        'default_currency' => config('app.currency', 'USD'),
        'max_companies' => config('app.max_companies', 10),
        'leave_request_expiry_days' => config('app.leave_request_expiry_days', 30),
        // Add other system-wide settings as needed
      ],
    ]);
  }

  /**
   * Update system settings.
   */
  public function update(Request $request)
  {
    $this->authorize('manage_system_settings');

    $validated = $request->validate([
      'app_name' => 'required|string|max:255',
      'app_timezone' => 'required|string|max:100',
      'default_currency' => 'required|string|size:3',
      'max_companies' => 'required|integer|min:1',
      'leave_request_expiry_days' => 'required|integer|min:1',
      // Validate other system settings as needed
    ]);

    // In a real implementation, you would update these settings
    // in your configuration or database

    return back()->with('success', 'System settings updated successfully.');
  }
}
