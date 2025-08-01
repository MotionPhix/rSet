<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaveRequestController extends Controller
{
    /**
     * Display all leave requests for the company
     */
    public function index()
    {
        $leaveRequests = LeaveRequest::whereHas('user', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        })
        ->with(['user', 'leaveType'])
        ->latest()
        ->paginate(20);

        return Inertia::render('admin/leave-requests/Index', [
            'leaveRequests' => $leaveRequests
        ]);
    }

    /**
     * Show a specific leave request
     */
    public function show(LeaveRequest $leaveRequest)
    {
        // Ensure the leave request belongs to the admin's company
        abort_if($leaveRequest->user->company_id !== auth()->user()->company_id, 403);

        $leaveRequest->load(['user', 'leaveType']);

        return Inertia::render('admin/leave-requests/Show', [
            'leaveRequest' => $leaveRequest
        ]);
    }

    /**
     * Approve a leave request
     */
    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        // Ensure the leave request belongs to the admin's company
        abort_if($leaveRequest->user->company_id !== auth()->user()->company_id, 403);

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes
        ]);

        return redirect()->route('admin.leave-requests.index')
            ->with('success', 'Leave request approved successfully.');
    }

    /**
     * Reject a leave request
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        // Ensure the leave request belongs to the admin's company
        abort_if($leaveRequest->user->company_id !== auth()->user()->company_id, 403);

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_notes' => $request->rejection_reason
        ]);

        return redirect()->route('admin.leave-requests.index')
            ->with('success', 'Leave request rejected.');
    }

    /**
     * Update a leave request (admin can edit details)
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:pending,approved,rejected',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        // Ensure the leave request belongs to the admin's company
        abort_if($leaveRequest->user->company_id !== auth()->user()->company_id, 403);

        $leaveRequest->update($request->only([
            'start_date',
            'end_date', 
            'status',
            'admin_notes'
        ]));

        return redirect()->route('admin.leave-requests.show', $leaveRequest)
            ->with('success', 'Leave request updated successfully.');
    }

    /**
     * Delete a leave request
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        // Ensure the leave request belongs to the admin's company
        abort_if($leaveRequest->user->company_id !== auth()->user()->company_id, 403);

        $leaveRequest->delete();

        return redirect()->route('admin.leave-requests.index')
            ->with('success', 'Leave request deleted successfully.');
    }
}
