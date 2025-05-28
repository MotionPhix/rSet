<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Create permissions
    $permissions = [
      'create_leave_request',
      'approve_leave_request',
      'reject_leave_request',
      'delete_leave_request',
      'view_leave_request',
      'manage_leave_types',
      'manage_teams',
      'manage_users',
    ];

    foreach ($permissions as $permission) {
      Permission::create(['name' => $permission]);
    }

    // Create roles and assign permissions
    $admin = Role::create(['name' => 'admin']);
    $admin->givePermissionTo($permissions);

    $hr = Role::create(['name' => 'hr']);
    $hr->givePermissionTo([
      'manage_leave_types',
      'manage_teams',
      'manage_users',
      'view_leave_request',
      'approve_leave_request',
      'reject_leave_request',
    ]);

    $manager = Role::create(['name' => 'manager']);
    $manager->givePermissionTo([
      'approve_leave_request',
      'reject_leave_request',
      'view_leave_request'
    ]);

    $employee = Role::create(['name' => 'employee']);
    $employee->givePermissionTo([
      'create_leave_request',
      'view_leave_request',
      'delete_leave_request'
    ]);
  }
}
