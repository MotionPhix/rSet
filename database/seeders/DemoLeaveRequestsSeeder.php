<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DemoLeaveRequestsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Creating demo leave requests...');

        // Get a demo employee (first employee found)
        $employee = User::whereHas('roles', function ($q) {
            $q->where('name', 'employee');
        })->first();

        if (!$employee) {
            $this->command->warn('No employee user found. Please run the user seeders first.');
            return;
        }

        // Get manager for approvals
        $manager = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['manager', 'hr', 'admin']);
        })->first();

        $leaveTypes = LeaveType::all();

        if ($leaveTypes->isEmpty()) {
            $this->command->warn('No leave types found. Please run LeaveTypesSeeder first.');
            return;
        }

        $this->command->info("Creating leave requests for employee: {$employee->name} ({$employee->email})");

        // Clear existing leave requests for this employee to avoid duplicates
        LeaveRequest::where('user_id', $employee->id)->delete();

        // Create a variety of realistic leave requests
        $requests = [
            // Past approved annual leave
            [
                'type' => 'Annual Leave',
                'start_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'days' => 5,
                'reason' => 'Family vacation to visit relatives in another city. Planned trip with spouse and children.',
                'status' => 'approved'
            ],

            // Past approved sick leave
            [
                'type' => 'Sick Leave',
                'start_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'days' => 2,
                'reason' => 'Flu symptoms with fever and body aches. Doctor recommended rest for full recovery.',
                'status' => 'approved'
            ],

            // Past rejected leave (conflicting with busy period)
            [
                'type' => 'Annual Leave',
                'start_date' => Carbon::now()->subMonth()->format('Y-m-d'),
                'days' => 3,
                'reason' => 'Long weekend getaway with friends. Request was for busy project period.',
                'status' => 'rejected'
            ],

            // Current pending request (submitted recently)
            [
                'type' => 'Annual Leave',
                'start_date' => Carbon::now()->addWeeks(2)->format('Y-m-d'),
                'days' => 7,
                'reason' => 'Wedding anniversary celebration and romantic getaway with spouse. Booked resort in advance.',
                'status' => 'pending'
            ],

            // Future emergency leave (urgent)
            [
                'type' => 'Emergency Leave',
                'start_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'days' => 2,
                'reason' => 'Family emergency - need to assist elderly parent with urgent medical appointments.',
                'status' => 'pending'
            ],

            // Future study leave
            [
                'type' => 'Study Leave',
                'start_date' => Carbon::now()->addMonth()->format('Y-m-d'),
                'days' => 3,
                'reason' => 'Professional certification exam preparation and exam attendance. Important for career development.',
                'status' => 'pending'
            ],

            // Longer annual leave (vacation)
            [
                'type' => 'Annual Leave',
                'start_date' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                'days' => 10,
                'reason' => 'Summer family vacation to Europe. Long-planned trip with extended family members.',
                'status' => 'pending'
            ],

            // Half-day sick leave (recent)
            [
                'type' => 'Sick Leave',
                'start_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'days' => 1,
                'reason' => 'Migraine headache and nausea. Unable to work effectively, need rest and medication.',
                'status' => 'approved'
            ],
        ];

        foreach ($requests as $requestData) {
            // Find the leave type
            $leaveType = $leaveTypes->firstWhere('name', $requestData['type']);
            if (!$leaveType) {
                $this->command->warn("Leave type '{$requestData['type']}' not found. Skipping...");
                continue;
            }

            $startDate = Carbon::parse($requestData['start_date']);
            $endDate = $startDate->copy()->addDays($requestData['days'] - 1);

            $leaveRequest = LeaveRequest::create([
                'user_id' => $employee->id,
                'approver_id' => ($requestData['status'] !== 'pending' && $manager) ? $manager->id : null,
                'type' => $requestData['type'],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'reason' => $requestData['reason'],
                'status' => $requestData['status'],
                'created_at' => $startDate->copy()->subDays(rand(1, 14)), // Created 1-14 days before start date
                'updated_at' => Carbon::now(),
            ]);

            $this->command->info("✅ Created {$requestData['status']} {$requestData['type']} request: {$requestData['days']} days starting {$startDate->format('M j, Y')}");
        }

        $this->command->info('');
        $this->command->info("🎉 Demo leave requests created successfully for {$employee->name}!");
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   • 8 leave requests with various statuses');
        $this->command->info('   • Mix of past, current, and future dates');
        $this->command->info('   • Different leave types (Annual, Sick, Emergency, Study)');
        $this->command->info('   • Realistic reasons and approval scenarios');
        $this->command->info('');
        $this->command->info('🔍 You can now test the leave management system with:');
        $this->command->info("   • Login as: {$employee->email}");
        $this->command->info('   • View leave requests in the employee dashboard');
        $this->command->info('   • Test creating new requests');
        $this->command->info('   • Check leave balance calculations');
    }
}
