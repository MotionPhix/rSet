<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\LeaveTypeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\HolidayController;

Route::middleware(['auth', 'verified', 'company.context', 'role:admin,hr'])->prefix('admin')->group(function () {

    // ============================================
    // ADMIN DASHBOARD
    // ============================================
    Route::get('/dashboard', function () {
        return Inertia::render('admin/Dashboard');
    })->name('admin.dashboard')->middleware('role:admin');

    // ============================================
    // CALENDAR (Admin/HR)
    // ============================================
    Route::prefix('calendar')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->name('admin.calendar.index');
        Route::get('/events', [CalendarController::class, 'getEvents'])->name('admin.calendar.events');
        Route::get('/team-events', [CalendarController::class, 'getTeamEvents'])->name('admin.calendar.team-events');
        
        // Company overview (only for users with view_all_leave_requests permission)
        Route::middleware(['can:view_all_leave_requests'])->group(function () {
            Route::get('/company-overview', [CalendarController::class, 'getCompanyOverview'])->name('admin.calendar.company-overview');
        });
    });

    // ============================================
    // HOLIDAYS (Admin/HR)
    // ============================================
    Route::resource('holidays', HolidayController::class)->names([
        'index' => 'admin.holidays.index',
        'store' => 'admin.holidays.store',
        'update' => 'admin.holidays.update',
        'destroy' => 'admin.holidays.destroy',
    ]);
    Route::get('/api/holidays/calendar', [HolidayController::class, 'getHolidaysForCalendar'])
        ->name('admin.holidays.calendar');

    // ============================================
    // REPORTS & ANALYTICS (Admin/HR)
    // ============================================
    Route::middleware(['can:view_reports'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('admin.reports.generate');
        Route::post('/reports/export', [ReportController::class, 'export'])->name('admin.reports.export');
    });

    Route::middleware(['can:view_analytics'])->group(function () {
        Route::get('/analytics', function () {
            return Inertia::render('admin/analytics/Index');
        })->name('admin.analytics.index');
    });

    // ============================================
    // LEAVE REQUEST MANAGEMENT (Admin/HR)
    // ============================================
    Route::middleware(['can:view_all_leave_requests'])->group(function () {
        Route::get('/leave-requests', function () {
            return Inertia::render('admin/leave-requests/Index');
        })->name('admin.leave-requests.index');

        Route::get('/leave-requests/{leaveRequest}', function () {
            return Inertia::render('admin/leave-requests/Show');
        })->name('admin.leave-requests.show');
    });

    Route::middleware(['can:approve_leave_request'])->group(function () {
        Route::post('/leave-requests/{leaveRequest}/approve', function () {
            // Handle leave request approval
            return redirect()->route('admin.leave-requests.index');
        })->name('admin.leave-requests.approve');
    });

    Route::middleware(['can:reject_leave_request'])->group(function () {
        Route::post('/leave-requests/{leaveRequest}/reject', function () {
            // Handle leave request rejection
            return redirect()->route('admin.leave-requests.index');
        })->name('admin.leave-requests.reject');
    });

    // ============================================
    // ADMIN SETTINGS (Moved from general settings)
    // ============================================
    Route::prefix('settings')->group(function () {
        // Company Management (Admin only)
        Route::middleware(['can:view_company_profile'])->group(function () {
            Route::get('/company', [CompanyController::class, 'index'])->name('admin.settings.company');
            Route::patch('/company', [CompanyController::class, 'update'])
                ->middleware('can:edit_company_profile')->name('admin.settings.company.update');
        });

        // User Management
        Route::middleware(['can:view_users'])->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('admin.settings.users');
            Route::post('/users', [UserController::class, 'store'])
                ->middleware('can:create_users')->name('admin.settings.users.store');
            Route::patch('/users/{user}', [UserController::class, 'update'])
                ->middleware('can:edit_users')->name('admin.settings.users.update');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])
                ->middleware('can:delete_users')->name('admin.settings.users.destroy');
        });

        // Team Management
        Route::middleware(['can:view_teams'])->group(function () {
            Route::get('/teams', [TeamController::class, 'index'])->name('admin.settings.teams');
            Route::post('/teams', [TeamController::class, 'store'])
                ->middleware('can:create_teams')->name('admin.settings.teams.store');
            Route::patch('/teams/{team}', [TeamController::class, 'update'])
                ->middleware('can:edit_teams')->name('admin.settings.teams.update');
            Route::delete('/teams/{team}', [TeamController::class, 'destroy'])
                ->middleware('can:delete_teams')->name('admin.settings.teams.destroy');
        });

        // Leave Types Management
        Route::middleware(['can:view_leave_types'])->group(function () {
            Route::get('/leave-types', [LeaveTypeController::class, 'index'])->name('admin.settings.leave-types');
            Route::post('/leave-types', [LeaveTypeController::class, 'store'])
                ->middleware('can:create_leave_types')->name('admin.settings.leave-types.store');
            Route::patch('/leave-types/{leaveType}', [LeaveTypeController::class, 'update'])
                ->middleware('can:edit_leave_types')->name('admin.settings.leave-types.update');
            Route::delete('/leave-types/{leaveType}', [LeaveTypeController::class, 'destroy'])
                ->middleware('can:delete_leave_types')->name('admin.settings.leave-types.destroy');
        });

        // Roles & Permissions
        Route::middleware(['can:assign_roles'])->group(function () {
            Route::get('/roles', [RoleController::class, 'index'])->name('admin.settings.roles');
            Route::patch('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
                ->middleware('can:manage_user_permissions')->name('admin.settings.roles.permissions');
        });
    });
});
