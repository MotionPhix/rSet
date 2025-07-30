<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run()
  {
    $companies = Company::all();

    foreach ($companies as $company) {
      // Create teams for each company
      $teams = [
        [
          'name' => 'Human Resources',
          'company_id' => $company->id,
        ],
        [
          'name' => 'Engineering',
          'company_id' => $company->id,
        ],
        [
          'name' => 'Marketing',
          'company_id' => $company->id,
        ],
        [
          'name' => 'Sales',
          'company_id' => $company->id,
        ],
        [
          'name' => 'Design',
          'company_id' => $company->id,
        ],
        [
          'name' => 'Finance',
          'company_id' => $company->id,
        ],
      ];

      foreach ($teams as $teamData) {
        Team::create($teamData);
      }
    }

    $this->command->info('Teams created for all companies successfully!');
  }
}
