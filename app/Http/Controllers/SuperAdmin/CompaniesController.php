<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class CompaniesController extends Controller
{
    public function index()
    {
        $companies = Company::withCount(['users'])
            ->with(['users' => function($query) {
                $query->whereHas('roles', function($roleQuery) {
                    $roleQuery->where('name', 'admin');
                })->first();
            }])
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Company::count(),
            'active' => Company::where('is_active', true)->count(),
            'expired' => Company::where('subscription_expires_at', '<', now())->count(),
            'revenue' => Company::where('is_active', true)
                ->sum(\DB::raw("CASE 
                    WHEN subscription_plan = 'basic' THEN 29
                    WHEN subscription_plan = 'premium' THEN 79
                    WHEN subscription_plan = 'enterprise' THEN 199
                    ELSE 0
                END")),
        ];

        return Inertia::render('super-admin/companies/Index', [
            'companies' => $companies,
            'stats' => $stats,
        ]);
    }

    public function show(Company $company)
    {
        $company->load([
            'users.roles',
            'users.team',
            'teams.users'
        ]);

        $companyStats = [
            'total_users' => $company->users->count(),
            'active_users' => $company->users->where('email_verified_at', '!=', null)->count(),
            'total_teams' => $company->teams->count(),
            'leave_requests_count' => \App\Models\LeaveRequest::whereHas('user', function($query) use ($company) {
                $query->where('company_id', $company->id);
            })->count(),
        ];

        return Inertia::render('super-admin/companies/Show', [
            'company' => $company,
            'stats' => $companyStats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'subscription_plan' => 'required|in:basic,premium,enterprise',
            'max_employees' => 'required|integer|min:1|max:10000',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = true;
        $validated['subscription_expires_at'] = now()->addMonth();

        $company = Company::create($validated);

        return redirect()->route('super-admin.companies.index')
            ->with('success', 'Company created successfully.');
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email,' . $company->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'subscription_plan' => 'required|in:basic,premium,enterprise',
            'max_employees' => 'required|integer|min:1|max:10000',
            'is_active' => 'boolean',
            'subscription_expires_at' => 'required|date',
        ]);

        if ($validated['name'] !== $company->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $company->update($validated);

        return redirect()->route('super-admin.companies.index')
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        // Check if company has users
        if ($company->users()->count() > 0) {
            return redirect()->route('super-admin.companies.index')
                ->with('error', 'Cannot delete company with existing users.');
        }

        $company->delete();

        return redirect()->route('super-admin.companies.index')
            ->with('success', 'Company deleted successfully.');
    }

    public function toggleStatus(Company $company)
    {
        $company->update([
            'is_active' => !$company->is_active
        ]);

        $status = $company->is_active ? 'activated' : 'deactivated';

        return redirect()->route('super-admin.companies.index')
            ->with('success', "Company {$status} successfully.");
    }
}
