<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first company for demo purposes
        $company = Company::first();

        if (!$company) {
            $company = Company::create([
                'name' => 'Demo Company Ltd',
                'slug' => 'demo-company',
                'email' => 'admin@democompany.com',
                'phone' => '+1 (555) 123-4567',
                'address' => '123 Business Street, Corporate City, CC 12345',
                'website' => 'https://democompany.com',
                'timezone' => 'UTC',
                'currency' => 'USD',
                'max_employees' => 100,
                'subscription_plan' => 'premium',
                'subscription_expires_at' => now()->addYear(),
                'is_active' => true,
            ]);
        }

        // Create teams
        $teams = [
            ['name' => 'Management', 'company_id' => $company->id],
            ['name' => 'Human Resources', 'company_id' => $company->id],
            ['name' => 'Engineering', 'company_id' => $company->id],
            ['name' => 'Sales', 'company_id' => $company->id],
            ['name' => 'Marketing', 'company_id' => $company->id],
            ['name' => 'Finance', 'company_id' => $company->id],
        ];

        $createdTeams = [];
        foreach ($teams as $teamData) {
            $createdTeams[] = Team::create($teamData);
        }

        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $hrRole = Role::where('name', 'hr')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $employeeRole = Role::where('name', 'employee')->first();

        // Create demo users
        $users = [
            [
                'name' => 'John Administrator',
                'email' => 'admin@democompany.com',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'team_id' => $createdTeams[0]->id, // Management
                'role' => $adminRole,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Jane HR Manager',
                'email' => 'hr@democompany.com',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'team_id' => $createdTeams[1]->id, // HR
                'role' => $hrRole,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Bob Engineering Lead',
                'email' => 'bob@democompany.com',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'team_id' => $createdTeams[2]->id, // Engineering
                'role' => $managerRole,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Alice Sales Manager',
                'email' => 'alice@democompany.com',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'team_id' => $createdTeams[3]->id, // Sales
                'role' => $managerRole,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Charlie Developer',
                'email' => 'charlie@democompany.com',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'team_id' => $createdTeams[2]->id, // Engineering
                'role' => $employeeRole,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Diana Marketing Specialist',
                'email' => 'diana@democompany.com',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'team_id' => $createdTeams[4]->id, // Marketing
                'role' => $employeeRole,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Eve Accountant',
                'email' => 'eve@democompany.com',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'team_id' => $createdTeams[5]->id, // Finance
                'role' => $employeeRole,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Frank Sales Rep',
                'email' => 'frank@democompany.com',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'team_id' => $createdTeams[3]->id, // Sales
                'role' => $employeeRole,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::create($userData);
            $user->assignRole($role);

            // Set team managers
            if ($role->name === 'manager') {
                $team = Team::find($userData['team_id']);
                $team->update(['manager_id' => $user->id]);
            }
        }

        $this->command->info('Demo users and teams created successfully!');
    }
}
