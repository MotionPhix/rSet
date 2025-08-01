<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'companies' => [
                'total' => Company::count(),
                'active' => Company::where('is_active', true)->count(),
                'expired' => Company::where('subscription_expires_at', '<', now())->count(),
                'new_this_month' => Company::whereBetween('created_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ])->count(),
            ],
            'users' => [
                'total' => User::count(),
                'active' => User::whereNotNull('email_verified_at')->count(),
                'new_this_month' => User::whereBetween('created_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ])->count(),
            ],
            'revenue' => [
                'monthly' => $this->getMonthlyRevenue(),
                'total' => Company::where('is_active', true)
                    ->sum(\DB::raw("CASE 
                        WHEN subscription_plan = 'basic' THEN 29
                        WHEN subscription_plan = 'premium' THEN 79
                        WHEN subscription_plan = 'enterprise' THEN 199
                        ELSE 0
                    END")),
                'growth_rate' => $this->getRevenueGrowthRate(),
            ],
            'leave_requests' => [
                'total' => LeaveRequest::count(),
                'this_month' => LeaveRequest::whereBetween('created_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ])->count(),
                'pending' => LeaveRequest::where('status', 'pending')->count(),
            ]
        ];

        // Recent activities
        $recent_companies = Company::with(['users' => function($query) {
            $query->where('role', 'admin')->first();
        }])
        ->latest()
        ->take(5)
        ->get();

        $recent_users = User::with('company')
            ->latest()
            ->take(10)
            ->get();

        // System health
        $system_health = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->getStorageInfo(),
            'cache' => $this->checkCacheHealth(),
        ];

        return Inertia::render('super-admin/Dashboard', [
            'stats' => $stats,
            'recent_companies' => $recent_companies,
            'recent_users' => $recent_users,
            'system_health' => $system_health,
        ]);
    }

    private function getMonthlyRevenue()
    {
        $revenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyRevenue = Company::where('is_active', true)
                ->where('created_at', '<=', $month->endOfMonth())
                ->sum(\DB::raw("CASE 
                    WHEN subscription_plan = 'basic' THEN 29
                    WHEN subscription_plan = 'premium' THEN 79
                    WHEN subscription_plan = 'enterprise' THEN 199
                    ELSE 0
                END"));
            
            $revenue[] = [
                'month' => $month->format('M Y'),
                'amount' => $monthlyRevenue
            ];
        }
        return $revenue;
    }

    private function getRevenueGrowthRate()
    {
        $currentMonth = Company::where('is_active', true)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum(\DB::raw("CASE 
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
            ->sum(\DB::raw("CASE 
                WHEN subscription_plan = 'basic' THEN 29
                WHEN subscription_plan = 'premium' THEN 79
                WHEN subscription_plan = 'enterprise' THEN 199
                ELSE 0
            END"));

        if ($lastMonth == 0) return 0;
        
        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2);
    }

    private function checkDatabaseHealth()
    {
        try {
            \DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function getStorageInfo()
    {
        $totalSpace = disk_total_space(storage_path());
        $freeSpace = disk_free_space(storage_path());
        $usedSpace = $totalSpace - $freeSpace;
        
        return [
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'percentage' => round(($usedSpace / $totalSpace) * 100, 2)
        ];
    }

    private function checkCacheHealth()
    {
        try {
            \Cache::put('health_check', 'ok', 60);
            $check = \Cache::get('health_check');
            return $check === 'ok' ? 'healthy' : 'error';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
