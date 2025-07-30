<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LeaveRequestsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['employee', 'manager']);
        })->get();

        $leaveTypes = LeaveType::all();

        if ($users->isEmpty() || $leaveTypes->isEmpty()) {
            $this->command->warn('No users or leave types found. Please run DemoDataSeeder and LeaveTypesSeeder first.');
            return;
        }

        $statuses = ['pending', 'approved', 'rejected'];
        $currentYear = Carbon::now()->year;

        // Create leave requests for the past 6 months and future 3 months
        for ($i = 0; $i < 30; $i++) {
            $user = $users->random();
            $leaveType = $leaveTypes->random();

            // Random date within the last 6 months or next 3 months
            $startDate = Carbon::now()
                ->subMonths(6)
                ->addDays(rand(0, 270)); // 9 months total range

            $days = rand($leaveType->min_duration, min($leaveType->max_duration, 10));
            $endDate = $startDate->copy()->addDays($days - 1);

            $status = $statuses[array_rand($statuses)];

            // Make future requests mostly pending
            if ($startDate->isFuture()) {
                $status = rand(0, 10) < 8 ? 'pending' : 'approved';
            }

            $approver = $status === 'approved' ? $this->getRandomApprover($user) : null;

            LeaveRequest::create([
                'user_id' => $user->id,
                'approver_id' => $approver,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'type' => $leaveType->name, // Use the leave type name as string
                'reason' => $this->getRandomReason($leaveType->name),
                'status' => $status,
            ]);
        }

        $this->command->info('Sample leave requests created successfully!');
    }

    private function getRandomReason(string $leaveType): string
    {
        $reasons = [
            'Annual Leave' => [
                'Family vacation to the beach',
                'Personal time off for relaxation',
                'Wedding anniversary celebration',
                'Visit family in another city',
                'Long weekend getaway',
            ],
            'Sick Leave' => [
                'Flu symptoms and fever',
                'Medical appointment and recovery',
                'Stomach bug',
                'Migraine and need rest',
                'Doctor recommended rest',
            ],
            'Emergency Leave' => [
                'Family emergency requiring immediate attention',
                'Urgent home repairs needed',
                'Emergency medical situation',
                'Unexpected family crisis',
                'Critical personal matter',
            ],
            'Study Leave' => [
                'Professional certification exam',
                'Attending industry conference',
                'Online course completion',
                'Skills development workshop',
                'Professional training session',
            ],
        ];

        $typeReasons = $reasons[$leaveType] ?? [
            'Personal time off needed',
            'Previously planned commitment',
            'Important personal matter',
        ];

        return $typeReasons[array_rand($typeReasons)];
    }

    private function getRandomApprover(User $user): ?int
    {
        // Get potential approvers (managers, HR, admins from the same company)
        $approvers = User::where('company_id', $user->company_id)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'hr', 'manager']);
            })
            ->where('id', '!=', $user->id)
            ->get();

        return $approvers->isNotEmpty() ? $approvers->random()->id : null;
    }

    private function getRandomRejectionReason(): string
    {
        $reasons = [
            'Insufficient leave balance',
            'Requested dates conflict with business needs',
            'Peak season - leave not approved',
            'Documentation incomplete',
            'Too many team members on leave during requested period',
            'Request submitted too late',
        ];

        return $reasons[array_rand($reasons)];
    }
}
