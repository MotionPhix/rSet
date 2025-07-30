<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
  /**
   * The root template that's loaded on the first page visit.
   *
   * @see https://inertiajs.com/server-side-setup#root-template
   *
   * @var string
   */
  protected $rootView = 'app';

  /**
   * Determines the current asset version.
   *
   * @see https://inertiajs.com/asset-versioning
   */
  public function version(Request $request): ?string
  {
    return parent::version($request);
  }

  /**
   * Define the props that are shared by default.
   *
   * @see https://inertiajs.com/shared-data
   *
   * @return array<string, mixed>
   */
  public function share(Request $request): array
  {
    [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

    $user = $request->user();

    // Load user with relationships if authenticated
    if ($user) {
      $user->load(['company', 'roles.permissions', 'team']);
    }

    return [
      ...parent::share($request),
      'name' => config('app.name'),
      'quote' => ['message' => trim($message), 'author' => trim($author)],
      'auth' => [
        'user' => $user ? [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
          'company_id' => $user->company_id,
          'team_id' => $user->team_id,
          'roles' => $user->roles->map(function ($role) {
            return [
              'id' => $role->id,
              'name' => $role->name,
            ];
          }),
          'team' => $user->team ? [
            'id' => $user->team->id,
            'name' => $user->team->name,
            'manager_id' => $user->team->manager_id,
          ] : null,
        ] : null,
        'company' => $user?->company ? [
          'id' => $user->company->id,
          'name' => $user->company->name,
          'slug' => $user->company->slug,
          'email' => $user->company->email,
          'phone' => $user->company->phone,
          'address' => $user->company->address,
          'website' => $user->company->website,
          'timezone' => $user->company->timezone,
          'currency' => $user->company->currency,
          'max_employees' => $user->company->max_employees,
          'subscription_plan' => $user->company->subscription_plan,
          'subscription_expires_at' => $user->company->subscription_expires_at,
          'is_active' => $user->company->is_active,
        ] : null,
        'abilities' => $user ? [
          // Leave Request Permissions
          'createLeaveRequest' => $user->can('create_leave_request'),
          'viewOwnLeaveRequest' => $user->can('view_own_leave_request'),
          'viewTeamLeaveRequests' => $user->can('view_team_leave_requests'),
          'viewAllLeaveRequests' => $user->can('view_all_leave_requests'),
          'editOwnLeaveRequest' => $user->can('edit_own_leave_request'),
          'editTeamLeaveRequests' => $user->can('edit_team_leave_requests'),
          'editAllLeaveRequests' => $user->can('edit_all_leave_requests'),
          'deleteOwnLeaveRequest' => $user->can('delete_own_leave_request'),
          'deleteTeamLeaveRequests' => $user->can('delete_team_leave_requests'),
          'deleteAllLeaveRequests' => $user->can('delete_all_leave_requests'),
          'approveLeaveRequest' => $user->can('approve_leave_request'),
          'rejectLeaveRequest' => $user->can('reject_leave_request'),
          'cancelLeaveRequest' => $user->can('cancel_leave_request'),

          // Leave Type Permissions
          'viewLeaveTypes' => $user->can('view_leave_types'),
          'createLeaveTypes' => $user->can('create_leave_types'),
          'editLeaveTypes' => $user->can('edit_leave_types'),
          'deleteLeaveTypes' => $user->can('delete_leave_types'),

          // User Management Permissions
          'viewUsers' => $user->can('view_users'),
          'createUsers' => $user->can('create_users'),
          'editUsers' => $user->can('edit_users'),
          'deleteUsers' => $user->can('delete_users'),
          'assignRoles' => $user->can('assign_roles'),
          'manageUserPermissions' => $user->can('manage_user_permissions'),

          // Team Management Permissions
          'viewTeams' => $user->can('view_teams'),
          'createTeams' => $user->can('create_teams'),
          'editTeams' => $user->can('edit_teams'),
          'deleteTeams' => $user->can('delete_teams'),
          'assignTeamMembers' => $user->can('assign_team_members'),

          // Company Management Permissions
          'viewCompanyProfile' => $user->can('view_company_profile'),
          'editCompanyProfile' => $user->can('edit_company_profile'),
          'manageCompanySettings' => $user->can('manage_company_settings'),
          'viewCompanyStatistics' => $user->can('view_company_statistics'),
          'manageCompanySubscription' => $user->can('manage_company_subscription'),

          // System Administration
          'manageAllCompanies' => $user->can('manage_all_companies'),
          'viewSystemLogs' => $user->can('view_system_logs'),
          'manageSystemSettings' => $user->can('manage_system_settings'),
          'impersonateUsers' => $user->can('impersonate_users'),

          // Reports and Analytics
          'viewReports' => $user->can('view_reports'),
          'exportData' => $user->can('export_data'),
          'viewAnalytics' => $user->can('view_analytics'),
        ] : [],
      ],
      'ziggy' => [
        ...(new Ziggy)->toArray(),
        'location' => $request->url(),
      ],
      'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
    ];
  }
}
