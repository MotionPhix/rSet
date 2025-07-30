<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypesSeeder extends Seeder
{
  public function run(): void
  {
    $leaveTypes = [
      [
        'name' => 'Annual Leave',
        'description' => 'Yearly vacation leave',
        'days_allowed' => 21,
        'min_duration' => 1,
        'max_duration' => 21,
        'allow_custom_duration' => true,
        'gender' => null,
        'requires_approval' => true,
        'requires_documentation' => false,
        'full_pay_days' => 21,
        'half_pay_days' => 0,
      ],
      [
        'name' => 'Sick Leave',
        'description' => 'Medical leave for illness',
        'days_allowed' => 10,
        'min_duration' => 1,
        'max_duration' => 10,
        'allow_custom_duration' => true,
        'gender' => null,
        'requires_approval' => false,
        'requires_documentation' => true,
        'full_pay_days' => 10,
        'half_pay_days' => 0,
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
        'full_pay_days' => 60,
        'half_pay_days' => 30,
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
        'requires_documentation' => false,
        'full_pay_days' => 14,
        'half_pay_days' => 0,
      ],
      [
        'name' => 'Emergency Leave',
        'description' => 'Urgent family or personal emergencies',
        'days_allowed' => 5,
        'min_duration' => 1,
        'max_duration' => 5,
        'allow_custom_duration' => true,
        'gender' => null,
        'requires_approval' => true,
        'requires_documentation' => true,
        'full_pay_days' => 3,
        'half_pay_days' => 2,
      ],
      [
        'name' => 'Study Leave',
        'description' => 'Educational and training purposes',
        'days_allowed' => 15,
        'min_duration' => 1,
        'max_duration' => 15,
        'allow_custom_duration' => true,
        'gender' => null,
        'requires_approval' => true,
        'requires_documentation' => true,
        'full_pay_days' => 10,
        'half_pay_days' => 5,
      ],
    ];

    foreach ($leaveTypes as $leaveType) {
      LeaveType::create($leaveType);
    }
  }
}
