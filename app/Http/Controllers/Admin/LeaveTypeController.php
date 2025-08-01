<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of leave types for the company
     */
    public function index()
    {
        $leaveTypes = LeaveType::where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/settings/leave-types/Index', [
            'leaveTypes' => $leaveTypes
        ]);
    }

    /**
     * Store a new leave type
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'days_per_year' => 'required|integer|min:0|max:365',
            'is_active' => 'boolean',
            'color' => 'required|string|max:7' // hex color
        ]);

        LeaveType::create([
            'name' => $request->name,
            'description' => $request->description,
            'days_per_year' => $request->days_per_year,
            'is_active' => $request->boolean('is_active', true),
            'color' => $request->color,
            'company_id' => auth()->user()->company_id
        ]);

        return redirect()->route('admin.settings.leave-types')
            ->with('success', 'Leave type created successfully.');
    }

    /**
     * Update a leave type
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        // Ensure leave type belongs to the admin's company
        abort_if($leaveType->company_id !== auth()->user()->company_id, 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'days_per_year' => 'required|integer|min:0|max:365',
            'is_active' => 'boolean',
            'color' => 'required|string|max:7'
        ]);

        $leaveType->update($request->only([
            'name', 'description', 'days_per_year', 'is_active', 'color'
        ]));

        return redirect()->route('admin.settings.leave-types')
            ->with('success', 'Leave type updated successfully.');
    }

    /**
     * Delete a leave type
     */
    public function destroy(LeaveType $leaveType)
    {
        // Ensure leave type belongs to the admin's company
        abort_if($leaveType->company_id !== auth()->user()->company_id, 403);

        // Check if leave type is being used
        if ($leaveType->leaveRequests()->exists()) {
            return redirect()->route('admin.settings.leave-types')
                ->with('error', 'Cannot delete leave type that has associated leave requests.');
        }

        $leaveType->delete();

        return redirect()->route('admin.settings.leave-types')
            ->with('success', 'Leave type deleted successfully.');
    }
}
