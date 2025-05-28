<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run()
  {
    Team::create(['name' => 'HR']);
    Team::create(['name' => 'Engineering']);
    Team::create(['name' => 'Marketing']);
  }
}
