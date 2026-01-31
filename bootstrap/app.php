<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add company middleware to web group (runs on every web request)
        $middleware->web(append: [
            \App\Http\Middleware\SetCurrentCompany::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'economy' => \App\Http\Middleware\EconomyMiddleware::class,
            'feature' => \App\Http\Middleware\CheckFeatureEnabled::class,
            'company' => \App\Http\Middleware\EnsureUserBelongsToCompany::class,
            'company.manager' => \App\Http\Middleware\CompanyManager::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
