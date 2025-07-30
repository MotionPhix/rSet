<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
  public function list(Request $request)
  {
    $this->authorize('manage users');

    $user = $request->user();
    $company = $user->company;

    // Get all users for the current company
    $users = User::where('company_id', $company->id)
      ->orderBy('name')
      ->get(['id', 'name', 'email', 'team_id']);

    return response()->json($users);
  }
}
