<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
  /**
   * The root template that's loaded on the first page visit.
   *
   * @see https://inertiajs.com/server-side-setup#root-template
   *
   * @var string
   */
  protected $rootView = 'app';

  /**
   * Determines the current asset version.
   *
   * @see https://inertiajs.com/asset-versioning
   */
  public function version(Request $request): ?string
  {
    return parent::version($request);
  }

  /**
   * Define the props that are shared by default.
   *
   * @see https://inertiajs.com/shared-data
   *
   * @return array<string, mixed>
   */
  public function share(Request $request): array
  {
    [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

    $user = $request->user();

    // Load user with relationships if authenticated
    if ($user) {
      $user->load(['company', 'roles.permissions', 'team']);
    }

    return [
      ...parent::share($request),
      'name' => config('app.name'),
      'quote' => ['message' => trim($message), 'author' => trim($author)],
      'auth' => [
        'user' => $user ? [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
          'company_id' => $user->company_id,
          'team_id' => $user->team_id,
          'roles' => $user->roles->map(function ($role) {
            return [
              'id' => $role->id,
              'name' => $role->name,
              'permissions' => $role->permissions->pluck('name')->toArray(),
            ];
          }),
          'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
          'team' => $user->team ? [
            'id' => $user->team->id,
            'name' => $user->team->name,
            'manager_id' => $user->team->manager_id,
          ] : null,
        ] : null,
        'company' => $user?->company ? [
          'id' => $user->company->id,
          'name' => $user->company->name,
          'slug' => $user->company->slug,
          'email' => $user->company->email,
          'phone' => $user->company->phone,
          'address' => $user->company->address,
          'website' => $user->company->website,
          'timezone' => $user->company->timezone,
          'currency' => $user->company->currency,
          'max_employees' => $user->company->max_employees,
          'subscription_plan' => $user->company->subscription_plan,
          'subscription_expires_at' => $user->company->subscription_expires_at,
          'is_active' => $user->company->is_active,
        ] : null,
      ],
      'ziggy' => [
        ...(new Ziggy)->toArray(),
        'location' => $request->url(),
      ],
      'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
    ];
  }
}
