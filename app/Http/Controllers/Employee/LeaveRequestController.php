<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveRequestResource;
use App\Http\Resources\LeaveRequestCollection;
use App\Models\LeaveRequest;
use App\Services\LeaveTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $user = Auth::user();
    
    // Build the query
    $query = LeaveRequest::with(['user', 'approver'])
        ->where('user_id', $user->id);

    // Apply filters
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    if ($request->filled('year')) {
        $query->whereYear('start_date', $request->year);
    }

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('reason', 'like', '%' . $request->search . '%')
              ->orWhere('type', 'like', '%' . $request->search . '%');
        });
    }

    // Get paginated results
    $leaveRequests = $query->orderBy('created_at', 'desc')
        ->paginate(10)
        ->withQueryString();

    // Transform the paginated results using the resource
    $leaveRequestsCollection = LeaveRequestResource::collection($leaveRequests);

    // Get summary stats
    $stats = [
        'total' => LeaveRequest::where('user_id', $user->id)->count(),
        'pending' => LeaveRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
        'approved' => LeaveRequest::where('user_id', $user->id)->where('status', 'approved')->count(),
        'rejected' => LeaveRequest::where('user_id', $user->id)->where('status', 'rejected')->count(),
        'days_this_year' => LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->get()
            ->sum('days'),
    ];

    // Get available years for filter
    $availableYears = LeaveRequest::where('user_id', $user->id)
        ->selectRaw('DISTINCT YEAR(start_date) as year')
        ->orderBy('year', 'desc')
        ->pluck('year')
        ->toArray();

    // Leave types for filter
    $leaveTypes = LeaveTypeService::getSelectOptions();

    return Inertia::render('employee/leave-requests/Index', [
        'leaveRequests' => $leaveRequestsCollection,
        'stats' => $stats,
        'filters' => $request->only(['status', 'type', 'year', 'search']),
        'availableYears' => $availableYears,
        'leaveTypes' => $leaveTypes,
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    $user = Auth::user();
    $currentYear = now()->year;
    
    // Get detailed leave types with all properties
    $leaveTypes = LeaveTypeService::getDetailedOptions();
    
    // Calculate leave balance for each type
    $leaveBalance = collect(['annual', 'sick', 'personal', 'emergency', 'unpaid'])->map(function ($type) use ($user, $currentYear) {
        $approvedRequests = LeaveRequest::where('user_id', $user->id)
            ->where('type', $type)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->get();

        $pendingRequests = LeaveRequest::where('user_id', $user->id)
            ->where('type', $type)
            ->where('status', 'pending')
            ->whereYear('start_date', $currentYear)
            ->get();

        $usedDays = $approvedRequests->sum('days');
        $pendingDays = $pendingRequests->sum('days');
        
        // Default allowances - could be enhanced to come from leave types or company settings
        $allowances = [
            'annual' => 25,
            'sick' => 10,
            'personal' => 5,
            'emergency' => 3,
            'unpaid' => 365,
        ];
        
        $totalDays = $allowances[$type] ?? 0;
        $remainingDays = max(0, $totalDays - $usedDays);
        $percentageUsed = $totalDays > 0 ? ($usedDays / $totalDays) * 100 : 0;

        return [
            'type' => $type,
            'total_days' => $totalDays,
            'used_days' => $usedDays,
            'pending_days' => $pendingDays,
            'remaining_days' => $remainingDays,
            'percentage_used' => round($percentageUsed, 1),
        ];
    })->toArray();
    
    // Get user profile information
    $userProfile = [
        'employment_start_date' => $user->created_at->format('Y-m-d'), // Assuming created_at as employment start
        'gender' => $user->gender ?? 'not_specified', // Add gender field to user migration if needed
        'team_name' => $user->team?->name,
    ];
    
    return Inertia::render('employee/leave-requests/Create', [
        'leaveTypes' => $leaveTypes,
        'leaveBalance' => $leaveBalance,
        'userProfile' => $userProfile,
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $user = Auth::user();
    $validTypes = implode(',', LeaveTypeService::getTypeKeys());
    
    // Basic validation
    $request->validate([
      'start_date' => 'required|date|after_or_equal:today',
      'end_date' => 'required|date|after_or_equal:start_date',
      'type' => "required|in:{$validTypes}",
      'reason' => 'required|string|min:10|max:500',
      'documentation' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB max
    ]);

    // Get detailed leave type information
    $detailedTypes = collect(LeaveTypeService::getDetailedOptions())
        ->keyBy('value');
    $selectedType = $detailedTypes[$request->type] ?? null;
    
    if (!$selectedType) {
        return back()->withErrors(['type' => 'Invalid leave type selected.']);
    }

    // Calculate days
    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);
    $requestedDays = $startDate->diffInDays($endDate) + 1;

    // Validate duration constraints
    if ($requestedDays < $selectedType['min_duration']) {
        return back()->withErrors(['end_date' => "Minimum duration for {$selectedType['label']} is {$selectedType['min_duration']} days."]);
    }
    
    if ($requestedDays > $selectedType['max_duration']) {
        return back()->withErrors(['end_date' => "Maximum duration for {$selectedType['label']} is {$selectedType['max_duration']} days."]);
    }

    // Check employment eligibility
    if ($selectedType['min_employment_months']) {
        $employmentMonths = $user->created_at->diffInMonths(now());
        if ($employmentMonths < $selectedType['min_employment_months']) {
            return back()->withErrors(['type' => "You need {$selectedType['min_employment_months']} months of employment for this leave type. You have {$employmentMonths} months."]);
        }
    }

    // Check leave balance
    $currentYear = now()->year;
    $usedDays = LeaveRequest::where('user_id', $user->id)
        ->where('type', $request->type)
        ->where('status', 'approved')
        ->whereYear('start_date', $currentYear)
        ->sum('days');

    $allowances = [
        'annual' => 25,
        'sick' => 10,
        'personal' => 5,
        'emergency' => 3,
        'unpaid' => 365,
    ];
    
    $totalAllowed = $allowances[$request->type] ?? 0;
    $remainingDays = $totalAllowed - $usedDays;
    
    if ($requestedDays > $remainingDays) {
        return back()->withErrors(['end_date' => "You only have {$remainingDays} days remaining for {$selectedType['label']}."]);
    }

    // Check for overlapping requests
    $overlapping = LeaveRequest::where('user_id', $user->id)
        ->where('status', '!=', 'rejected')
        ->where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q) use ($startDate, $endDate) {
                      $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                  });
        })
        ->exists();

    if ($overlapping) {
        return back()->withErrors(['start_date' => 'You already have a leave request for overlapping dates.']);
    }

    // Handle file upload if documentation is required
    $documentationPath = null;
    if ($selectedType['requires_documentation'] && $request->hasFile('documentation')) {
        $documentationPath = $request->file('documentation')->store('leave-documents', 'private');
    } elseif ($selectedType['requires_documentation']) {
        return back()->withErrors(['documentation' => 'Documentation is required for this leave type.']);
    }

    // Create the leave request
    $leaveRequest = Auth::user()->leaveRequests()->create([
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'type' => $request->type,
        'reason' => $request->reason,
        'status' => 'pending',
        'company_id' => Auth::user()->company_id,
        'documentation_path' => $documentationPath,
    ]);

    return redirect()->route('leave-requests.index')->with('success', 'Leave request submitted successfully! You will be notified once it\'s reviewed.');
  }

  /**
   * Display the specified resource.
   */
  public function show(LeaveRequest $leaveRequest)
  {
    // Ensure user can only view their own requests
    if ($leaveRequest->user_id !== Auth::id()) {
        abort(403);
    }

    $leaveRequest->load(['user', 'approver']);

    return Inertia::render('employee/leave-requests/Show', [
        'leaveRequest' => new LeaveRequestResource($leaveRequest)
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(LeaveRequest $leaveRequest)
  {
    // Ensure user can only edit their own pending requests
    if ($leaveRequest->user_id !== Auth::id() || $leaveRequest->status !== 'pending') {
        abort(403);
    }

    $leaveTypes = LeaveTypeService::getSelectOptions();

    return Inertia::render('employee/leave-requests/Edit', [
        'leaveRequest' => new LeaveRequestResource($leaveRequest),
        'leaveTypes' => $leaveTypes,
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, LeaveRequest $leaveRequest)
  {
    // Ensure user can only update their own pending requests
    if ($leaveRequest->user_id !== Auth::id() || $leaveRequest->status !== 'pending') {
        abort(403);
    }

    $validTypes = implode(',', LeaveTypeService::getTypeKeys());

    $request->validate([
      'start_date' => 'required|date|after_or_equal:today',
      'end_date' => 'required|date|after_or_equal:start_date',
      'type' => "required|in:{$validTypes}",
      'reason' => 'nullable|string|max:500',
    ]);

    $leaveRequest->update([
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'type' => $request->type,
        'reason' => $request->reason,
    ]);

    return redirect()->route('leave-requests.index')->with('success', 'Leave request updated successfully!');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(LeaveRequest $leaveRequest)
  {
    // Ensure user can only delete their own pending requests
    if ($leaveRequest->user_id !== Auth::id() || !in_array($leaveRequest->status, ['pending', 'approved'])) {
        abort(403);
    }

    // Don't allow deletion if leave has already started
    if ($leaveRequest->start_date->isPast()) {
        return back()->with('error', 'Cannot cancel leave that has already started.');
    }

    $leaveRequest->delete();

    return redirect()->route('leave-requests.index')->with('success', 'Leave request cancelled successfully!');
  }
}
