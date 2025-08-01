<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Employee\ReportController;
use App\Http\Controllers\Employee\DashboardController;
use App\Http\Controllers\Employee\LeaveRequestController;
use App\Http\Controllers\Employee\CalendarController;

Route::middleware(['auth', 'verified', 'company.context'])->group(function () {

    // ============================================
    // EMPLOYEE DASHBOARD
    // ============================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================
    // EMPLOYEE REPORTS
    // ============================================
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('employee.reports.index');
        Route::get('/personal', [ReportController::class, 'personal'])->name('employee.reports.personal');

        // Team reports (only for managers)
        Route::middleware(['role:manager'])->group(function () {
            Route::get('/team', [ReportController::class, 'team'])->name('employee.reports.team');
        });
    });

    // ============================================
    // CALENDAR
    // ============================================
    Route::prefix('calendar')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
        
        // Team calendar (only for managers)
        Route::middleware(['role:manager'])->group(function () {
            Route::get('/team-events', [CalendarController::class, 'getTeamEvents'])->name('calendar.team-events');
        });
    });

    // ============================================
    // LEAVE REQUEST MANAGEMENT (Employee/Manager)
    // ============================================
    Route::middleware(['can:create_leave_request'])->group(function () {
        Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
        Route::get('/leave-requests/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
        Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
    });

    Route::middleware(['can:view_own_leave_request'])->group(function () {
        Route::get('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
    });

    Route::middleware(['can:edit_own_leave_request'])->group(function () {
        Route::get('/leave-requests/{leaveRequest}/edit', [LeaveRequestController::class, 'edit'])->name('leave-requests.edit');
        Route::put('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'update'])->name('leave-requests.update');
        Route::delete('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('leave-requests.destroy');
    });

    Route::middleware(['can:delete_own_leave_request'])->group(function () {
        Route::delete('/leave-requests/{leaveRequest}', function () {
            // Handle leave request deletion
            return redirect()->route('leave-requests.index');
        })->name('leave-requests.destroy');
    });

    Route::middleware(['can:cancel_leave_request'])->group(function () {
        Route::post('/leave-requests/{leaveRequest}/cancel', function () {
            // Handle leave request cancellation
            return redirect()->route('leave-requests.index');
        })->name('leave-requests.cancel');
    });

    // ============================================
    // MANAGER SPECIFIC ROUTES
    // ============================================
    Route::middleware(['role:manager', 'can:view_team_leave_requests'])->prefix('team')->group(function () {
        Route::get('/leave-requests', function () {
            return Inertia::render('employee/team/LeaveRequests');
        })->name('team.leave-requests.index');

        Route::get('/leave-requests/{leaveRequest}', function () {
            return Inertia::render('employee/team/LeaveRequestShow');
        })->name('team.leave-requests.show');

        Route::post('/leave-requests/{leaveRequest}/approve', function () {
            // Handle team leave request approval
            return redirect()->route('team.leave-requests.index');
        })->name('team.leave-requests.approve');

        Route::post('/leave-requests/{leaveRequest}/reject', function () {
            // Handle team leave request rejection
            return redirect()->route('team.leave-requests.index');
        })->name('team.leave-requests.reject');
    });
});
