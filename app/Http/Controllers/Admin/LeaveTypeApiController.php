<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeApiController extends Controller
{
  public function list(Request $request)
  {
    $this->authorize('manage leave types');

    $user = $request->user();
    $company = $user->company;

    // Get all leave types for the current company
    $leaveTypes = LeaveType::where('company_id', $company->id)
      ->orderBy('name')
      ->get(['id', 'name', 'description', 'days_allowed']);

    return response()->json($leaveTypes);
  }
}
