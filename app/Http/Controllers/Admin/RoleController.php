<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display roles and permissions for the company
     */
    public function index()
    {
        // Get company-specific roles (excluding super-admin)
        $roles = Role::whereNotIn('name', ['super-admin'])
            ->with('permissions')
            ->get();

        $permissions = Permission::all()->groupBy(function ($permission) {
            // Group permissions by category
            $parts = explode('_', $permission->name);
            return $parts[count($parts) - 1]; // Get last part as category
        });

        return Inertia::render('admin/settings/roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    /**
     * Update permissions for a role
     */
    public function updatePermissions(Request $request, Role $role)
    {
        // Prevent modification of super-admin role
        abort_if($role->name === 'super-admin', 403);

        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.settings.roles')
            ->with('success', 'Role permissions updated successfully.');
    }
}
