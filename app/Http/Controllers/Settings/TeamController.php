<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Team;
use App\Models\User;

class TeamController extends Controller
{
    /**
     * Show the teams settings page.
     */
    public function index(Request $request)
    {
        $this->authorize('view_teams');

        $user = $request->user();
        $company = $user->company;

        $teams = Team::where('company_id', $company->id)
            ->with('manager:id,name,email')
            ->get();

        $managers = User::where('company_id', $company->id)
            ->whereHas('roles', function($query) {
                $query->where('name', 'manager');
            })
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('settings/teams/Index', [
            'teams' => $teams,
            'managers' => $managers,
            'canCreate' => $user->can('create_teams'),
            'canEdit' => $user->can('edit_teams'),
            'canDelete' => $user->can('delete_teams'),
        ]);
    }

    /**
     * Store a new team.
     */
    public function store(Request $request)
    {
        $this->authorize('create_teams');

        $user = $request->user();
        $company = $user->company;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $validated['company_id'] = $company->id;

        Team::create($validated);

        return back()->with('success', 'Team created successfully.');
    }

    /**
     * Update a team.
     */
    public function update(Request $request, Team $team)
    {
        $this->authorize('edit_teams');

        // Check if team belongs to user's company
        if ($team->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $team->update($validated);

        return back()->with('success', 'Team updated successfully.');
    }

    /**
     * Delete a team.
     */
    public function destroy(Request $request, Team $team)
    {
        $this->authorize('delete_teams');

        // Check if team belongs to user's company
        if ($team->company_id !== $request->user()->company_id) {
            abort(403);
        }

        // Check if there are users in this team
        $usersCount = User::where('team_id', $team->id)->count();
        if ($usersCount > 0) {
            return back()->with('error', 'Cannot delete a team that has members. Please reassign team members first.');
        }

        $team->delete();

        return back()->with('success', 'Team deleted successfully.');
    }
}
