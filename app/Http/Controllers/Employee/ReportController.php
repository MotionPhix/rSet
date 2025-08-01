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
      'detailed_requests' => $this->getDetailedRequests($user, $year),
      'leave_type_breakdown' => $this->getLeaveTypeBreakdown($user, $year),
      'attendance_patterns' => $this->getAttendancePatterns($user, $year),
      'forfeited_analysis' => $this->getForfeitedAnalysis($user, $year),
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
        ->whereYear('start_date', $currentYear)
        ->get()
        ->sum(function ($request) {
          return \Carbon\Carbon::parse($request->start_date)->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1;
        }),
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
    return LeaveRequest::where('user_id', $user->id)
      ->latest()
      ->take(10)
      ->get()
      ->map(function ($request) {
        return [
          'id' => $request->id,
          'leave_type' => $request->type, // Use the type field directly
          'start_date' => $request->start_date,
          'end_date' => $request->end_date,
          'days' => \Carbon\Carbon::parse($request->start_date)->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1,
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

    $approvedRequests = $requests->where('status', 'approved');
    $totalApprovedDays = $approvedRequests->sum(function ($request) {
      return \Carbon\Carbon::parse($request->start_date)->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1;
    });

    return [
      'total_requests' => $requests->count(),
      'approved_requests' => $approvedRequests->count(),
      'rejected_requests' => $requests->where('status', 'rejected')->count(),
      'pending_requests' => $requests->where('status', 'pending')->count(),
      'total_approved_days' => $totalApprovedDays,
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

      $approvedDays = $requests->where('status', 'approved')->sum(function ($request) {
        return \Carbon\Carbon::parse($request->start_date)->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1;
      });

      $monthlyData[] = [
        'month' => Carbon::create($year, $month)->format('M'),
        'month_number' => $month,
        'requests' => $requests->count(),
        'approved_days' => $approvedDays,
      ];
    }

    return $monthlyData;
  }

  private function getLeaveBalance($user, $year)
  {
    // Get approved and pending requests for the year
    $approvedRequests = LeaveRequest::where('user_id', $user->id)
      ->where('status', 'approved')
      ->whereYear('start_date', $year)
      ->get();
      
    $pendingRequests = LeaveRequest::where('user_id', $user->id)
      ->where('status', 'pending')
      ->whereYear('start_date', $year)
      ->get();

    $usedDays = $approvedRequests->sum(function ($request) {
      return \Carbon\Carbon::parse($request->start_date)->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1;
    });

    $pendingDays = $pendingRequests->sum(function ($request) {
      return \Carbon\Carbon::parse($request->start_date)->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1;
    });

    // This would typically come from company policies or leave type configurations
    $annualEntitlement = 25; // This should come from company settings or user profile

    return [
      'annual_entitlement' => $annualEntitlement,
      'used_days' => $usedDays,
      'pending_days' => $pendingDays,
      'remaining_days' => $annualEntitlement - $usedDays,
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

    $approvedRequests = $requests->where('status', 'approved');
    $totalDaysOff = $approvedRequests->sum(function ($request) {
      return \Carbon\Carbon::parse($request->start_date)->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1;
    });

    return [
      'total_requests' => $requests->count(),
      'approved_requests' => $approvedRequests->count(),
      'pending_requests' => $requests->where('status', 'pending')->count(),
      'total_days_off' => $totalDaysOff,
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

      $approvedDays = $requests->where('status', 'approved')->sum(function ($request) {
        return \Carbon\Carbon::parse($request->start_date)->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1;
      });

      return [
        'id' => $member->id,
        'name' => $member->name,
        'total_requests' => $requests->count(),
        'approved_days' => $approvedDays,
        'pending_requests' => $requests->where('status', 'pending')->count(),
      ];
    });
  }

  private function getDetailedRequests($user, $year)
  {
    $requests = LeaveRequest::where('user_id', $user->id)
      ->whereYear('start_date', $year)
      ->with(['user'])
      ->orderBy('start_date', 'desc')
      ->get();

    return $requests->map(function ($request) {
      $startDate = Carbon::parse($request->start_date);
      $endDate = Carbon::parse($request->end_date);
      $days = $startDate->diffInDays($endDate) + 1;
      
      return [
        'id' => $request->id,
        'type' => $request->type,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'days' => $days,
        'status' => $request->status,
        'reason' => $request->reason,
        'applied_date' => $request->created_at->format('Y-m-d'),
        'approved_date' => $request->approved_at?->format('Y-m-d'),
        'approver_name' => $request->approver?->name,
        'week_of_year' => $startDate->weekOfYear,
        'quarter' => $startDate->quarter,
        'is_weekend_adjacent' => $this->isWeekendAdjacent($startDate, $endDate),
        'is_long_weekend' => $days >= 3 && $this->isWeekendAdjacent($startDate, $endDate),
      ];
    });
  }

  private function getLeaveTypeBreakdown($user, $year)
  {
    $requests = LeaveRequest::where('user_id', $user->id)
      ->whereYear('start_date', $year)
      ->get()
      ->groupBy('type');

    $breakdown = [];
    foreach ($requests as $type => $typeRequests) {
      $approvedRequests = $typeRequests->where('status', 'approved');
      $rejectedRequests = $typeRequests->where('status', 'rejected');
      $pendingRequests = $typeRequests->where('status', 'pending');

      $totalDays = $approvedRequests->sum(function ($request) {
        return Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
      });

      $breakdown[] = [
        'type' => $type,
        'total_requests' => $typeRequests->count(),
        'approved_requests' => $approvedRequests->count(),
        'rejected_requests' => $rejectedRequests->count(),
        'pending_requests' => $pendingRequests->count(),
        'total_days_used' => $totalDays,
        'average_duration' => $approvedRequests->count() > 0 ? round($totalDays / $approvedRequests->count(), 1) : 0,
        'approval_rate' => $typeRequests->count() > 0 ? round(($approvedRequests->count() / $typeRequests->count()) * 100, 1) : 0,
        'most_recent_use' => $approvedRequests->sortByDesc('end_date')->first()?->end_date,
      ];
    }

    return collect($breakdown)->sortByDesc('total_days_used')->values();
  }

  private function getAttendancePatterns($user, $year)
  {
    $requests = LeaveRequest::where('user_id', $user->id)
      ->where('status', 'approved')
      ->whereYear('start_date', $year)
      ->get();

    // Quarterly breakdown
    $quarters = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
    $monthlyPattern = array_fill(1, 12, 0);
    $dayOfWeekPattern = ['Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0, 'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0, 'Sunday' => 0];

    foreach ($requests as $request) {
      $startDate = Carbon::parse($request->start_date);
      $endDate = Carbon::parse($request->end_date);
      $days = $startDate->diffInDays($endDate) + 1;

      // Quarter analysis
      $quarters[$startDate->quarter] += $days;

      // Monthly pattern
      $monthlyPattern[$startDate->month] += $days;

      // Day of week pattern (start day preference)
      $dayOfWeekPattern[$startDate->format('l')] += 1;
    }

    return [
      'quarterly_distribution' => [
        'Q1' => $quarters[1],
        'Q2' => $quarters[2], 
        'Q3' => $quarters[3],
        'Q4' => $quarters[4],
      ],
      'monthly_pattern' => $monthlyPattern,
      'preferred_start_days' => $dayOfWeekPattern,
      'peak_month' => array_search(max($monthlyPattern), $monthlyPattern),
      'least_active_month' => array_search(min($monthlyPattern), $monthlyPattern),
      'total_leave_blocks' => $requests->count(),
      'average_leave_duration' => $requests->count() > 0 ? round($requests->sum(function ($r) {
        return Carbon::parse($r->start_date)->diffInDays(Carbon::parse($r->end_date)) + 1;
      }) / $requests->count(), 1) : 0,
    ];
  }

  private function getForfeitedAnalysis($user, $year)
  {
    $annualEntitlement = 25; // This should come from company settings
    $carryOverAllowed = 5; // Maximum days that can be carried over
    $carryOverDeadline = Carbon::create($year, 3, 31); // Deadline to use carried over days

    $approvedRequests = LeaveRequest::where('user_id', $user->id)
      ->where('status', 'approved')
      ->whereYear('start_date', $year)
      ->get();

    $usedDays = $approvedRequests->sum(function ($request) {
      return Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
    });

    $remainingDays = $annualEntitlement - $usedDays;
    $forfeitedDays = 0;
    $recommendations = [];

    // Calculate forfeited days from previous year (if past deadline)
    if (Carbon::now()->gt($carryOverDeadline)) {
      $previousYearRemaining = $this->getPreviousYearRemaining($user, $year - 1);
      $usedCarryOver = min($carryOverAllowed, $previousYearRemaining);
      $forfeitedDays = max(0, $previousYearRemaining - $usedCarryOver);
    }

    // Generate recommendations
    if ($remainingDays > 0) {
      $monthsRemaining = 12 - Carbon::now()->month + 1;
      if ($monthsRemaining <= 3 && $remainingDays > 0) {
        $recommendations[] = "You have {$remainingDays} days remaining with only {$monthsRemaining} months left. Consider planning your leave soon.";
      }
      
      if ($remainingDays >= $carryOverAllowed) {
        $excessDays = $remainingDays - $carryOverAllowed;
        $recommendations[] = "You can only carry over {$carryOverAllowed} days. {$excessDays} days will be forfeited if not used by year-end.";
      }
    }

    return [
      'annual_entitlement' => $annualEntitlement,
      'used_days' => $usedDays,
      'remaining_days' => $remainingDays,
      'forfeited_days_this_year' => 0, // Will be calculated at year end
      'forfeited_days_last_year' => $forfeitedDays,
      'carry_over_limit' => $carryOverAllowed,
      'utilization_rate' => round(($usedDays / $annualEntitlement) * 100, 1),
      'projected_forfeit' => max(0, $remainingDays - $carryOverAllowed),
      'recommendations' => $recommendations,
      'optimal_usage_timeline' => $this->getOptimalUsageTimeline($remainingDays),
    ];
  }

  private function getPreviousYearRemaining($user, $previousYear)
  {
    $annualEntitlement = 25;
    $usedLastYear = LeaveRequest::where('user_id', $user->id)
      ->where('status', 'approved')
      ->whereYear('start_date', $previousYear)
      ->get()
      ->sum(function ($request) {
        return Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
      });

    return max(0, $annualEntitlement - $usedLastYear);
  }

  private function getOptimalUsageTimeline($remainingDays)
  {
    if ($remainingDays <= 0) {
      return ['message' => 'You have used all your annual leave entitlement.'];
    }

    $monthsLeft = 12 - Carbon::now()->month + 1;
    $optimalPerMonth = ceil($remainingDays / max(1, $monthsLeft));

    return [
      'months_remaining' => $monthsLeft,
      'days_per_month_suggested' => $optimalPerMonth,
      'message' => "Consider taking approximately {$optimalPerMonth} days per month for the remaining {$monthsLeft} months.",
    ];
  }

  private function isWeekendAdjacent($startDate, $endDate)
  {
    $start = Carbon::parse($startDate);
    $end = Carbon::parse($endDate);
    
    // Check if leave starts on Monday (adjacent to weekend)
    if ($start->dayOfWeek === Carbon::MONDAY) {
      return true;
    }
    
    // Check if leave ends on Friday (adjacent to weekend)
    if ($end->dayOfWeek === Carbon::FRIDAY) {
      return true;
    }
    
    return false;
  }
}
