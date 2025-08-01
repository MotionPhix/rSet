<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $analytics = [
            'revenue' => $this->getRevenueAnalytics(),
            'companies' => $this->getCompanyAnalytics(),
            'users' => $this->getUserAnalytics(),
            'usage' => $this->getUsageAnalytics(),
        ];

        return Inertia::render('super-admin/analytics/Index', [
            'analytics' => $analytics,
        ]);
    }

    private function getRevenueAnalytics()
    {
        // Monthly revenue for the past 12 months
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = Company::where('is_active', true)
                ->where('created_at', '<=', $month->endOfMonth())
                ->sum(DB::raw("CASE 
                    WHEN subscription_plan = 'basic' THEN 29
                    WHEN subscription_plan = 'premium' THEN 79
                    WHEN subscription_plan = 'enterprise' THEN 199
                    ELSE 0
                END"));
            
            $monthlyRevenue[] = [
                'month' => $month->format('M Y'),
                'amount' => $revenue
            ];
        }

        // Yearly revenue
        $yearlyRevenue = [];
        for ($i = 2; $i >= 0; $i--) {
            $year = now()->subYears($i);
            $revenue = Company::where('is_active', true)
                ->whereYear('created_at', '<=', $year->year)
                ->sum(DB::raw("CASE 
                    WHEN subscription_plan = 'basic' THEN 29
                    WHEN subscription_plan = 'premium' THEN 79
                    WHEN subscription_plan = 'enterprise' THEN 199
                    ELSE 0
                END"));
            
            $yearlyRevenue[] = [
                'year' => $year->format('Y'),
                'amount' => $revenue
            ];
        }

        $totalRevenue = Company::where('is_active', true)
            ->sum(DB::raw("CASE 
                WHEN subscription_plan = 'basic' THEN 29
                WHEN subscription_plan = 'premium' THEN 79
                WHEN subscription_plan = 'enterprise' THEN 199
                ELSE 0
            END"));

        $growthRate = $this->calculateRevenueGrowthRate();

        return [
            'monthly' => $monthlyRevenue,
            'yearly' => $yearlyRevenue,
            'total' => $totalRevenue,
            'growth_rate' => $growthRate,
        ];
    }

    private function getCompanyAnalytics()
    {
        $total = Company::count();
        $newThisMonth = Company::whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ])->count();

        $churnThisMonth = Company::where('is_active', false)
            ->whereBetween('updated_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->count();

        // Company distribution by plan
        $byPlan = Company::select('subscription_plan')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM companies)) as percentage')
            ->groupBy('subscription_plan')
            ->get()
            ->map(function ($item) {
                return [
                    'plan' => ucfirst($item->subscription_plan),
                    'count' => $item->count,
                    'percentage' => round($item->percentage, 1),
                ];
            });

        // Growth trend for the past 12 months
        $growthTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $new = Company::whereBetween('created_at', [
                $month->startOfMonth(),
                $month->endOfMonth()
            ])->count();

            $churned = Company::where('is_active', false)
                ->whereBetween('updated_at', [
                    $month->startOfMonth(),
                    $month->endOfMonth()
                ])->count();

            $growthTrend[] = [
                'month' => $month->format('M Y'),
                'new' => $new,
                'churned' => $churned,
            ];
        }

        return [
            'total' => $total,
            'new_this_month' => $newThisMonth,
            'churn_this_month' => $churnThisMonth,
            'by_plan' => $byPlan,
            'growth_trend' => $growthTrend,
        ];
    }

    private function getUserAnalytics()
    {
        $total = User::count();
        $activeMonthly = User::whereNotNull('email_verified_at')
            ->where('last_login_at', '>=', now()->subMonth())
            ->count();

        $newThisMonth = User::whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ])->count();

        // Users by role
        $byRole = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name as role')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('roles.name')
            ->get()
            ->map(function ($item) {
                return [
                    'role' => $item->role,
                    'count' => $item->count,
                ];
            });

        return [
            'total' => $total,
            'active_monthly' => $activeMonthly,
            'new_this_month' => $newThisMonth,
            'by_role' => $byRole,
        ];
    }

    private function getUsageAnalytics()
    {
        $totalLeaveRequests = LeaveRequest::count();
        $leaveRequestsThisMonth = LeaveRequest::whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ])->count();

        // Most used leave types
        $mostUsedLeaveTypes = LeaveRequest::join('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id')
            ->select('leave_types.name as type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('leave_types.id', 'leave_types.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'count' => $item->count,
                ];
            });

        // Peak months for leave requests
        $peakMonths = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $requests = LeaveRequest::whereBetween('created_at', [
                $month->startOfMonth(),
                $month->endOfMonth()
            ])->count();

            $peakMonths[] = [
                'month' => $month->format('M Y'),
                'requests' => $requests,
            ];
        }

        return [
            'leave_requests_total' => $totalLeaveRequests,
            'leave_requests_this_month' => $leaveRequestsThisMonth,
            'most_used_leave_types' => $mostUsedLeaveTypes,
            'peak_months' => $peakMonths,
        ];
    }

    private function calculateRevenueGrowthRate()
    {
        $currentMonth = Company::where('is_active', true)
            ->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])
            ->sum(DB::raw("CASE 
                WHEN subscription_plan = 'basic' THEN 29
                WHEN subscription_plan = 'premium' THEN 79
                WHEN subscription_plan = 'enterprise' THEN 199
                ELSE 0
            END"));

        $lastMonth = Company::where('is_active', true)
            ->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth()
            ])
            ->sum(DB::raw("CASE 
                WHEN subscription_plan = 'basic' THEN 29
                WHEN subscription_plan = 'premium' THEN 79
                WHEN subscription_plan = 'enterprise' THEN 199
                ELSE 0
            END"));

        if ($lastMonth == 0) return 0;

        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2);
    }

    /**
     * Show companies analytics.
     */
    public function companies()
    {
        $stats = [
            'total_companies' => Company::count(),
            'active_companies' => Company::where('is_active', true)->count(),
            'inactive_companies' => Company::where('is_active', false)->count(),
            'growth_this_month' => Company::whereMonth('created_at', now()->month)->count(),
        ];

        $companiesByPlan = [
            'basic' => Company::where('subscription_plan', 'basic')->count(),
            'premium' => Company::where('subscription_plan', 'premium')->count(), 
            'enterprise' => Company::where('subscription_plan', 'enterprise')->count(),
        ];

        $growthData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $growthData[] = [
                'month' => $month->format('M Y'),
                'companies' => Company::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        }

        return Inertia::render('super-admin/analytics/Companies', [
            'stats' => $stats,
            'companiesByPlan' => $companiesByPlan,
            'growthData' => $growthData,
        ]);
    }

    /**
     * Show subscriptions analytics.
     */
    public function subscriptions()
    {
        $stats = [
            'total_revenue' => Company::where('is_active', true)
                ->sum(DB::raw("CASE 
                    WHEN subscription_plan = 'basic' THEN 29
                    WHEN subscription_plan = 'premium' THEN 79
                    WHEN subscription_plan = 'enterprise' THEN 199
                    ELSE 0
                END")),
            'monthly_recurring_revenue' => Company::where('is_active', true)
                ->sum(DB::raw("CASE 
                    WHEN subscription_plan = 'basic' THEN 29
                    WHEN subscription_plan = 'premium' THEN 79
                    WHEN subscription_plan = 'enterprise' THEN 199
                    ELSE 0
                END")),
            'avg_revenue_per_customer' => Company::where('is_active', true)->count() > 0 
                ? Company::where('is_active', true)
                    ->sum(DB::raw("CASE 
                        WHEN subscription_plan = 'basic' THEN 29
                        WHEN subscription_plan = 'premium' THEN 79
                        WHEN subscription_plan = 'enterprise' THEN 199
                        ELSE 0
                    END")) / Company::where('is_active', true)->count()
                : 0,
            'churn_rate' => $this->calculateChurnRate(),
        ];

        $revenueByPlan = [
            'basic' => Company::where('subscription_plan', 'basic')->where('is_active', true)->count() * 29,
            'premium' => Company::where('subscription_plan', 'premium')->where('is_active', true)->count() * 79,
            'enterprise' => Company::where('subscription_plan', 'enterprise')->where('is_active', true)->count() * 199,
        ];

        $revenueGrowth = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyRevenue = Company::where('is_active', true)
                ->whereYear('created_at', '<=', $month->year)
                ->where(function($query) use ($month) {
                    $query->whereYear('created_at', '<', $month->year)
                          ->orWhere(function($q) use ($month) {
                              $q->whereYear('created_at', $month->year)
                                ->whereMonth('created_at', '<=', $month->month);
                          });
                })
                ->sum(DB::raw("CASE 
                    WHEN subscription_plan = 'basic' THEN 29
                    WHEN subscription_plan = 'premium' THEN 79
                    WHEN subscription_plan = 'enterprise' THEN 199
                    ELSE 0
                END"));

            $revenueGrowth[] = [
                'month' => $month->format('M Y'),
                'revenue' => $monthlyRevenue,
            ];
        }

        return Inertia::render('super-admin/analytics/Subscriptions', [
            'stats' => $stats,
            'revenueByPlan' => $revenueByPlan,
            'revenueGrowth' => $revenueGrowth,
        ]);
    }

    /**
     * Calculate monthly churn rate.
     */
    private function calculateChurnRate(): float
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        
        $companiesAtStart = Company::where('created_at', '<', $startOfMonth)->where('is_active', true)->count();
        $companiesCancelled = Company::whereBetween('updated_at', [$startOfMonth, $endOfMonth])
            ->where('is_active', false)->count();
        
        if ($companiesAtStart == 0) return 0;
        
        return round(($companiesCancelled / $companiesAtStart) * 100, 2);
    }
}
