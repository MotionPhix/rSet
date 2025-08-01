<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamController extends Controller
{
    /**
     * Display a listing of teams for the company
     */
    public function index()
    {
        $teams = Team::where('company_id', auth()->user()->company_id)
            ->with(['manager', 'members'])
            ->get();

        return Inertia::render('admin/settings/teams/Index', [
            'teams' => $teams
        ]);
    }

    /**
     * Store a new team
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'manager_id' => 'nullable|exists:users,id'
        ]);

        $team = Team::create([
            'name' => $request->name,
            'description' => $request->description,
            'manager_id' => $request->manager_id,
            'company_id' => auth()->user()->company_id
        ]);

        return redirect()->route('admin.settings.teams')
            ->with('success', 'Team created successfully.');
    }

    /**
     * Update a team
     */
    public function update(Request $request, Team $team)
    {
        // Ensure team belongs to the admin's company
        abort_if($team->company_id !== auth()->user()->company_id, 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'manager_id' => 'nullable|exists:users,id'
        ]);

        $team->update($request->only(['name', 'description', 'manager_id']));

        return redirect()->route('admin.settings.teams')
            ->with('success', 'Team updated successfully.');
    }

    /**
     * Delete a team
     */
    public function destroy(Team $team)
    {
        // Ensure team belongs to the admin's company
        abort_if($team->company_id !== auth()->user()->company_id, 403);

        $team->delete();

        return redirect()->route('admin.settings.teams')
            ->with('success', 'Team deleted successfully.');
    }
}
