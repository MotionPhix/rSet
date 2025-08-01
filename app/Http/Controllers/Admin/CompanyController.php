<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function __construct(
        private CompanyService $companyService
    ) {}

    /**
     * Display company setup page for users without a company.
     */
    public function setup()
    {
        // Redirect if user already has a company
        if (Auth::user()->company_id) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('company/Setup');
    }

    /**
     * Store a new company and assign current user as admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'timezone' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
        ]);

        $company = $this->companyService->createCompany($request->all());

        // Assign current user to the company as admin
        $this->companyService->assignUserToCompany(Auth::user(), $company);

        // Assign admin role to the user
        Auth::user()->assignRole('admin');

        return redirect()->route('dashboard')
            ->with('success', 'Company created successfully!');
    }

    /**
     * Display company profile/settings (Admin only).
     */
    public function show()
    {
        $company = Auth::user()->company;

        if (!$company) {
            return redirect()->route('company.setup');
        }

        $stats = $this->companyService->getCompanyStats($company);

        return Inertia::render('admin/company/Profile', [
            'company' => $company,
            'stats' => $stats,
        ]);
    }

    /**
     * Update company information.
     */
    public function update(Request $request)
    {
        $company = Auth::user()->company;

        if (!$company) {
            return redirect()->route('company.setup');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'timezone' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
            'max_employees' => 'nullable|integer|min:1|max:1000',
        ]);

        $this->companyService->updateCompany($company, $request->all());

        return back()->with('success', 'Company information updated successfully!');
    }

    /**
     * Display subscription expired page.
     */
    public function subscriptionExpired()
    {
        return Inertia::render('company/SubscriptionExpired');
    }

    /**
     * Display company employees (Admin only).
     */
    public function employees()
    {
        $company = Auth::user()->company;

        if (!$company) {
            return redirect()->route('company.setup');
        }

        $employees = $company->users()
            ->with(['team', 'roles'])
            ->paginate(20);

        return Inertia::render('admin/company/Employees', [
            'employees' => $employees,
            'canAddEmployee' => $this->companyService->canAddEmployee($company),
        ]);
    }

    /**
     * Remove employee from company.
     */
    public function removeEmployee(Request $request, $userId)
    {
        $company = Auth::user()->company;
        $user = $company->users()->findOrFail($userId);

        // Prevent removing yourself
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot remove yourself from the company.');
        }

        $this->companyService->removeUserFromCompany($user);

        return back()->with('success', 'Employee removed from company successfully.');
    }
}
