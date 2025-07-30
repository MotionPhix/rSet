<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index()
    {
        $this->authorize('view reports');

        $user = auth()->user();
        $company = $user->company;

        // Get high-level statistics
        $stats = [
            'total_leave_requests' => LeaveRequest::whereHas('user', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })->count(),
            'pending_leave_requests' => LeaveRequest::whereHas('user', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })->where('status', 'pending')->count(),
            'approved_leave_requests' => LeaveRequest::whereHas('user', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })->where('status', 'approved')->count(),
            'rejected_leave_requests' => LeaveRequest::whereHas('user', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })->where('status', 'rejected')->count(),
        ];

        return Inertia::render('admin/reports/Index', [
            'stats' => $stats,
            'reportTypes' => [
                ['id' => 'leave-by-type', 'name' => 'Leave by Type'],
                ['id' => 'leave-by-team', 'name' => 'Leave by Team'],
                ['id' => 'leave-by-employee', 'name' => 'Leave by Employee'],
                ['id' => 'leave-calendar', 'name' => 'Leave Calendar'],
                ['id' => 'leave-summary', 'name' => 'Leave Summary'],
            ],
        ]);
    }

    public function generate(Request $request)
    {
        $this->authorize('view reports');

        $request->validate([
            'report_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'team_id' => 'nullable|exists:teams,id',
            'user_id' => 'nullable|exists:users,id',
            'leave_type_id' => 'nullable|exists:leave_types,id',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        $user = auth()->user();
        $company = $user->company;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Base query with company context
        $query = LeaveRequest::whereHas('user', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        })->whereBetween('start_date', [$startDate, $endDate])
           ->orWhereBetween('end_date', [$startDate, $endDate]);

        // Apply filters
        if ($request->team_id) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('team_id', $request->team_id);
            });
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->leave_type_id) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Get the data
        $leaveRequests = $query->with(['user', 'leaveType'])->get();

        // Prepare report data based on report type
        $reportData = [];
        $chartData = [];

        switch ($request->report_type) {
            case 'leave-by-type':
                $leaveTypes = LeaveType::where('company_id', $company->id)->get();

                foreach ($leaveTypes as $leaveType) {
                    $count = $leaveRequests->where('leave_type_id', $leaveType->id)->count();
                    $days = $leaveRequests->where('leave_type_id', $leaveType->id)->sum('duration');

                    $reportData[] = [
                        'type' => $leaveType->name,
                        'count' => $count,
                        'days' => $days,
                    ];

                    // For chart
                    $chartData[] = [
                        'name' => $leaveType->name,
                        'value' => $days,
                    ];
                }
                break;

            case 'leave-by-team':
                $teams = Team::where('company_id', $company->id)->get();

                foreach ($teams as $team) {
                    $teamLeaveRequests = $leaveRequests->filter(function ($leaveRequest) use ($team) {
                        return $leaveRequest->user->team_id === $team->id;
                    });

                    $count = $teamLeaveRequests->count();
                    $days = $teamLeaveRequests->sum('duration');

                    $reportData[] = [
                        'team' => $team->name,
                        'count' => $count,
                        'days' => $days,
                    ];

                    // For chart
                    $chartData[] = [
                        'name' => $team->name,
                        'value' => $days,
                    ];
                }
                break;

            case 'leave-by-employee':
                $users = User::where('company_id', $company->id)->get();

                foreach ($users as $employee) {
                    $employeeLeaveRequests = $leaveRequests->where('user_id', $employee->id);

                    $count = $employeeLeaveRequests->count();
                    $days = $employeeLeaveRequests->sum('duration');

                    if ($count > 0) {
                        $reportData[] = [
                            'employee' => $employee->name,
                            'email' => $employee->email,
                            'count' => $count,
                            'days' => $days,
                        ];
                    }
                }

                // Sort by days taken (descending)
                usort($reportData, function ($a, $b) {
                    return $b['days'] <=> $a['days'];
                });

                // For chart (top 10 only)
                $topEmployees = array_slice($reportData, 0, 10);
                foreach ($topEmployees as $employee) {
                    $chartData[] = [
                        'name' => $employee['employee'],
                        'value' => $employee['days'],
                    ];
                }
                break;

            case 'leave-calendar':
                // Group by date
                $dateMap = [];
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);
                $currentDate = $startDate->copy();

                while ($currentDate->lte($endDate)) {
                    $dateKey = $currentDate->format('Y-m-d');
                    $dateMap[$dateKey] = 0;
                    $currentDate->addDay();
                }

                foreach ($leaveRequests as $leaveRequest) {
                    $start = Carbon::parse($leaveRequest->start_date);
                    $end = Carbon::parse($leaveRequest->end_date);
                    $current = $start->copy();

                    while ($current->lte($end)) {
                        $dateKey = $current->format('Y-m-d');
                        if (isset($dateMap[$dateKey])) {
                            $dateMap[$dateKey]++;
                        }
                        $current->addDay();
                    }
                }

                foreach ($dateMap as $date => $count) {
                    $reportData[] = [
                        'date' => $date,
                        'count' => $count,
                    ];

                    // For chart
                    $chartData[] = [
                        'name' => Carbon::parse($date)->format('M d'),
                        'value' => $count,
                    ];
                }
                break;

            case 'leave-summary':
                // Summary statistics
                $reportData = [
                    'total_leave_requests' => $leaveRequests->count(),
                    'total_days' => $leaveRequests->sum('duration'),
                    'approved_requests' => $leaveRequests->where('status', 'approved')->count(),
                    'approved_days' => $leaveRequests->where('status', 'approved')->sum('duration'),
                    'pending_requests' => $leaveRequests->where('status', 'pending')->count(),
                    'pending_days' => $leaveRequests->where('status', 'pending')->sum('duration'),
                    'rejected_requests' => $leaveRequests->where('status', 'rejected')->count(),
                    'rejected_days' => $leaveRequests->where('status', 'rejected')->sum('duration'),
                ];

                // For chart
                $chartData = [
                    ['name' => 'Approved', 'value' => $reportData['approved_days']],
                    ['name' => 'Pending', 'value' => $reportData['pending_days']],
                    ['name' => 'Rejected', 'value' => $reportData['rejected_days']],
                ];
                break;
        }

        return response()->json([
            'report_type' => $request->report_type,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'data' => $reportData,
            'chart_data' => $chartData,
        ]);
    }

    public function export(Request $request)
    {
        $this->authorize('create reports');

        $request->validate([
            'report_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'team_id' => 'nullable|exists:teams,id',
            'user_id' => 'nullable|exists:users,id',
            'leave_type_id' => 'nullable|exists:leave_types,id',
            'status' => 'nullable|in:pending,approved,rejected',
            'format' => 'required|in:csv,excel,pdf',
        ]);

        $user = auth()->user();
        $company = $user->company;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Get report data using the same logic as in generate method
        $query = LeaveRequest::whereHas('user', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        })->whereBetween('start_date', [$startDate, $endDate])
           ->orWhereBetween('end_date', [$startDate, $endDate]);

        // Apply filters
        if ($request->team_id) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('team_id', $request->team_id);
            });
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->leave_type_id) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->with(['user', 'leaveType'])->get();

        // Generate filename
        $filename = Str::slug($company->name) . '_' .
                   Str::slug($request->report_type) . '_' .
                   $startDate->format('Y-m-d') . '_to_' .
                   $endDate->format('Y-m-d');

        // Prepare data for export based on report type
        $exportData = [];
        $headers = [];

        switch ($request->report_type) {
            case 'leave-by-type':
                $headers = ['Leave Type', 'Request Count', 'Days Taken'];
                $leaveTypes = LeaveType::where('company_id', $company->id)->get();

                foreach ($leaveTypes as $leaveType) {
                    $count = $leaveRequests->where('leave_type_id', $leaveType->id)->count();
                    $days = $leaveRequests->where('leave_type_id', $leaveType->id)->sum('duration');

                    $exportData[] = [
                        'type' => $leaveType->name,
                        'count' => $count,
                        'days' => $days,
                    ];
                }
                break;

            case 'leave-by-team':
                $headers = ['Team', 'Request Count', 'Days Taken'];
                $teams = Team::where('company_id', $company->id)->get();

                foreach ($teams as $team) {
                    $teamLeaveRequests = $leaveRequests->filter(function ($leaveRequest) use ($team) {
                        return $leaveRequest->user->team_id === $team->id;
                    });

                    $count = $teamLeaveRequests->count();
                    $days = $teamLeaveRequests->sum('duration');

                    $exportData[] = [
                        'team' => $team->name,
                        'count' => $count,
                        'days' => $days,
                    ];
                }
                break;

            case 'leave-by-employee':
                $headers = ['Employee', 'Email', 'Request Count', 'Days Taken'];
                $users = User::where('company_id', $company->id)->get();

                foreach ($users as $employee) {
                    $employeeLeaveRequests = $leaveRequests->where('user_id', $employee->id);

                    $count = $employeeLeaveRequests->count();
                    $days = $employeeLeaveRequests->sum('duration');

                    if ($count > 0) {
                        $exportData[] = [
                            'employee' => $employee->name,
                            'email' => $employee->email,
                            'count' => $count,
                            'days' => $days,
                        ];
                    }
                }
                break;

            case 'leave-calendar':
                $headers = ['Date', 'Employees on Leave'];
                $dateMap = [];
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);
                $currentDate = $startDate->copy();

                while ($currentDate->lte($endDate)) {
                    $dateKey = $currentDate->format('Y-m-d');
                    $dateMap[$dateKey] = 0;
                    $currentDate->addDay();
                }

                foreach ($leaveRequests as $leaveRequest) {
                    $start = Carbon::parse($leaveRequest->start_date);
                    $end = Carbon::parse($leaveRequest->end_date);
                    $current = $start->copy();

                    while ($current->lte($end)) {
                        $dateKey = $current->format('Y-m-d');
                        if (isset($dateMap[$dateKey])) {
                            $dateMap[$dateKey]++;
                        }
                        $current->addDay();
                    }
                }

                foreach ($dateMap as $date => $count) {
                    $exportData[] = [
                        'date' => $date,
                        'count' => $count,
                    ];
                }
                break;

            case 'leave-summary':
                $headers = ['Metric', 'Requests', 'Days'];
                $exportData = [
                    [
                        'metric' => 'Total',
                        'requests' => $leaveRequests->count(),
                        'days' => $leaveRequests->sum('duration'),
                    ],
                    [
                        'metric' => 'Approved',
                        'requests' => $leaveRequests->where('status', 'approved')->count(),
                        'days' => $leaveRequests->where('status', 'approved')->sum('duration'),
                    ],
                    [
                        'metric' => 'Pending',
                        'requests' => $leaveRequests->where('status', 'pending')->count(),
                        'days' => $leaveRequests->where('status', 'pending')->sum('duration'),
                    ],
                    [
                        'metric' => 'Rejected',
                        'requests' => $leaveRequests->where('status', 'rejected')->count(),
                        'days' => $leaveRequests->where('status', 'rejected')->sum('duration'),
                    ],
                ];
                break;

            default:
                // Detailed leave requests as default
                $headers = ['Employee', 'Leave Type', 'Start Date', 'End Date', 'Duration', 'Status'];
                foreach ($leaveRequests as $leaveRequest) {
                    $exportData[] = [
                        'employee' => $leaveRequest->user->name,
                        'type' => $leaveRequest->leaveType->name,
                        'start_date' => $leaveRequest->start_date,
                        'end_date' => $leaveRequest->end_date,
                        'duration' => $leaveRequest->duration,
                        'status' => $leaveRequest->status,
                    ];
                }
        }

        // Create export based on requested format
        switch ($request->format) {
            case 'csv':
                $output = fopen('php://temp', 'w');
                fputcsv($output, $headers);

                foreach ($exportData as $row) {
                    fputcsv($output, $row);
                }

                rewind($output);
                $csv = stream_get_contents($output);
                fclose($output);

                return response($csv)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}.csv\"");

            case 'excel':
                // For a real implementation, you would use a library like PhpSpreadsheet
                // For now, we'll return a CSV with an Excel mime type
                $output = fopen('php://temp', 'w');
                fputcsv($output, $headers);

                foreach ($exportData as $row) {
                    fputcsv($output, $row);
                }

                rewind($output);
                $excel = stream_get_contents($output);
                fclose($output);

                return response($excel)
                    ->header('Content-Type', 'application/vnd.ms-excel')
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}.xls\"");

            case 'pdf':
                // For a real implementation, you would use a library like DomPDF or Snappy
                // For now, we'll return a simple text representation
                $pdf = "Report: " . ucfirst(str_replace('-', ' ', $request->report_type)) . "\n";
                $pdf .= "Period: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . "\n\n";

                $pdf .= implode(", ", $headers) . "\n";
                $pdf .= str_repeat("-", 80) . "\n";

                foreach ($exportData as $row) {
                    $pdf .= implode(", ", $row) . "\n";
                }

                return response($pdf)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}.pdf\"");
        }

        return response()->json(['error' => 'Unsupported export format'], 400);
    }
}
