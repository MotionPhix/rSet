<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Show the roles and permissions settings page.
     */
    public function index(Request $request)
    {
        $this->authorize('assign_roles');

        // Get all roles except super-admin (system level)
        $roles = Role::whereNotIn('name', ['super-admin'])
            ->with('permissions:id,name')
            ->get();

        // Get all permissions grouped by category
        $permissions = Permission::all()
            ->groupBy(function($permission) {
                // Extract category from permission name (e.g., "view_users" -> "users")
                $parts = explode('_', $permission->name);
                return count($parts) > 1 ? $parts[1] : 'other';
            });

        return Inertia::render('settings/roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'canManagePermissions' => $request->user()->can('manage_user_permissions'),
        ]);
    }

    /**
     * Update role permissions.
     */
    public function updatePermissions(Request $request, Role $role)
    {
        $this->authorize('manage_user_permissions');

        // Prevent editing super-admin role
        if ($role->name === 'super-admin') {
            return back()->with('error', 'The super-admin role cannot be modified.');
        }

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions']);

        return back()->with('success', 'Role permissions updated successfully.');
    }
}
