<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
  public function run()
  {
    // HR User
    $hr = User::create([
      'name' => 'HR Admin',
      'email' => 'hr@example.com',
      'password' => Hash::make('password'),
      'team_id' => Team::where('name', 'HR')->first()->id,
    ]);
    $hr->assignRole('hr');

    // Engineering Manager
    $engManager = User::create([
      'name' => 'Engineering Manager',
      'email' => 'manager@example.com',
      'password' => Hash::make('password'),
      'team_id' => Team::where('name', 'Engineering')->first()->id,
    ]);
    $engManager->assignRole('manager');

    // Regular Employee
    User::create([
      'name' => 'John Doe',
      'email' => 'employee@example.com',
      'password' => Hash::make('password'),
      'team_id' => Team::where('name', 'Engineering')->first()->id,
    ])->assignRole('employee');
  }
}
