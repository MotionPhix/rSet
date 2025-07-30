<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamApiController extends Controller
{
    public function list(Request $request)
    {
        $this->authorize('manage teams');

        $user = $request->user();
        $company = $user->company;

        // Get all teams for the current company
        $teams = Team::where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'manager_id']);

        return response()->json($teams);
    }
}
