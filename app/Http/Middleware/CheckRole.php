<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
  /**
   * Handle an incoming request.
   *
   * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
   */
  public function handle(Request $request, Closure $next, ...$roles): Response
  {
    if (!$request->user()) {
      return redirect()->route('login');
    }

    $user = $request->user();

    // Check if user has any of the required roles
    $hasRole = false;
    foreach ($roles as $role) {
      if ($user->hasRole($role)) {
        $hasRole = true;
        break;
      }
    }

    if (!$hasRole) {
      // Redirect based on user's actual role
      if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard')
          ->with('error', 'You do not have permission to access that page.');
      } elseif ($user->hasRole('hr') || $user->hasRole('manager')) {
        return redirect()->route('dashboard')
          ->with('error', 'You do not have permission to access that page.');
      } else {
        return redirect()->route('dashboard')
          ->with('error', 'You do not have permission to access that page.');
      }
    }

    return $next($request);
  }
}
