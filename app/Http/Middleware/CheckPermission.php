<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
  /**
   * Handle an incoming request.
   *
   * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
   */
  public function handle(Request $request, Closure $next, ...$permissions): Response
  {
    if (!$request->user()) {
      return redirect()->route('login');
    }

    $user = $request->user();

    // Check if user has any of the required permissions
    $hasPermission = false;
    foreach ($permissions as $permission) {
      if ($user->can($permission)) {
        $hasPermission = true;
        break;
      }
    }

    if (!$hasPermission) {
      // Redirect based on user's actual role
      if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard')
          ->with('error', 'You do not have permission to perform that action.');
      } elseif ($user->hasRole('hr') || $user->hasRole('manager')) {
        return redirect()->route('dashboard')
          ->with('error', 'You do not have permission to perform that action.');
      } else {
        return redirect()->route('dashboard')
          ->with('error', 'You do not have permission to perform that action.');
      }
    }

    return $next($request);
  }
}
