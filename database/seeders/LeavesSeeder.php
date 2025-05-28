<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeavesSeeder extends Seeder
{
  public function run()
  {
    $employee = User::role('employee')->first();

    LeaveRequest::create([
      'user_id' => $employee->id,
      'type' => 'vacation',
      'start_date' => Carbon::today(),
      'end_date' => Carbon::today()->addDays(3),
      'status' => 'pending',
    ]);
  }
}
