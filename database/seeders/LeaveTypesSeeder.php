<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypesSeeder extends Seeder
{
  public function run()
  {
    // Annual Leave (Malawi minimum: 15 days for 5-day week)
    LeaveType::create([
      'name' => 'Annual Leave',
      'description' => 'Paid time off for rest and relaxation',
      'days_allowed' => 15,
      'min_duration' => 1,
      'max_duration' => 15,
      'gender' => null,
      'cooldown_days' => 0,
      'requires_approval' => true,
      'approvers' => ['manager'],
    ]);

    // Maternity Leave (Malawi: 8 weeks)
    LeaveType::create([
      'name' => 'Maternity Leave',
      'description' => '8 weeks paid leave for expecting mothers (Malawi Employment Act)',
      'days_allowed' => 40,
      'min_duration' => 40, // Must take full duration
      'max_duration' => 40,
      'gender' => 'female',
      'cooldown_days' => 1095, // 3 years
      'requires_documentation' => true,
      'documentation_type' => 'pregnancy_confirmation',
      'approvers' => ['hr'],
    ]);

    // Paternity Leave (Malawi: 2 weeks)
    LeaveType::create([
      'name' => 'Paternity Leave',
      'description' => '2 weeks paid leave for new fathers (Malawi Employment Act)',
      'days_allowed' => 10,
      'min_duration' => 5,  // Can take minimum 1 week
      'max_duration' => 10,
      'gender' => 'male',
      'cooldown_days' => 1095, // 3 years
      'approvers' => ['manager'],
    ]);

    // Sick Leave (Malawi: 4w full pay + 8w half pay)
    LeaveType::create([
      'name' => 'Sick Leave',
      'description' => '4 weeks full pay + 8 weeks half pay annually',
      'days_allowed' => 60, // 12 weeks total
      'full_pay_days' => 20, // 4 weeks
      'half_pay_days' => 40, // 8 weeks
      'requires_documentation' => true,
      'documentation_type' => 'medical_certificate',
      'approvers' => ['hr', 'manager'],
    ]);

    // Unpaid Leave
    LeaveType::create([
      'name' => 'Unpaid Leave',
      'description' => 'Leave without pay',
      'days_allowed' => 365, // Theoretical max
      'requires_approval' => true,
      'approvers' => ['hr'],
    ]);
  }
}
