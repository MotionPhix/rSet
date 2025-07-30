<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeavesSeeder extends Seeder
{
  public function run()
  {
    $companies = Company::all();

    foreach ($companies as $company) {
      $users = User::where('company_id', $company->id)
                  ->whereHas('roles', function($query) {
                    $query->whereIn('name', ['employee', 'manager']);
                  })
                  ->get();

      $leaveTypes = LeaveType::where('company_id', $company->id)->get();

      if ($users->isEmpty() || $leaveTypes->isEmpty()) {
        continue;
      }

      // Create various leave requests with different statuses and dates
      $this->createLeaveRequests($company, $users, $leaveTypes);
    }

    $this->command->info('Leave requests created for all companies successfully!');
  }

  private function createLeaveRequests($company, $users, $leaveTypes)
  {
    $statuses = ['pending', 'approved', 'rejected'];
    $reasons = [
      'Annual Leave' => [
        'Family vacation to the beach',
        'Wedding anniversary celebration',
        'Visit family in another city',
        'Rest and relaxation',
        'Travel abroad',
        'Staycation at home',
      ],
      'Sick Leave' => [
        'Flu symptoms',
        'Medical appointment',
        'Recovery from surgery',
        'Dental procedure',
        'Back pain treatment',
        'Eye examination',
      ],
      'Personal Leave' => [
        'Personal matters to attend to',
        'House moving',
        'Car maintenance',
        'Legal appointment',
        'Personal development',
      ],
      'Emergency Leave' => [
        'Family emergency',
        'Medical emergency',
        'Home emergency repair',
        'Urgent family matter',
      ],
      'Maternity Leave' => [
        'Maternity leave for childbirth',
        'Prenatal care and delivery',
      ],
      'Paternity Leave' => [
        'Paternity leave for new baby',
        'Support partner during childbirth',
      ],
      'Study Leave' => [
        'Professional certification exam',
        'University course attendance',
        'Training workshop',
      ],
      'Bereavement Leave' => [
        'Funeral attendance',
        'Mourning family member',
        'Memorial service',
      ],
    ];

    // Create leave requests for the past 6 months
    $startDate = Carbon::now()->subMonths(6);
    $endDate = Carbon::now()->addMonths(3);

    foreach ($users as $user) {
      // Each user gets 3-8 leave requests
      $requestCount = rand(3, 8);

      for ($i = 0; $i < $requestCount; $i++) {
        $leaveType = $leaveTypes->random();
        $status = $statuses[array_rand($statuses)];

        // Adjust status probabilities (more approved than rejected)
        if (rand(1, 100) <= 70) {
          $status = 'approved';
        } elseif (rand(1, 100) <= 85) {
          $status = 'pending';
        } else {
          $status = 'rejected';
        }

        // Generate random dates
        $requestStartDate = Carbon::createFromTimestamp(
          rand($startDate->timestamp, $endDate->timestamp)
        );

        // Duration based on leave type
        $duration = rand($leaveType->min_duration, min($leaveType->max_duration, 5));
        $requestEndDate = $requestStartDate->copy()->addDays($duration - 1);

        // Get appropriate reason
        $typeReasons = $reasons[$leaveType->name] ?? ['General leave request'];
        $reason = $typeReasons[array_rand($typeReasons)];

        // Get approver (manager or HR)
        $approver = null;
        if ($status !== 'pending') {
          $approvers = User::where('company_id', $company->id)
                          ->whereHas('roles', function($query) {
                            $query->whereIn('name', ['manager', 'hr', 'admin']);
                          })
                          ->where('id', '!=', $user->id)
                          ->get();

          if ($approvers->isNotEmpty()) {
            $approver = $approvers->random();
          }
        }

        LeaveRequest::create([
          'user_id' => $user->id,
          'company_id' => $company->id,
          'approver_id' => $approver?->id,
          'start_date' => $requestStartDate->format('Y-m-d'),
          'end_date' => $requestEndDate->format('Y-m-d'),
          'type' => $leaveType->name,
          'reason' => $reason,
          'status' => $status,
          'created_at' => $requestStartDate->copy()->subDays(rand(1, 14)),
          'updated_at' => $status === 'pending'
            ? $requestStartDate->copy()->subDays(rand(1, 14))
            : $requestStartDate->copy()->subDays(rand(0, 7)),
        ]);
      }
    }

    // Create some recent pending requests for testing
    $recentUsers = $users->take(5);
    foreach ($recentUsers as $user) {
      $leaveType = $leaveTypes->where('name', 'Annual Leave')->first() ?? $leaveTypes->first();

      $requestStartDate = Carbon::now()->addDays(rand(7, 30));
      $requestEndDate = $requestStartDate->copy()->addDays(rand(1, 5));

      LeaveRequest::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'approver_id' => null,
        'start_date' => $requestStartDate->format('Y-m-d'),
        'end_date' => $requestEndDate->format('Y-m-d'),
        'type' => $leaveType->name,
        'reason' => 'Recent leave request for testing',
        'status' => 'pending',
        'created_at' => Carbon::now()->subDays(rand(1, 3)),
        'updated_at' => Carbon::now()->subDays(rand(1, 3)),
      ]);
    }
  }
}
