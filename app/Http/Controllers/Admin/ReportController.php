<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $this->authorize('viewReports', auth()->user());

        return Inertia::render('admin/reports/Index', [
            'stats' => $this->getBasicStats(),
            'recentLeaveRequests' => $this->getRecentLeaveRequests(),
        ]);
    }

    public function generate(Request $request)
    {
        $this->authorize('viewReports', auth()->user());

        $request->validate([
            'type' => 'required|in:leave_summary,team_performance,monthly_overview,yearly_overview',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'team_id' => 'nullable|exists:teams,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $data = match($request->type) {
            'leave_summary' => $this->generateLeaveSummaryReport($request),
            'team_performance' => $this->generateTeamPerformanceReport($request),
            'monthly_overview' => $this->generateMonthlyOverviewReport($request),
            'yearly_overview' => $this->generateYearlyOverviewReport($request),
        };

        return response()->json($data);
    }

    public function export(Request $request)
    {
        $this->authorize('exportData', auth()->user());

        $request->validate([
            'type' => 'required|in:csv,pdf,excel',
            'report_type' => 'required|in:leave_summary,team_performance,monthly_overview,yearly_overview',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Export logic will be implemented here
        return response()->json(['message' => 'Export will be available in your downloads shortly.']);
    }

    private function getBasicStats()
    {
        $companyId = auth()->user()->company_id;

        return [
            'total_employees' => User::where('company_id', $companyId)->count(),
            'pending_requests' => LeaveRequest::where('company_id', $companyId)
                ->where('status', 'pending')->count(),
            'approved_this_month' => LeaveRequest::where('company_id', $companyId)
                ->where('status', 'approved')
                ->whereMonth('created_at', now()->month)->count(),
            'total_teams' => Team::where('company_id', $companyId)->count(),
        ];
    }

    private function getRecentLeaveRequests()
    {
        return LeaveRequest::with(['user', 'leaveType'])
            ->where('company_id', auth()->user()->company_id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'user_name' => $request->user->name,
                    'leave_type' => $request->leaveType->name,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'status' => $request->status,
                    'days' => $request->days,
                ];
            });
    }

    private function generateLeaveSummaryReport(Request $request)
    {
        $query = LeaveRequest::with(['user', 'leaveType'])
            ->where('company_id', auth()->user()->company_id)
            ->whereBetween('start_date', [$request->start_date, $request->end_date]);

        if ($request->team_id) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('team_id', $request->team_id);
            });
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $leaveRequests = $query->get();

        return [
            'total_requests' => $leaveRequests->count(),
            'approved_requests' => $leaveRequests->where('status', 'approved')->count(),
            'pending_requests' => $leaveRequests->where('status', 'pending')->count(),
            'rejected_requests' => $leaveRequests->where('status', 'rejected')->count(),
            'total_days' => $leaveRequests->where('status', 'approved')->sum('days'),
            'by_leave_type' => $leaveRequests->groupBy('leaveType.name')->map->count(),
            'by_month' => $leaveRequests->groupBy(function ($item) {
                return Carbon::parse($item->start_date)->format('Y-m');
            })->map->count(),
        ];
    }

    private function generateTeamPerformanceReport(Request $request)
    {
        $teams = Team::with(['users'])
            ->where('company_id', auth()->user()->company_id)
            ->get();

        return $teams->map(function ($team) use ($request) {
            $leaveRequests = LeaveRequest::whereHas('user', function ($q) use ($team) {
                $q->where('team_id', $team->id);
            })->whereBetween('start_date', [$request->start_date, $request->end_date])->get();

            return [
                'team_name' => $team->name,
                'total_employees' => $team->users->count(),
                'total_leave_requests' => $leaveRequests->count(),
                'approved_leave_days' => $leaveRequests->where('status', 'approved')->sum('days'),
                'average_leave_per_employee' => $team->users->count() > 0
                    ? round($leaveRequests->where('status', 'approved')->sum('days') / $team->users->count(), 2)
                    : 0,
            ];
        });
    }

    private function generateMonthlyOverviewReport(Request $request)
    {
        $startDate = Carbon::parse($request->start_date)->startOfMonth();
        $endDate = Carbon::parse($request->end_date)->endOfMonth();

        $monthlyData = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $requests = LeaveRequest::where('company_id', auth()->user()->company_id)
                ->whereBetween('start_date', [$monthStart, $monthEnd])
                ->get();

            $monthlyData[] = [
                'month' => $current->format('Y-m'),
                'month_name' => $current->format('F Y'),
                'total_requests' => $requests->count(),
                'approved_requests' => $requests->where('status', 'approved')->count(),
                'total_days' => $requests->where('status', 'approved')->sum('days'),
            ];

            $current->addMonth();
        }

        return $monthlyData;
    }

    private function generateYearlyOverviewReport(Request $request)
    {
        $year = Carbon::parse($request->start_date)->year;

        $yearlyData = LeaveRequest::where('company_id', auth()->user()->company_id)
            ->whereYear('start_date', $year)
            ->get();

        return [
            'year' => $year,
            'total_requests' => $yearlyData->count(),
            'approved_requests' => $yearlyData->where('status', 'approved')->count(),
            'pending_requests' => $yearlyData->where('status', 'pending')->count(),
            'rejected_requests' => $yearlyData->where('status', 'rejected')->count(),
            'total_approved_days' => $yearlyData->where('status', 'approved')->sum('days'),
            'monthly_breakdown' => $yearlyData->groupBy(function ($item) {
                return Carbon::parse($item->start_date)->month;
            })->map(function ($monthData, $month) {
                return [
                    'month' => Carbon::create()->month($month)->format('F'),
                    'requests' => $monthData->count(),
                    'approved_days' => $monthData->where('status', 'approved')->sum('days'),
                ];
            })->values(),
        ];
    }
}
