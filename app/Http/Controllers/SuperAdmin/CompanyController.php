<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
  use AuthorizesRequests;

  /**
   * Show the company settings page.
   */
  public function index(Request $request)
  {
    $this->authorize('view_company_profile');

    $user = $request->user();
    $company = $user->company;

    return Inertia::render('settings/company/Index', [
      'company' => $company,
      'canEdit' => $user->can('edit_company_profile'),
    ]);
  }

  /**
   * Update the company information.
   */
  public function update(Request $request)
  {
    $this->authorize('edit_company_profile');

    $user = $request->user();
    $company = $user->company;

    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|max:255',
      'phone' => 'nullable|string|max:20',
      'address' => 'nullable|string|max:255',
      'website' => 'nullable|url|max:255',
      'timezone' => 'required|string|max:100',
      'currency' => 'required|string|size:3',
    ]);

    $company->update($validated);

    return back()->with('success', 'Company information updated successfully.');
  }
}
