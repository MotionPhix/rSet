<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    /**
     * Create a new company with default settings.
     */
    public function createCompany(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            // Generate unique slug
            $slug = $this->generateUniqueSlug($data['name']);

            $company = Company::create([
                'name' => $data['name'],
                'slug' => $slug,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'website' => $data['website'] ?? null,
                'timezone' => $data['timezone'] ?? 'UTC',
                'currency' => $data['currency'] ?? 'USD',
                'max_employees' => $data['max_employees'] ?? 50,
                'subscription_plan' => $data['subscription_plan'] ?? 'basic',
                'subscription_expires_at' => $data['subscription_expires_at'] ?? null,
                'settings' => $data['settings'] ?? [],
            ]);

            // Create default leave types for the company
            $this->createDefaultLeaveTypes($company);

            return $company;
        });
    }

    /**
     * Update company information.
     */
    public function updateCompany(Company $company, array $data): Company
    {
        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $company->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $company->id);
        }

        $company->update($data);

        return $company->fresh();
    }

    /**
     * Assign user to company.
     */
    public function assignUserToCompany(User $user, Company $company): User
    {
        $user->update(['company_id' => $company->id]);

        return $user->fresh();
    }

    /**
     * Remove user from company.
     */
    public function removeUserFromCompany(User $user): User
    {
        $user->update(['company_id' => null, 'team_id' => null]);

        return $user->fresh();
    }

    /**
     * Get company statistics.
     */
    public function getCompanyStats(Company $company): array
    {
        return [
            'total_employees' => $company->users()->count(),
            'total_teams' => $company->teams()->count(),
            'total_leave_requests' => $company->leaveRequests()->count(),
            'pending_leave_requests' => $company->leaveRequests()->where('status', 'pending')->count(),
            'approved_leave_requests' => $company->leaveRequests()->where('status', 'approved')->count(),
            'rejected_leave_requests' => $company->leaveRequests()->where('status', 'rejected')->count(),
            'total_leave_types' => $company->leaveTypes()->count(),
        ];
    }

    /**
     * Check if company can add more employees.
     */
    public function canAddEmployee(Company $company): bool
    {
        return $company->users()->count() < $company->max_employees;
    }

    /**
     * Generate unique slug for company.
     */
    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists.
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Company::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Create default leave types for a new company.
     */
    private function createDefaultLeaveTypes(Company $company): void
    {
        $defaultLeaveTypes = [
            [
                'name' => 'Annual Leave',
                'description' => 'Yearly vacation leave',
                'days_allowed' => 21,
                'min_duration' => 1,
                'max_duration' => 21,
                'allow_custom_duration' => true,
                'requires_approval' => true,
                'full_pay_days' => 21,
                'half_pay_days' => 0,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Sick Leave',
                'description' => 'Medical leave for illness',
                'days_allowed' => 10,
                'min_duration' => 1,
                'max_duration' => 10,
                'allow_custom_duration' => true,
                'requires_approval' => false,
                'requires_documentation' => true,
                'documentation_type' => 'medical_certificate',
                'full_pay_days' => 10,
                'half_pay_days' => 0,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Maternity Leave',
                'description' => 'Leave for new mothers',
                'days_allowed' => 90,
                'min_duration' => 30,
                'max_duration' => 90,
                'allow_custom_duration' => false,
                'gender' => 'female',
                'requires_approval' => true,
                'requires_documentation' => true,
                'documentation_type' => 'medical_certificate',
                'full_pay_days' => 60,
                'half_pay_days' => 30,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Paternity Leave',
                'description' => 'Leave for new fathers',
                'days_allowed' => 14,
                'min_duration' => 7,
                'max_duration' => 14,
                'allow_custom_duration' => false,
                'gender' => 'male',
                'requires_approval' => true,
                'full_pay_days' => 14,
                'half_pay_days' => 0,
                'company_id' => $company->id,
            ],
        ];

        foreach ($defaultLeaveTypes as $leaveType) {
            $company->leaveTypes()->create($leaveType);
        }
    }
}
