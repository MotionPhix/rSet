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
    // Create permissions with more granular control
    $permissions = [
      // Leave Request Permissions
      'create_leave_request',
      'view_own_leave_request',
      'view_team_leave_requests',
      'view_all_leave_requests',
      'edit_own_leave_request',
      'edit_team_leave_requests',
      'edit_all_leave_requests',
      'delete_own_leave_request',
      'delete_team_leave_requests',
      'delete_all_leave_requests',
      'approve_leave_request',
      'reject_leave_request',
      'cancel_leave_request',

      // Leave Type Permissions
      'view_leave_types',
      'create_leave_types',
      'edit_leave_types',
      'delete_leave_types',

      // User Management Permissions
      'view_users',
      'create_users',
      'edit_users',
      'delete_users',
      'assign_roles',
      'manage_user_permissions',

      // Team Management Permissions
      'view_teams',
      'create_teams',
      'edit_teams',
      'delete_teams',
      'assign_team_members',

      // Company Management Permissions
      'view_company_profile',
      'edit_company_profile',
      'manage_company_settings',
      'view_company_statistics',
      'manage_company_subscription',

      // System Administration (Super Admin only)
      'manage_all_companies',
      'view_system_logs',
      'manage_system_settings',
      'impersonate_users',

      // Reports and Analytics
      'view_reports',
      'export_data',
      'view_analytics',
    ];

    foreach ($permissions as $permission) {
      Permission::create(['name' => $permission]);
    }

    // Create Super Admin role (for system owner)
    $superAdmin = Role::create(['name' => 'super-admin']);
    $superAdmin->givePermissionTo($permissions); // All permissions

    // Create Company Admin role (for company owners)
    $admin = Role::create(['name' => 'admin']);
    $admin->givePermissionTo([
      // Leave management
      'view_all_leave_requests',
      'edit_all_leave_requests',
      'delete_all_leave_requests',
      'approve_leave_request',
      'reject_leave_request',
      'cancel_leave_request',

      // Leave types
      'view_leave_types',
      'create_leave_types',
      'edit_leave_types',
      'delete_leave_types',

      // User management
      'view_users',
      'create_users',
      'edit_users',
      'delete_users',
      'assign_roles',

      // Team management
      'view_teams',
      'create_teams',
      'edit_teams',
      'delete_teams',
      'assign_team_members',

      // Company management
      'view_company_profile',
      'edit_company_profile',
      'manage_company_settings',
      'view_company_statistics',
      'manage_company_subscription',

      // Reports
      'view_reports',
      'export_data',
      'view_analytics',
    ]);

    // Create HR role
    $hr = Role::create(['name' => 'hr']);
    $hr->givePermissionTo([
      // Leave management
      'view_all_leave_requests',
      'edit_all_leave_requests',
      'approve_leave_request',
      'reject_leave_request',

      // Leave types
      'view_leave_types',
      'create_leave_types',
      'edit_leave_types',

      // User management
      'view_users',
      'create_users',
      'edit_users',

      // Team management
      'view_teams',
      'assign_team_members',

      // Company
      'view_company_profile',
      'view_company_statistics',

      // Reports
      'view_reports',
      'export_data',
    ]);

    // Create Manager role
    $manager = Role::create(['name' => 'manager']);
    $manager->givePermissionTo([
      // Leave management
      'view_team_leave_requests',
      'approve_leave_request',
      'reject_leave_request',

      // Leave types
      'view_leave_types',

      // User management
      'view_users',

      // Team management
      'view_teams',

      // Own leave requests
      'create_leave_request',
      'view_own_leave_request',
      'edit_own_leave_request',
      'delete_own_leave_request',
    ]);

    // Create Employee role
    $employee = Role::create(['name' => 'employee']);
    $employee->givePermissionTo([
      // Own leave requests
      'create_leave_request',
      'view_own_leave_request',
      'edit_own_leave_request',
      'delete_own_leave_request',

      // Leave types
      'view_leave_types',

      // Basic company info
      'view_company_profile',
    ]);

    $this->command->info('Roles and permissions created successfully!');
  }
}
