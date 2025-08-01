<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the employee dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $currentYear = now()->year;

        // Get employee's leave statistics
        $approvedRequests = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->get();

        $daysTaken = $approvedRequests->sum(function ($request) {
            return $request->days;
        });

        $stats = [
            'total_requests' => LeaveRequest::where('user_id', $user->id)->count(),
            'pending_requests' => LeaveRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved_requests' => LeaveRequest::where('user_id', $user->id)->where('status', 'approved')->count(),
            'rejected_requests' => LeaveRequest::where('user_id', $user->id)->where('status', 'rejected')->count(),
            'days_taken' => $daysTaken,
        ];

        // Get recent leave requests
        $recentRequests = LeaveRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'leave_type' => ucfirst($request->type),
                    'start_date' => $request->start_date->format('M j, Y'),
                    'end_date' => $request->end_date->format('M j, Y'),
                    'days_requested' => $request->days,
                    'status' => $request->status,
                    'reason' => $request->reason,
                    'submitted_at' => $request->created_at->format('M j, Y'),
                ];
            });

        // Get leave balance by type - using the type field from leave_requests
        $leaveTypes = ['annual', 'sick', 'personal', 'emergency']; // Common leave types
        $leaveBalance = collect($leaveTypes)->map(function ($type) use ($user, $currentYear) {
            $requests = LeaveRequest::where('user_id', $user->id)
                ->where('type', $type)
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->get();

            $usedDays = $requests->sum(function ($request) {
                return $request->days;
            });

            // Default allowances - these should ideally come from a settings table
            $allowances = [
                'annual' => 25,
                'sick' => 10,
                'personal' => 5,
                'emergency' => 3,
            ];

            $totalDays = $allowances[$type] ?? 0;

            return [
                'type' => ucfirst($type) . ' Leave',
                'total_days' => $totalDays,
                'used_days' => $usedDays,
                'remaining_days' => max(0, $totalDays - $usedDays),
                'percentage_used' => $totalDays > 0 
                    ? round(($usedDays / $totalDays) * 100, 1) 
                    : 0,
            ];
        });

        // Get monthly leave usage for chart
        $monthlyUsage = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $requests = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereYear('start_date', $month->year)
                ->whereMonth('start_date', $month->month)
                ->get();

            $daysUsed = $requests->sum(function ($request) {
                return $request->days;
            });

            $monthlyUsage[] = [
                'month' => $month->format('M Y'),
                'days' => $daysUsed,
            ];
        }

        // Get leave requests by status for pie chart
        $requestsByStatus = [
            ['status' => 'Pending', 'count' => $stats['pending_requests']],
            ['status' => 'Approved', 'count' => $stats['approved_requests']],
            ['status' => 'Rejected', 'count' => $stats['rejected_requests']],
        ];

        // Get upcoming leave
        $upcomingLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->limit(3)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'leave_type' => ucfirst($request->type),
                    'start_date' => $request->start_date->format('M j, Y'),
                    'end_date' => $request->end_date->format('M j, Y'),
                    'days_requested' => $request->days,
                    'days_until' => now()->diffInDays($request->start_date),
                ];
            });

        // Get team members on leave (same team)
        $teamOnLeave = [];
        if ($user->team_id) {
            $teamOnLeave = LeaveRequest::with(['user'])
                ->whereHas('user', function ($query) use ($user) {
                    $query->where('team_id', $user->team_id)
                          ->where('id', '!=', $user->id);
                })
                ->where('status', 'approved')
                ->where('start_date', '<=', now()->addDays(7))
                ->where('end_date', '>=', now())
                ->get()
                ->map(function ($request) {
                    return [
                        'user_name' => $request->user->name,
                        'leave_type' => ucfirst($request->type),
                        'start_date' => $request->start_date->format('M j'),
                        'end_date' => $request->end_date->format('M j'),
                        'days_requested' => $request->days,
                    ];
                });
        }

        return Inertia::render('employee/Dashboard', [
            'stats' => $stats,
            'recentRequests' => $recentRequests,
            'leaveBalance' => $leaveBalance,
            'monthlyUsage' => $monthlyUsage,
            'requestsByStatus' => $requestsByStatus,
            'upcomingLeave' => $upcomingLeave,
            'teamOnLeave' => $teamOnLeave,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'team' => $user->team ? $user->team->name : null,
            ],
        ]);
    }
}
