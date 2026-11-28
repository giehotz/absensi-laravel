<?php

use App\Http\Middleware\AuthenticateApiClient;
use App\Http\Middleware\EnsureCbtManageAccess;
use App\Http\Middleware\EnsureCbtStudent;
use App\Http\Middleware\EnsureIsMbgOfficer;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureSupervisiRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'mbg.officer' => EnsureIsMbgOfficer::class,
            'api.client' => AuthenticateApiClient::class,
            'supervisi.role' => EnsureSupervisiRole::class,
            'cbt.manage' => EnsureCbtManageAccess::class,
            'cbt.student' => EnsureCbtStudent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
