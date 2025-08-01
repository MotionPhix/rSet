<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LeaveType;

class LeaveTypeController extends Controller
{
    /**
     * Show the leave types settings page.
     */
    public function index(Request $request)
    {
        $this->authorize('view_leave_types');

        $user = $request->user();
        $company = $user->company;

        $leaveTypes = LeaveType::where('company_id', $company->id)
            ->get();

        return Inertia::render('settings/leave-types/Index', [
            'leaveTypes' => $leaveTypes,
            'canCreate' => $user->can('create_leave_types'),
            'canEdit' => $user->can('edit_leave_types'),
            'canDelete' => $user->can('delete_leave_types'),
        ]);
    }

    /**
     * Store a new leave type.
     */
    public function store(Request $request)
    {
        $this->authorize('create_leave_types');

        $user = $request->user();
        $company = $user->company;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'days_allowed' => 'required|integer|min:1',
            'min_duration' => 'required|integer|min:1',
            'max_duration' => 'required|integer|gte:min_duration',
            'allow_custom_duration' => 'boolean',
            'gender' => 'nullable|string|in:male,female,all',
            'min_employment_months' => 'nullable|integer|min:0',
            'cooldown_days' => 'nullable|integer|min:0',
            'max_usage_per_year' => 'nullable|integer|min:1',
            'full_pay_days' => 'required|integer|min:0',
            'half_pay_days' => 'required|integer|min:0',
            'requires_approval' => 'boolean',
            'approvers' => 'nullable|array',
            'requires_documentation' => 'boolean',
            'documentation_type' => 'nullable|string|max:255',
        ]);

        $validated['company_id'] = $company->id;

        LeaveType::create($validated);

        return back()->with('success', 'Leave type created successfully.');
    }

    /**
     * Update a leave type.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $this->authorize('edit_leave_types');

        // Check if leave type belongs to user's company
        if ($leaveType->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'days_allowed' => 'required|integer|min:1',
            'min_duration' => 'required|integer|min:1',
            'max_duration' => 'required|integer|gte:min_duration',
            'allow_custom_duration' => 'boolean',
            'gender' => 'nullable|string|in:male,female,all',
            'min_employment_months' => 'nullable|integer|min:0',
            'cooldown_days' => 'nullable|integer|min:0',
            'max_usage_per_year' => 'nullable|integer|min:1',
            'full_pay_days' => 'required|integer|min:0',
            'half_pay_days' => 'required|integer|min:0',
            'requires_approval' => 'boolean',
            'approvers' => 'nullable|array',
            'requires_documentation' => 'boolean',
            'documentation_type' => 'nullable|string|max:255',
        ]);

        $leaveType->update($validated);

        return back()->with('success', 'Leave type updated successfully.');
    }

    /**
     * Delete a leave type.
     */
    public function destroy(Request $request, LeaveType $leaveType)
    {
        $this->authorize('delete_leave_types');

        // Check if leave type belongs to user's company
        if ($leaveType->company_id !== $request->user()->company_id) {
            abort(403);
        }

        // Check if there are leave requests associated with this type
        $hasLeaveRequests = $leaveType->leaveRequests()->exists();
        if ($hasLeaveRequests) {
            return back()->with('error', 'Cannot delete a leave type that has leave requests. Consider deactivating it instead.');
        }

        $leaveType->delete();

        return back()->with('success', 'Leave type deleted successfully.');
    }
}
