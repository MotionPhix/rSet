<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use App\Models\Company;
use Illuminate\Database\Seeder;

class LeaveTypesSeeder extends Seeder
{
  public function run()
  {
    $companies = Company::all();

    foreach ($companies as $company) {
      // Create leave types for each company
      $leaveTypes = [
        [
          'name' => 'Annual Leave',
          'description' => 'Yearly vacation leave for rest and relaxation',
          'days_allowed' => 21,
          'min_duration' => 1,
          'max_duration' => 21,
          'allow_custom_duration' => true,
          'gender' => null,
          'min_employment_months' => 6,
          'cooldown_days' => 0,
          'max_usage_per_year' => 1,
          'full_pay_days' => 21,
          'half_pay_days' => 0,
          'requires_approval' => true,
          'approvers' => ['manager', 'hr'],
          'requires_documentation' => false,
          'company_id' => $company->id,
        ],
        [
          'name' => 'Sick Leave',
          'description' => 'Medical leave for illness or injury',
          'days_allowed' => 10,
          'min_duration' => 1,
          'max_duration' => 10,
          'allow_custom_duration' => true,
          'gender' => null,
          'min_employment_months' => 0,
          'cooldown_days' => 0,
          'max_usage_per_year' => 3,
          'full_pay_days' => 10,
          'half_pay_days' => 0,
          'requires_approval' => false,
          'approvers' => null,
          'requires_documentation' => true,
          'documentation_type' => 'medical_certificate',
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
          'min_employment_months' => 12,
          'cooldown_days' => 1095, // 3 years
          'max_usage_per_year' => 1,
          'full_pay_days' => 60,
          'half_pay_days' => 30,
          'requires_approval' => true,
          'approvers' => ['hr'],
          'requires_documentation' => true,
          'documentation_type' => 'medical_certificate',
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
          'min_employment_months' => 12,
          'cooldown_days' => 1095, // 3 years
          'max_usage_per_year' => 1,
          'full_pay_days' => 14,
          'half_pay_days' => 0,
          'requires_approval' => true,
          'approvers' => ['manager', 'hr'],
          'requires_documentation' => true,
          'documentation_type' => 'birth_certificate',
          'company_id' => $company->id,
        ],
        [
          'name' => 'Personal Leave',
          'description' => 'Personal time off for personal matters',
          'days_allowed' => 5,
          'min_duration' => 1,
          'max_duration' => 5,
          'allow_custom_duration' => true,
          'gender' => null,
          'min_employment_months' => 3,
          'cooldown_days' => 30,
          'max_usage_per_year' => 2,
          'full_pay_days' => 0,
          'half_pay_days' => 0,
          'requires_approval' => true,
          'approvers' => ['manager'],
          'requires_documentation' => false,
          'company_id' => $company->id,
        ],
        [
          'name' => 'Emergency Leave',
          'description' => 'Urgent leave for family emergencies',
          'days_allowed' => 3,
          'min_duration' => 1,
          'max_duration' => 3,
          'allow_custom_duration' => true,
          'gender' => null,
          'min_employment_months' => 0,
          'cooldown_days' => 90,
          'max_usage_per_year' => 3,
          'full_pay_days' => 3,
          'half_pay_days' => 0,
          'requires_approval' => false,
          'approvers' => null,
          'requires_documentation' => true,
          'documentation_type' => 'emergency_proof',
          'company_id' => $company->id,
        ],
        [
          'name' => 'Study Leave',
          'description' => 'Leave for educational purposes and professional development',
          'days_allowed' => 10,
          'min_duration' => 1,
          'max_duration' => 10,
          'allow_custom_duration' => true,
          'gender' => null,
          'min_employment_months' => 24,
          'cooldown_days' => 365,
          'max_usage_per_year' => 1,
          'full_pay_days' => 5,
          'half_pay_days' => 5,
          'requires_approval' => true,
          'approvers' => ['manager', 'hr'],
          'requires_documentation' => true,
          'documentation_type' => 'enrollment_certificate',
          'company_id' => $company->id,
        ],
        [
          'name' => 'Bereavement Leave',
          'description' => 'Leave for mourning the loss of a family member',
          'days_allowed' => 5,
          'min_duration' => 1,
          'max_duration' => 5,
          'allow_custom_duration' => true,
          'gender' => null,
          'min_employment_months' => 0,
          'cooldown_days' => 0,
          'max_usage_per_year' => 3,
          'full_pay_days' => 5,
          'half_pay_days' => 0,
          'requires_approval' => false,
          'approvers' => null,
          'requires_documentation' => true,
          'documentation_type' => 'death_certificate',
          'company_id' => $company->id,
        ],
      ];

      foreach ($leaveTypes as $leaveTypeData) {
        LeaveType::create($leaveTypeData);
      }
    }

    $this->command->info('Leave types created for all companies successfully!');
  }
}
