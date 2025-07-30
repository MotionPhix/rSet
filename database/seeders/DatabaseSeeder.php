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
      CompanySeeder::class,              // 2. Create companies
      TeamsSeeder::class,                // 3. Create teams (depends on companies)
      LeaveTypesSeeder::class,           // 4. Create leave types (depends on companies)
      UsersSeeder::class,                // 5. Create users (depends on companies and teams)
      LeavesSeeder::class,               // 6. Create leave requests (depends on users and leave types)
    ]);

    $this->command->info('✅ Database seeding completed successfully!');
    $this->command->info('');
    $this->command->info('📊 Sample data created:');
    $this->command->info('   • 5 Companies with different subscription plans');
    $this->command->info('   • 6 Teams per company (HR, Engineering, Marketing, Sales, Design, Finance)');
    $this->command->info('   • 8 Leave types per company (Annual, Sick, Maternity, Paternity, etc.)');
    $this->command->info('   • 20+ Users per company with different roles');
    $this->command->info('   • 100+ Leave requests with various statuses and dates');
    $this->command->info('');
    $this->command->info('🔑 Login credentials:');
    $this->command->info('   Super Admin: superadmin@leavehub.com / password');
    $this->command->info('   Company Admin: admin@techcorp.com / password');
    $this->command->info('   HR Manager: hr@techcorp.com / password');
    $this->command->info('   Team Manager: engineering.manager@techcorp.com / password');
    $this->command->info('   Employee: alice.johnson@techcorp.com / password');
    $this->command->info('');
    $this->command->info('🏢 Companies created:');
    $this->command->info('   • TechCorp Solutions (Premium plan)');
    $this->command->info('   • Global Innovations Ltd (Standard plan)');
    $this->command->info('   • StartupHub Inc (Basic plan)');
    $this->command->info('   • Creative Agency Pro (Standard plan)');
    $this->command->info('   • Manufacturing Corp (Premium plan)');
  }
}
