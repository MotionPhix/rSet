<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
  public function run()
  {
    $companies = Company::all();

    if ($companies->isEmpty()) {
      $this->command->error('No companies found. Please run CompanySeeder first.');
      return;
    }

    foreach ($companies as $index => $company) {
      // Skip if users already exist for this company
      if (User::where('company_id', $company->id)->exists()) {
        $this->command->info("Users already exist for {$company->name}, skipping...");
        continue;
      }

      // Get teams for this company
      $teams = Team::where('company_id', $company->id)->get();

      if ($teams->isEmpty()) {
        $this->command->error("No teams found for company {$company->name}. Please run TeamsSeeder first.");
        continue;
      }

      // Create company admin
      $adminEmail = 'admin@' . strtolower(str_replace([' ', '.'], '', $company->name)) . '.com';

      $admin = User::create([
        'name' => $company->name . ' Admin',
        'email' => $adminEmail,
        'password' => Hash::make('password'),
        'company_id' => $company->id,
        'team_id' => null,
      ]);
      $admin->assignRole('admin');

      // Create HR Manager
      $hrTeam = $teams->where('name', 'Human Resources')->first();
      if ($hrTeam) {
        $hrEmail = 'hr@' . strtolower(str_replace([' ', '.'], '', $company->name)) . '.com';

        $hrManager = User::create([
          'name' => 'HR Manager',
          'email' => $hrEmail,
          'password' => Hash::make('password'),
          'company_id' => $company->id,
          'team_id' => $hrTeam->id,
        ]);
        $hrManager->assignRole('hr');

        // Update team manager
        $hrTeam->update(['manager_id' => $hrManager->id]);
      }

      // Create team managers and employees for each team
      foreach ($teams as $team) {
        if ($team->name === 'Human Resources') {
          continue; // Already handled above
        }

        // Create team manager
        $managerEmail = strtolower(str_replace(' ', '', $team->name)) . '.manager@' . strtolower(str_replace([' ', '.'], '', $company->name)) . '.com';

        $manager = User::create([
          'name' => $team->name . ' Manager',
          'email' => $managerEmail,
          'password' => Hash::make('password'),
          'company_id' => $company->id,
          'team_id' => $team->id,
        ]);
        $manager->assignRole('manager');

        // Update team manager
        $team->update(['manager_id' => $manager->id]);

        // Create employees for each team
        $employeeNames = $this->getEmployeeNames($team->name);

        foreach ($employeeNames as $employeeName) {
          $email = strtolower(str_replace(' ', '.', $employeeName)) . '@' . strtolower(str_replace([' ', '.'], '', $company->name)) . '.com';

          $employee = User::create([
            'name' => $employeeName,
            'email' => $email,
            'password' => Hash::make('password'),
            'company_id' => $company->id,
            'team_id' => $team->id,
          ]);
          $employee->assignRole('employee');
        }
      }

      // Create some employees without teams
      $unassignedEmployees = [
        'John Contractor',
        'Jane Freelancer',
      ];

      foreach ($unassignedEmployees as $employeeName) {
        $email = strtolower(str_replace(' ', '.', $employeeName)) . '@' . strtolower(str_replace([' ', '.'], '', $company->name)) . '.com';

        $employee = User::create([
          'name' => $employeeName,
          'email' => $email,
          'password' => Hash::make('password'),
          'company_id' => $company->id,
          'team_id' => null,
        ]);
        $employee->assignRole('employee');
      }
    }

    // Create a super admin (system owner) if it doesn't exist
    if (!User::where('email', 'superadmin@leavehub.com')->exists()) {
      $superAdmin = User::create([
        'name' => 'Super Administrator',
        'email' => 'superadmin@leavehub.com',
        'password' => Hash::make('password'),
        'company_id' => null, // No company assignment for super admin
        'team_id' => null,
      ]);
      $superAdmin->assignRole('super-admin');
    }

    $this->command->info('Users created and assigned to companies successfully!');
  }

  private function getEmployeeNames($teamName)
  {
    $employeesByTeam = [
      'Engineering' => [
        'Alice Johnson',
        'Bob Smith',
        'Charlie Brown',
        'Diana Prince',
        'Edward Norton',
        'Fiona Apple',
      ],
      'Marketing' => [
        'Grace Kelly',
        'Henry Ford',
        'Iris West',
        'Jack Ryan',
      ],
      'Sales' => [
        'Karen Page',
        'Luke Cage',
        'Mary Jane',
        'Nathan Drake',
        'Olivia Pope',
      ],
      'Design' => [
        'Peter Parker',
        'Quinn Fabray',
        'Rachel Green',
      ],
      'Finance' => [
        'Steve Rogers',
        'Tony Stark',
      ],
      'Human Resources' => [
        'Wanda Maximoff',
      ],
    ];

    return $employeesByTeam[$teamName] ?? [];
  }
}
