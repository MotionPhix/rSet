<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\EnsureCompanyContext;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->encryptCookies(except: ['sidebar_state']);

    $middleware->web(append: [
      HandleInertiaRequests::class,
      AddLinkHeadersForPreloadedAssets::class,
    ]);

    $middleware->alias([
      'company.context' => EnsureCompanyContext::class,
      'role' => CheckRole::class,
      'permission' => CheckPermission::class,
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })->create();
