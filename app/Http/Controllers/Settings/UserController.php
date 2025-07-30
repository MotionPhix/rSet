<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Team;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Show the users settings page.
     */
    public function index(Request $request)
    {
        $this->authorize('view_users');

        $user = $request->user();
        $company = $user->company;

        $users = User::where('company_id', $company->id)
            ->with(['team:id,name', 'roles:id,name'])
            ->get();

        $teams = Team::where('company_id', $company->id)
            ->select('id', 'name')
            ->get();

        $roles = Role::whereNotIn('name', ['super-admin'])
            ->select('id', 'name')
            ->get();

        return Inertia::render('settings/users/Index', [
            'users' => $users,
            'teams' => $teams,
            'roles' => $roles,
            'canCreate' => $user->can('create_users'),
            'canEdit' => $user->can('edit_users'),
            'canDelete' => $user->can('delete_users'),
            'canAssignRoles' => $user->can('assign_roles'),
        ]);
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $this->authorize('create_users');

        $user = $request->user();
        $company = $user->company;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'team_id' => 'nullable|exists:teams,id',
            'role' => 'required|exists:roles,name',
        ]);

        // Check if team belongs to company
        if ($validated['team_id'] && Team::where('id', $validated['team_id'])->value('company_id') !== $company->id) {
            return back()->with('error', 'The selected team does not belong to your company.');
        }

        // Create user
        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_id' => $company->id,
            'team_id' => $validated['team_id'],
        ]);

        // Assign role
        if ($user->can('assign_roles')) {
            $newUser->assignRole($validated['role']);
        } else {
            $newUser->assignRole('employee');
        }

        return back()->with('success', 'User created successfully.');
    }

    /**
     * Update a user.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('edit_users');

        // Check if user belongs to requesting user's company
        if ($user->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'team_id' => 'nullable|exists:teams,id',
            'role' => $request->user()->can('assign_roles') ? 'required|exists:roles,name' : 'nullable',
        ]);

        // Check if team belongs to company
        if ($validated['team_id'] && Team::where('id', $validated['team_id'])->value('company_id') !== $request->user()->company_id) {
            return back()->with('error', 'The selected team does not belong to your company.');
        }

        // Update user
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'team_id' => $validated['team_id'],
        ]);

        // Update role if authorized
        if ($request->user()->can('assign_roles') && isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return back()->with('success', 'User updated successfully.');
    }

    /**
     * Delete a user.
     */
    public function destroy(Request $request, User $user)
    {
        $this->authorize('delete_users');

        // Check if user belongs to requesting user's company
        if ($user->company_id !== $request->user()->company_id) {
            abort(403);
        }

        // Check if user is trying to delete themselves
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }
}
