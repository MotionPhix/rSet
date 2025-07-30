<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Employee\ReportController;

Route::middleware(['auth', 'verified', 'company.context'])->group(function () {

    // ============================================
    // EMPLOYEE DASHBOARD
    // ============================================
    Route::get('/dashboard', function () {
        return Inertia::render('employee/Dashboard');
    })->name('dashboard');

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
    // LEAVE REQUEST MANAGEMENT (Employee/Manager)
    // ============================================
    Route::middleware(['can:create_leave_request'])->group(function () {
        Route::get('/leave-requests', function () {
            return Inertia::render('employee/leave-requests/Index');
        })->name('leave-requests.index');

        Route::get('/leave-requests/create', function () {
            return Inertia::render('employee/leave-requests/Create');
        })->name('leave-requests.create');

        Route::post('/leave-requests', function () {
            // Handle leave request creation
            return redirect()->route('leave-requests.index');
        })->name('leave-requests.store');
    });

    Route::middleware(['can:view_own_leave_request'])->group(function () {
        Route::get('/leave-requests/{leaveRequest}', function () {
            return Inertia::render('employee/leave-requests/Show');
        })->name('leave-requests.show');
    });

    Route::middleware(['can:edit_own_leave_request'])->group(function () {
        Route::get('/leave-requests/{leaveRequest}/edit', function () {
            return Inertia::render('employee/leave-requests/Edit');
        })->name('leave-requests.edit');

        Route::put('/leave-requests/{leaveRequest}', function () {
            // Handle leave request update
            return redirect()->route('leave-requests.index');
        })->name('leave-requests.update');
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
    // EMPLOYEE SETTINGS (Personal settings only)
    // ============================================
    Route::prefix('settings')->group(function () {
        // Personal Profile
        Route::get('/profile', [App\Http\Controllers\Settings\ProfileController::class, 'edit'])->name('employee.settings.profile');
        Route::patch('/profile', [App\Http\Controllers\Settings\ProfileController::class, 'update'])->name('employee.settings.profile.update');

        // Password
        Route::get('/password', [App\Http\Controllers\Settings\PasswordController::class, 'edit'])->name('employee.settings.password');
        Route::put('/password', [App\Http\Controllers\Settings\PasswordController::class, 'update'])->name('employee.settings.password.update');

        // Appearance
        Route::get('/appearance', function () {
            return Inertia::render('employee/settings/Appearance');
        })->name('employee.settings.appearance');
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
