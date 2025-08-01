<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class FrontendTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if test company already exists
        if (Company::where('slug', 'techcorp-frontend-test')->exists()) {
            $this->command->info('Frontend test data already exists. Skipping...');
            return;
        }

        // Create a test company
        $company = Company::create([
            'name' => 'TechCorp Solutions',
            'slug' => 'techcorp-frontend-test',
            'email' => 'info@techcorp.com',
            'phone' => '+1 (555) 123-4567',
            'address' => '123 Business Ave, Suite 100, Tech City, TC 12345',
            'website' => 'https://techcorp.com',
            'timezone' => 'America/New_York',
            'currency' => 'USD',
            'subscription_plan' => 'premium',
            'max_employees' => 200,
            'is_active' => true,
        ]);

        // Create teams
        $engineeringTeam = Team::create([
            'company_id' => $company->id,
            'name' => 'Engineering',
        ]);

        $marketingTeam = Team::create([
            'company_id' => $company->id,
            'name' => 'Marketing',
        ]);

        $hrTeam = Team::create([
            'company_id' => $company->id,
            'name' => 'Human Resources',
        ]);

        // Create leave types for the company
        $leaveTypes = [
            ['name' => 'Annual Leave', 'days_allowed' => 25],
            ['name' => 'Sick Leave', 'days_allowed' => 10],
            ['name' => 'Personal Leave', 'days_allowed' => 5],
            ['name' => 'Maternity/Paternity', 'days_allowed' => 90],
            ['name' => 'Study Leave', 'days_allowed' => 5],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::create([
                'company_id' => $company->id,
                'name' => $type['name'],
                'days_allowed' => $type['days_allowed'],
            ]);
        }

        // Create company admin
        $admin = User::create([
            'name' => 'John Smith',
            'email' => 'admin@techcorp.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'company_id' => $company->id,
            'team_id' => null, // Admins typically don't belong to specific teams
        ]);
        $admin->assignRole('admin');

        // Create HR Manager
        $hrManager = User::create([
            'name' => 'Sarah Johnson',
            'email' => 'hr@techcorp.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'company_id' => $company->id,
            'team_id' => $hrTeam->id,
        ]);
        $hrManager->assignRole('hr');

        // Create Team Managers
        $engineeringManager = User::create([
            'name' => 'Mike Chen',
            'email' => 'mike.chen@techcorp.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'company_id' => $company->id,
            'team_id' => $engineeringTeam->id,
        ]);
        $engineeringManager->assignRole('manager');

        $marketingManager = User::create([
            'name' => 'Lisa Rodriguez',
            'email' => 'lisa.rodriguez@techcorp.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'company_id' => $company->id,
            'team_id' => $marketingTeam->id,
        ]);
        $marketingManager->assignRole('manager');

        // Create Employees
        $employees = [
            [
                'name' => 'David Wilson',
                'email' => 'david.wilson@techcorp.com',
                'team' => $engineeringTeam,
            ],
            [
                'name' => 'Emma Davis',
                'email' => 'emma.davis@techcorp.com',
                'team' => $engineeringTeam,
            ],
            [
                'name' => 'James Brown',
                'email' => 'james.brown@techcorp.com',
                'team' => $engineeringTeam,
            ],
            [
                'name' => 'Anna Taylor',
                'email' => 'anna.taylor@techcorp.com',
                'team' => $marketingTeam,
            ],
            [
                'name' => 'Tom Miller',
                'email' => 'tom.miller@techcorp.com',
                'team' => $marketingTeam,
            ],
        ];

        foreach ($employees as $employeeData) {
            $employee = User::create([
                'name' => $employeeData['name'],
                'email' => $employeeData['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'company_id' => $company->id,
                'team_id' => $employeeData['team']->id,
            ]);
            $employee->assignRole('employee');
        }

        // Create some sample leave requests
        $annualLeaveType = LeaveType::where('company_id', $company->id)->where('name', 'Annual Leave')->first();
        $sickLeaveType = LeaveType::where('company_id', $company->id)->where('name', 'Sick Leave')->first();

        // Pending leave request
        LeaveRequest::create([
            'user_id' => User::where('email', 'david.wilson@techcorp.com')->first()->id,
            'company_id' => $company->id,
            'team_id' => $engineeringTeam->id,
            'leave_type_id' => $annualLeaveType->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(14),
            'days_requested' => 5,
            'reason' => 'Family vacation to Hawaii',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        // Approved leave request
        LeaveRequest::create([
            'user_id' => User::where('email', 'emma.davis@techcorp.com')->first()->id,
            'company_id' => $company->id,
            'team_id' => $engineeringTeam->id,
            'leave_type_id' => $sickLeaveType->id,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(3),
            'days_requested' => 2,
            'reason' => 'Medical appointment',
            'status' => 'approved',
            'submitted_at' => now()->subHours(6),
            'reviewed_at' => now()->subHours(2),
            'reviewed_by' => $engineeringManager->id,
        ]);

        // Rejected leave request
        LeaveRequest::create([
            'user_id' => User::where('email', 'james.brown@techcorp.com')->first()->id,
            'company_id' => $company->id,
            'team_id' => $engineeringTeam->id,
            'leave_type_id' => $annualLeaveType->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(7),
            'days_requested' => 3,
            'reason' => 'Personal travel',
            'status' => 'rejected',
            'submitted_at' => now()->subDays(2),
            'reviewed_at' => now()->subDays(1),
            'reviewed_by' => $engineeringManager->id,
            'rejection_reason' => 'High workload during this period. Please reschedule.',
        ]);

        $this->command->info('Frontend test data created successfully!');
        $this->command->info('Company: TechCorp Solutions');
        $this->command->info('Users created:');
        $this->command->info('- Admin: admin@techcorp.com (password: password)');
        $this->command->info('- HR Manager: hr@techcorp.com (password: password)');
        $this->command->info('- Engineering Manager: mike.chen@techcorp.com (password: password)');
        $this->command->info('- Marketing Manager: lisa.rodriguez@techcorp.com (password: password)');
        $this->command->info('- Employee: david.wilson@techcorp.com (password: password)');
        $this->command->info('- Employee: emma.davis@techcorp.com (password: password)');
        $this->command->info('- Employee: james.brown@techcorp.com (password: password)');
        $this->command->info('- Employee: anna.taylor@techcorp.com (password: password)');
        $this->command->info('- Employee: tom.miller@techcorp.com (password: password)');
    }
}
