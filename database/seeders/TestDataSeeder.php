<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\Team;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds for testing purposes.
     * This creates minimal data for quick testing.
     */
    public function run(): void
    {
        $this->command->info('🧪 Creating test data...');

        // Create a test company
        $company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'email' => 'test@company.com',
            'phone' => '+1-555-TEST',
            'address' => '123 Test Street, Test City, TC 12345',
            'website' => 'https://test.company.com',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'max_employees' => 25,
            'subscription_plan' => 'premium',
            'subscription_expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        // Create teams
        $teams = [
            ['name' => 'Engineering'],
            ['name' => 'HR'],
            ['name' => 'Marketing'],
        ];

        foreach ($teams as $teamData) {
            Team::create([
                'name' => $teamData['name'],
                'company_id' => $company->id,
            ]);
        }

        $engineeringTeam = Team::where('company_id', $company->id)->where('name', 'Engineering')->first();
        $hrTeam = Team::where('company_id', $company->id)->where('name', 'HR')->first();

        // Create test users
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
        ]);
        $admin->assignRole('admin');

        $hrUser = User::create([
            'name' => 'Test HR',
            'email' => 'hr@test.com',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
            'team_id' => $hrTeam->id,
        ]);
        $hrUser->assignRole('hr');

        $manager = User::create([
            'name' => 'Test Manager',
            'email' => 'manager@test.com',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
            'team_id' => $engineeringTeam->id,
        ]);
        $manager->assignRole('manager');

        // Update team manager
        $engineeringTeam->update(['manager_id' => $manager->id]);

        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@test.com',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
            'team_id' => $engineeringTeam->id,
        ]);
        $employee->assignRole('employee');

        // Create leave types
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'description' => 'Yearly vacation leave',
                'days_allowed' => 21,
                'min_duration' => 1,
                'max_duration' => 21,
                'requires_approval' => true,
                'full_pay_days' => 21,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Sick Leave',
                'description' => 'Medical leave',
                'days_allowed' => 10,
                'min_duration' => 1,
                'max_duration' => 10,
                'requires_approval' => false,
                'requires_documentation' => true,
                'full_pay_days' => 10,
                'company_id' => $company->id,
            ],
        ];

        foreach ($leaveTypes as $leaveTypeData) {
            LeaveType::create($leaveTypeData);
        }

        // Create test leave requests
        $annualLeave = LeaveType::where('company_id', $company->id)->where('name', 'Annual Leave')->first();

        // Pending request
        LeaveRequest::create([
            'user_id' => $employee->id,
            'company_id' => $company->id,
            'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(9)->format('Y-m-d'),
            'type' => $annualLeave->name,
            'reason' => 'Test vacation request',
            'status' => 'pending',
        ]);

        // Approved request
        LeaveRequest::create([
            'user_id' => $employee->id,
            'company_id' => $company->id,
            'approver_id' => $manager->id,
            'start_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'end_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'type' => $annualLeave->name,
            'reason' => 'Approved vacation',
            'status' => 'approved',
        ]);

        $this->command->info('✅ Test data created successfully!');
        $this->command->info('');
        $this->command->info('🔑 Test login credentials:');
        $this->command->info('   Admin: admin@test.com / password');
        $this->command->info('   HR: hr@test.com / password');
        $this->command->info('   Manager: manager@test.com / password');
        $this->command->info('   Employee: employee@test.com / password');
    }
}
