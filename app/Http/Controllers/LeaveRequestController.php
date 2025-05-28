<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    if (auth()->user()->role === 'manager') {
      $leaveRequests = LeaveRequest::forTeam(auth()->user()->team_id)->get();
    } else {
      $leaveRequests = auth()->user()->leaveRequests;
    }

    return view('leave-requests.index', compact('leaveRequests'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $request->validate([
      'start_date' => 'required|date',
      'end_date' => 'required|date|after:start_date',
      'type' => 'required|in:vacation,sick,unpaid',
    ]);

    auth()->user()->leaveRequests()->create($request->all());

    return redirect()->back()->with('success', 'Leave request submitted!');
  }

  /**
   * Display the specified resource.
   */
  public function show(LeaveRequest $leaveRequest)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(LeaveRequest $leaveRequest)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, LeaveRequest $leaveRequest)
  {
    $request->validate(['status' => 'required|in:approved,rejected']);

    $leaveRequest->update([
      'status' => $request->status,
      'approver_id' => auth()->id(),
    ]);

    // Optional: Send email notification
    return redirect()->back()->with('success', 'Leave request updated!');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(LeaveRequest $leaveRequest)
  {
    //
  }
}
