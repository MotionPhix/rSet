<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $this->command->info('🌱 Starting database seeding...');

    // Clear existing data (optional - comment out if you want to keep existing data)
    $this->command->info('🗑️  Clearing existing data...');
    \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    \DB::table('leave_requests')->truncate();
    \DB::table('model_has_roles')->truncate();
    \DB::table('users')->truncate();
    \DB::table('teams')->truncate();
    \DB::table('leave_types')->truncate();
    \DB::table('companies')->truncate();
    \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    // Seed in the correct order due to foreign key constraints
    $this->call([
      RolesAndPermissionsSeeder::class,  // 1. Create roles and permissions first
      DemoDataSeeder::class,             // 2. Create demo company, teams, and users
      LeaveTypesSeeder::class,           // 3. Create leave types
      LeaveRequestsSeeder::class,        // 4. Create sample leave requests
    ]);

    $this->command->info('✅ Database seeding completed successfully!');
    $this->command->info('');
    $this->command->info('📊 Sample data created:');
    $this->command->info('   • Demo company with 6 teams');
    $this->command->info('   • 6 Leave types with different configurations');
    $this->command->info('   • 8 Users with different roles (admin, hr, managers, employees)');
    $this->command->info('   • 30+ Leave requests with various statuses and dates');
    $this->command->info('');
    $this->command->info('🔑 Login credentials:');
    $this->command->info('   Admin: admin@democompany.com / password');
    $this->command->info('   HR Manager: hr@democompany.com / password');
    $this->command->info('   Manager: bob@democompany.com / password');
    $this->command->info('   Employee: charlie@democompany.com / password');
  }
}
