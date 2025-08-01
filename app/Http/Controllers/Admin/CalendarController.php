<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return Inertia::render('admin/calendar/Index', [
            'userPermissions' => [
                'canViewTeam' => true, // Admins can always view team calendars
                'canViewAllCompany' => $user->can('view_all_leave_requests'),
            ],
            'teams' => Team::where('company_id', $user->company_id)
                ->with('users')
                ->get()
                ->map(function ($team) {
                    return [
                        'id' => $team->id,
                        'name' => $team->name,
                        'users_count' => $team->users->count(),
                    ];
                }),
        ]);
    }

    public function getEvents(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'team_id' => 'nullable|exists:teams,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $user = auth()->user();
        $startDate = Carbon::parse($request->start);
        $endDate = Carbon::parse($request->end);

        $query = LeaveRequest::query()
            ->where('status', '!=', 'rejected')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($subQ) use ($startDate, $endDate) {
                      $subQ->where('start_date', '<=', $startDate)
                           ->where('end_date', '>=', $endDate);
                  });
            })
            ->with(['user']);

        // Filter by company
        $query->whereHas('user', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        });

        // Filter by specific user if requested
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by team if requested
        if ($request->filled('team_id')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('team_id', $request->team_id);
            });
        }

        $leaveRequests = $query->get();

        $events = $leaveRequests->map(function ($request) {
            return $this->formatLeaveEvent($request, false); // false = not own request for admin view
        });

        return response()->json($events);
    }

    public function getTeamEvents(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        $user = auth()->user();
        $startDate = Carbon::parse($request->start);
        $endDate = Carbon::parse($request->end);

        $query = LeaveRequest::query()
            ->where('status', 'approved') // Only show approved requests for team view
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($subQ) use ($startDate, $endDate) {
                      $subQ->where('start_date', '<=', $startDate)
                           ->where('end_date', '>=', $endDate);
                  });
            })
            ->with(['user']);

        // Filter by company
        $query->whereHas('user', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        });

        // Filter by specific team if requested
        if ($request->filled('team_id')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('team_id', $request->team_id);
            });
        }

        $leaveRequests = $query->get();

        $events = $leaveRequests->map(function ($request) {
            return $this->formatLeaveEvent($request, false, true); // Third param = team view
        });

        return response()->json($events);
    }

    public function getCompanyOverview(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $user = auth()->user();
        
        if (!$user->can('view_all_leave_requests')) {
            abort(403, 'Unauthorized to view company overview');
        }

        $startDate = Carbon::parse($request->start);
        $endDate = Carbon::parse($request->end);

        // Get all leave requests for the company in the date range
        $leaveRequests = LeaveRequest::query()
            ->whereHas('user', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($subQ) use ($startDate, $endDate) {
                      $subQ->where('start_date', '<=', $startDate)
                           ->where('end_date', '>=', $endDate);
                  });
            })
            ->with(['user', 'user.team'])
            ->get();

        // Calculate statistics
        $totalRequests = $leaveRequests->count();
        $approvedRequests = $leaveRequests->where('status', 'approved')->count();
        $pendingRequests = $leaveRequests->where('status', 'pending')->count();
        $rejectedRequests = $leaveRequests->where('status', 'rejected')->count();

        $totalDaysOff = $leaveRequests->where('status', 'approved')->sum(function ($request) {
            return Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
        });

        // Team breakdown
        $teamBreakdown = $leaveRequests->groupBy('user.team.name')->map(function ($requests, $teamName) {
            $approved = $requests->where('status', 'approved');
            return [
                'team_name' => $teamName ?: 'No Team',
                'total_requests' => $requests->count(),
                'approved_requests' => $approved->count(),
                'total_days' => $approved->sum(function ($request) {
                    return Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
                }),
                'employees_on_leave' => $approved->pluck('user_id')->unique()->count(),
            ];
        })->values();

        // Leave type breakdown
        $typeBreakdown = $leaveRequests->where('status', 'approved')->groupBy('type')->map(function ($requests, $type) {
            return [
                'type' => $type,
                'count' => $requests->count(),
                'total_days' => $requests->sum(function ($request) {
                    return Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
                }),
            ];
        })->values();

        return response()->json([
            'summary' => [
                'total_requests' => $totalRequests,
                'approved_requests' => $approvedRequests,
                'pending_requests' => $pendingRequests,
                'rejected_requests' => $rejectedRequests,
                'total_days_off' => $totalDaysOff,
                'employees_on_leave' => $leaveRequests->where('status', 'approved')->pluck('user_id')->unique()->count(),
            ],
            'team_breakdown' => $teamBreakdown,
            'type_breakdown' => $typeBreakdown,
        ]);
    }

    private function formatLeaveEvent($leaveRequest, $isOwnRequest = false, $isTeamView = false)
    {
        $startDate = Carbon::parse($leaveRequest->start_date);
        $endDate = Carbon::parse($leaveRequest->end_date);
        $days = $startDate->diffInDays($endDate) + 1;

        // Color coding for different leave types and statuses
        $colors = [
            'pending' => ['bg' => '#fef3c7', 'border' => '#f59e0b', 'text' => '#92400e'],
            'approved' => ['bg' => '#dcfce7', 'border' => '#16a34a', 'text' => '#166534'],
            'rejected' => ['bg' => '#fee2e2', 'border' => '#dc2626', 'text' => '#991b1b'],
        ];

        $typeColors = [
            'annual' => ['bg' => '#dbeafe', 'border' => '#3b82f6', 'text' => '#1e40af'],
            'sick' => ['bg' => '#fef2f2', 'border' => '#ef4444', 'text' => '#991b1b'],
            'personal' => ['bg' => '#f3e8ff', 'border' => '#8b5cf6', 'text' => '#6b21a8'],
            'maternity' => ['bg' => '#fce7f3', 'border' => '#ec4899', 'text' => '#be185d'],
            'paternity' => ['bg' => '#ecfdf5', 'border' => '#10b981', 'text' => '#047857'],
            'bereavement' => ['bg' => '#f9fafb', 'border' => '#6b7280', 'text' => '#374151'],
        ];

        $statusColor = $colors[$leaveRequest->status] ?? $colors['pending'];
        $typeColor = $typeColors[$leaveRequest->type] ?? $typeColors['annual'];

        return [
            'id' => $leaveRequest->id,
            'title' => $isTeamView ? $leaveRequest->user->name . ' - ' . ucfirst($leaveRequest->type) : ucfirst($leaveRequest->type) . ' Leave',
            'start' => $leaveRequest->start_date,
            'end' => Carbon::parse($leaveRequest->end_date)->addDay()->toDateString(), // Add one day for FullCalendar end date
            'allDay' => true,
            'status' => $leaveRequest->status,
            'type' => ucfirst($leaveRequest->type),
            'reason' => $leaveRequest->reason,
            'days' => $days,
            'user_name' => $leaveRequest->user->name,
            'team_name' => $leaveRequest->user->team?->name,
            'backgroundColor' => $statusColor['bg'],
            'borderColor' => $statusColor['border'],
            'textColor' => $statusColor['text'],
            'extendedProps' => [
                'status' => $leaveRequest->status,
                'type' => $leaveRequest->type,
                'isOwnRequest' => $isOwnRequest,
                'appliedAt' => $leaveRequest->created_at->format('M j, Y'),
                'approvedAt' => $leaveRequest->approved_at?->format('M j, Y'),
                'approverName' => $leaveRequest->approver?->name,
                'userId' => $leaveRequest->user_id,
                'userName' => $leaveRequest->user->name,
                'teamName' => $leaveRequest->user->team?->name,
                'canApprove' => auth()->user()->can('approve_leave_request') && $leaveRequest->status === 'pending',
                'canReject' => auth()->user()->can('reject_leave_request') && $leaveRequest->status === 'pending',
            ],
        ];
    }
}
