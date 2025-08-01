<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveTypeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get current date or requested date
        $date = $request->get('date', now()->format('Y-m-d'));
        $currentDate = Carbon::parse($date);
        
        // Get view mode (month or week)
        $view = $request->get('view', 'month');
        
        return Inertia::render('employee/calendar/Index', [
            'currentDate' => $currentDate->format('Y-m-d'),
            'view' => $view,
            'calendarData' => $this->getCalendarData($user, $currentDate, $view),
            'teamData' => $this->getTeamCalendarData($user, $currentDate, $view),
            'leaveTypes' => $this->getLeaveTypes(),
            'userPermissions' => [
                'canViewTeam' => $user->hasRole('manager') || $user->can('view_team_leave_requests'),
                'canCreateLeave' => $user->can('create_leave_request'),
                'canApproveLeave' => $user->hasRole('manager') || $user->can('approve_leave_request'),
            ],
        ]);
    }
    
    public function getEvents(Request $request)
    {
        $user = auth()->user();
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        
        $events = $this->getEventsInRange($user, $start, $end);
        
        return response()->json($events);
    }
    
    public function getTeamEvents(Request $request)
    {
        $user = auth()->user();
        
        // Check permissions
        if (!$user->hasRole('manager') && !$user->can('view_team_leave_requests')) {
            abort(403, 'Unauthorized to view team calendar');
        }
        
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        
        $events = $this->getTeamEventsInRange($user, $start, $end);
        
        return response()->json($events);
    }
    
    private function getCalendarData($user, $currentDate, $view)
    {
        if ($view === 'week') {
            $start = $currentDate->clone()->startOfWeek();
            $end = $currentDate->clone()->endOfWeek();
        } else {
            $start = $currentDate->clone()->startOfMonth()->startOfWeek();
            $end = $currentDate->clone()->endOfMonth()->endOfWeek();
        }
        
        return $this->getEventsInRange($user, $start, $end);
    }
    
    private function getTeamCalendarData($user, $currentDate, $view)
    {
        if (!$user->hasRole('manager') && !$user->can('view_team_leave_requests')) {
            return [];
        }
        
        if ($view === 'week') {
            $start = $currentDate->clone()->startOfWeek();
            $end = $currentDate->clone()->endOfWeek();
        } else {
            $start = $currentDate->clone()->startOfMonth()->startOfWeek();
            $end = $currentDate->clone()->endOfMonth()->endOfWeek();
        }
        
        return $this->getTeamEventsInRange($user, $start, $end);
    }
    
    private function getEventsInRange($user, $start, $end)
    {
        $requests = LeaveRequest::where('user_id', $user->id)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                      ->orWhereBetween('end_date', [$start, $end])
                      ->orWhere(function ($q) use ($start, $end) {
                          $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                      });
            })
            ->with(['user'])
            ->get();
            
        return $requests->map(function ($request) {
            return $this->formatLeaveEvent($request);
        });
    }
    
    private function getTeamEventsInRange($user, $start, $end)
    {
        if (!$user->team_id) {
            return [];
        }
        
        $requests = LeaveRequest::whereHas('user', function ($query) use ($user) {
                $query->where('team_id', $user->team_id);
            })
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                      ->orWhereBetween('end_date', [$start, $end])
                      ->orWhere(function ($q) use ($start, $end) {
                          $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                      });
            })
            ->with(['user'])
            ->get();
            
        return $requests->map(function ($request) {
            return $this->formatLeaveEvent($request, true);
        });
    }
    
    private function formatLeaveEvent($request, $includeUserName = false)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $days = $startDate->diffInDays($endDate) + 1;
        
        $title = $includeUserName 
            ? "{$request->user->name} - {$request->type}" 
            : $request->type;
            
        if ($days > 1) {
            $title .= " ({$days} days)";
        }
        
        return [
            'id' => $request->id,
            'title' => $title,
            'start' => $request->start_date,
            'end' => Carbon::parse($request->end_date)->addDay()->format('Y-m-d'), // Full calendar needs exclusive end date
            'type' => $request->type,
            'status' => $request->status,
            'reason' => $request->reason,
            'user_name' => $request->user->name,
            'user_id' => $request->user_id,
            'days' => $days,
            'color' => $this->getStatusColor($request->status),
            'backgroundColor' => $this->getStatusBackgroundColor($request->status),
            'borderColor' => $this->getStatusBorderColor($request->status),
            'allDay' => true,
            'extendedProps' => [
                'status' => $request->status,
                'type' => $request->type,
                'reason' => $request->reason,
                'appliedAt' => $request->created_at->format('M j, Y'),
                'isOwnRequest' => $request->user_id === auth()->id(),
            ]
        ];
    }
    
    private function getStatusColor($status)
    {
        switch ($status) {
            case 'approved':
                return '#10b981'; // green
            case 'pending':
                return '#f59e0b'; // yellow
            case 'rejected':
                return '#ef4444'; // red
            default:
                return '#6b7280'; // gray
        }
    }
    
    private function getStatusBackgroundColor($status)
    {
        switch ($status) {
            case 'approved':
                return '#dcfce7'; // light green
            case 'pending':
                return '#fef3c7'; // light yellow
            case 'rejected':
                return '#fee2e2'; // light red
            default:
                return '#f3f4f6'; // light gray
        }
    }
    
    private function getStatusBorderColor($status)
    {
        switch ($status) {
            case 'approved':
                return '#10b981';
            case 'pending':
                return '#f59e0b';
            case 'rejected':
                return '#ef4444';
            default:
                return '#6b7280';
        }
    }
    
    private function getLeaveTypes()
    {
        return LeaveTypeService::getSelectOptions();
    }
}
