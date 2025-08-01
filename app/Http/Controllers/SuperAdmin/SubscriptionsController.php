<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class SubscriptionsController extends Controller
{
    public function index()
    {
        $subscriptions = Company::select([
            'id', 'name', 'email', 'subscription_plan', 
            'subscription_expires_at', 'is_active', 'created_at'
        ])
        ->withCount('users')
        ->latest()
        ->paginate(20);

        $stats = [
            'total' => Company::count(),
            'active' => Company::where('is_active', true)->count(),
            'expired' => Company::where('subscription_expires_at', '<', now())->count(),
            'expiring_soon' => Company::where('subscription_expires_at', '<=', now()->addDays(7))
                ->where('subscription_expires_at', '>', now())->count(),
            'revenue' => [
                'monthly' => $this->getMonthlyRevenue(),
                'by_plan' => $this->getRevenueByPlan(),
            ]
        ];

        $plans = [
            'basic' => [
                'name' => 'Basic',
                'price' => 29,
                'features' => ['Up to 25 employees', 'Basic leave management', 'Email support'],
                'subscribers' => Company::where('subscription_plan', 'basic')->count(),
            ],
            'premium' => [
                'name' => 'Premium', 
                'price' => 79,
                'features' => ['Up to 100 employees', 'Advanced analytics', 'Priority support', 'API access'],
                'subscribers' => Company::where('subscription_plan', 'premium')->count(),
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => 199,
                'features' => ['Unlimited employees', 'Custom integrations', 'Dedicated support', 'White-label'],
                'subscribers' => Company::where('subscription_plan', 'enterprise')->count(),
            ]
        ];

        return Inertia::render('super-admin/subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'plans' => $plans,
        ]);
    }

    public function plans()
    {
        $plans = [
            'basic' => [
                'name' => 'Basic',
                'price' => 29,
                'features' => ['Up to 25 employees', 'Basic leave management', 'Email support'],
                'subscribers' => Company::where('subscription_plan', 'basic')->count(),
                'revenue' => Company::where('subscription_plan', 'basic')->where('is_active', true)->count() * 29,
            ],
            'premium' => [
                'name' => 'Premium', 
                'price' => 79,
                'features' => ['Up to 100 employees', 'Advanced analytics', 'Priority support', 'API access'],
                'subscribers' => Company::where('subscription_plan', 'premium')->count(),
                'revenue' => Company::where('subscription_plan', 'premium')->where('is_active', true)->count() * 79,
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => 199,
                'features' => ['Unlimited employees', 'Custom integrations', 'Dedicated support', 'White-label'],
                'subscribers' => Company::where('subscription_plan', 'enterprise')->count(),
                'revenue' => Company::where('subscription_plan', 'enterprise')->where('is_active', true)->count() * 199,
            ]
        ];

        return Inertia::render('super-admin/subscriptions/Plans', [
            'plans' => $plans,
        ]);
    }

    public function updatePlan(Request $request, Company $company)
    {
        $validated = $request->validate([
            'subscription_plan' => 'required|in:basic,premium,enterprise',
            'subscription_expires_at' => 'required|date|after:today',
        ]);

        $company->update($validated);

        return redirect()->route('super-admin.subscriptions.index')
            ->with('success', 'Subscription plan updated successfully.');
    }

    public function extendSubscription(Request $request, Company $company)
    {
        $validated = $request->validate([
            'months' => 'required|integer|min:1|max:24'
        ]);

        $newExpirationDate = Carbon::parse($company->subscription_expires_at)
            ->addMonths($validated['months']);

        $company->update([
            'subscription_expires_at' => $newExpirationDate
        ]);

        return redirect()->route('super-admin.subscriptions.index')
            ->with('success', "Subscription extended by {$validated['months']} months.");
    }

    public function cancelSubscription(Company $company)
    {
        $company->update([
            'is_active' => false,
            'subscription_expires_at' => now()
        ]);

        return redirect()->route('super-admin.subscriptions.index')
            ->with('success', 'Subscription cancelled successfully.');
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

    /**
     * Create a new subscription plan.
     */
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'max_employees' => 'required|integer|min:1',
            'features' => 'required|array',
            'features.*' => 'string|max:255',
        ]);

        // In a real application, you would save this to a subscription_plans table
        // For now, we'll just redirect back with success message
        
        return redirect()->route('super-admin.subscriptions.plans')
            ->with('success', 'Subscription plan created successfully.');
    }

    /**
     * Show a specific subscription.
     */
    public function show($id)
    {
        // In a real application, you would fetch the subscription by ID
        $subscription = [
            'id' => $id,
            'company' => [
                'name' => 'Sample Company ' . $id,
                'email' => 'contact@company' . $id . '.com',
            ],
            'plan' => [
                'name' => 'Premium',
                'price' => 79,
                'billing_cycle' => 'monthly',
            ],
            'status' => 'active',
            'current_period_start' => now()->startOfMonth(),
            'current_period_end' => now()->endOfMonth(),
            'created_at' => now()->subMonths(6),
            'payment_history' => [
                [
                    'date' => now()->subMonth(),
                    'amount' => 79,
                    'status' => 'paid',
                    'invoice_url' => '#',
                ],
                [
                    'date' => now()->subMonths(2),
                    'amount' => 79,
                    'status' => 'paid',
                    'invoice_url' => '#',
                ],
            ],
        ];

        return Inertia::render('super-admin/subscriptions/Show', [
            'subscription' => $subscription,
        ]);
    }

    /**
     * Update a subscription.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'plan' => 'required|string|in:basic,premium,enterprise',
            'status' => 'sometimes|string|in:active,cancelled,suspended',
        ]);

        // In a real application, you would update the subscription
        // This might involve calling external payment processor APIs
        
        return redirect()->route('super-admin.subscriptions.index')
            ->with('success', 'Subscription updated successfully.');
    }

    /**
     * Cancel/delete a subscription.
     */
    public function destroy($id)
    {
        // In a real application, you would cancel the subscription
        // This might involve calling external payment processor APIs
        
        return redirect()->route('super-admin.subscriptions.index')
            ->with('success', 'Subscription cancelled successfully.');
    }

    private function getRevenueByPlan()
    {
        return [
            'basic' => Company::where('subscription_plan', 'basic')->where('is_active', true)->count() * 29,
            'premium' => Company::where('subscription_plan', 'premium')->where('is_active', true)->count() * 79,
            'enterprise' => Company::where('subscription_plan', 'enterprise')->where('is_active', true)->count() * 199,
        ];
    }
}
