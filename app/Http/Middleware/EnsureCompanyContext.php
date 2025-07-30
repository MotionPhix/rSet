<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for unauthenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Check if user has a company assigned
        if (!$user->company_id) {
            // Redirect to company setup or show error
            return redirect()->route('company.setup')
                ->with('error', 'You must be assigned to a company to access this resource.');
        }

        // Check if the company is active
        if (!$user->company->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Your company account has been deactivated. Please contact support.');
        }

        // Check subscription status
        if (!$user->company->hasActiveSubscription()) {
            return redirect()->route('subscription.expired')
                ->with('error', 'Your company subscription has expired. Please renew to continue.');
        }

        // Set the current company in the request for easy access
        $request->merge(['current_company' => $user->company]);

        // Store company context in session for global access
        session(['current_company_id' => $user->company_id]);

        return $next($request);
    }
}
