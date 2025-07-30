<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
  public function index()
  {
    $user = auth()->user();

    return Inertia::render('employee/reports/Index', [
      'personalStats' => $this->getPersonalStats($user),
      'teamStats' => $this->getTeamStats($user),
      'leaveHistory' => $this->getLeaveHistory($user),
    ]);
  }

  public function personal(Request $request)
  {
    $user = auth()->user();

    $request->validate([
      'year' => 'nullable|integer|min:2020|max:' . (date('Y') + 1),
    ]);

    $year = $request->year ?? date('Y');

    return response()->json([
      'yearly_summary' => $this->getYearlySummary($user, $year),
      'monthly_breakdown' => $this->getMonthlyBreakdown($user, $year),
      'leave_balance' => $this->getLeaveBalance($user, $year),
    ]);
  }

  public function team(Request $request)
  {
    $user = auth()->user();

    // Only managers can view team reports
    if (!$user->hasRole('manager') && !$user->can('view_team_leave_requests')) {
      abort(403, 'Unauthorized to view team reports');
    }

    $request->validate([
      'month' => 'nullable|date_format:Y-m',
    ]);

    $month = $request->month ?? date('Y-m');

    return response()->json([
      'team_overview' => $this->getTeamOverview($user, $month),
      'team_members_status' => $this->getTeamMembersStatus($user, $month),
    ]);
  }

  private function getPersonalStats($user)
  {
    $currentYear = date('Y');

    return [
      'total_requests_this_year' => LeaveRequest::where('user_id', $user->id)
        ->whereYear('start_date', $currentYear)->count(),
      'approved_days_this_year' => LeaveRequest::where('user_id', $user->id)
        ->where('status', 'approved')
        ->whereYear('start_date', $currentYear)->sum('days'),
      'pending_requests' => LeaveRequest::where('user_id', $user->id)
        ->where('status', 'pending')->count(),
      'last_leave' => LeaveRequest::where('user_id', $user->id)
        ->where('status', 'approved')
        ->latest('end_date')->first()?->end_date,
    ];
  }

  private function getTeamStats($user)
  {
    if (!$user->team_id || (!$user->hasRole('manager') && !$user->can('view_team_leave_requests'))) {
      return null;
    }

    $currentMonth = date('Y-m');

    return [
      'team_name' => $user->team->name,
      'team_members_count' => $user->team->users->count(),
      'pending_approvals' => LeaveRequest::whereHas('user', function ($q) use ($user) {
        $q->where('team_id', $user->team_id);
      })->where('status', 'pending')->count(),
      'approved_this_month' => LeaveRequest::whereHas('user', function ($q) use ($user) {
        $q->where('team_id', $user->team_id);
      })->where('status', 'approved')
        ->where('start_date', 'like', $currentMonth . '%')->count(),
    ];
  }

  private function getLeaveHistory($user)
  {
    return LeaveRequest::with('leaveType')
      ->where('user_id', $user->id)
      ->latest()
      ->take(10)
      ->get()
      ->map(function ($request) {
        return [
          'id' => $request->id,
          'leave_type' => $request->leaveType->name,
          'start_date' => $request->start_date,
          'end_date' => $request->end_date,
          'days' => $request->days,
          'status' => $request->status,
          'applied_at' => $request->created_at->format('M j, Y'),
        ];
      });
  }

  private function getYearlySummary($user, $year)
  {
    $requests = LeaveRequest::where('user_id', $user->id)
      ->whereYear('start_date', $year)
      ->get();

    return [
      'total_requests' => $requests->count(),
      'approved_requests' => $requests->where('status', 'approved')->count(),
      'rejected_requests' => $requests->where('status', 'rejected')->count(),
      'pending_requests' => $requests->where('status', 'pending')->count(),
      'total_approved_days' => $requests->where('status', 'approved')->sum('days'),
    ];
  }

  private function getMonthlyBreakdown($user, $year)
  {
    $monthlyData = [];

    for ($month = 1; $month <= 12; $month++) {
      $requests = LeaveRequest::where('user_id', $user->id)
        ->whereYear('start_date', $year)
        ->whereMonth('start_date', $month)
        ->get();

      $monthlyData[] = [
        'month' => Carbon::create($year, $month)->format('M'),
        'month_number' => $month,
        'requests' => $requests->count(),
        'approved_days' => $requests->where('status', 'approved')->sum('days'),
      ];
    }

    return $monthlyData;
  }

  private function getLeaveBalance($user, $year)
  {
    // This would typically come from company policies or leave type configurations
    // For now, we'll return a simple structure
    return [
      'annual_entitlement' => 25, // This should come from company settings or user profile
      'used_days' => LeaveRequest::where('user_id', $user->id)
        ->where('status', 'approved')
        ->whereYear('start_date', $year)->sum('days'),
      'pending_days' => LeaveRequest::where('user_id', $user->id)
        ->where('status', 'pending')
        ->whereYear('start_date', $year)->sum('days'),
      'remaining_days' => 25 - LeaveRequest::where('user_id', $user->id)
          ->where('status', 'approved')
          ->whereYear('start_date', $year)->sum('days'),
    ];
  }

  private function getTeamOverview($user, $month)
  {
    if (!$user->team_id) {
      return null;
    }

    $requests = LeaveRequest::whereHas('user', function ($q) use ($user) {
      $q->where('team_id', $user->team_id);
    })->where('start_date', 'like', $month . '%')->get();

    return [
      'total_requests' => $requests->count(),
      'approved_requests' => $requests->where('status', 'approved')->count(),
      'pending_requests' => $requests->where('status', 'pending')->count(),
      'total_days_off' => $requests->where('status', 'approved')->sum('days'),
    ];
  }

  private function getTeamMembersStatus($user, $month)
  {
    if (!$user->team_id) {
      return [];
    }

    return $user->team->users->map(function ($member) use ($month) {
      $requests = LeaveRequest::where('user_id', $member->id)
        ->where('start_date', 'like', $month . '%')
        ->get();

      return [
        'id' => $member->id,
        'name' => $member->name,
        'total_requests' => $requests->count(),
        'approved_days' => $requests->where('status', 'approved')->sum('days'),
        'pending_requests' => $requests->where('status', 'pending')->count(),
      ];
    });
  }
}
