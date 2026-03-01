<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Excluir el webhook de Evolution API de la verificación CSRF
        $middleware->validateCsrfTokens(except: [
            'webhook/whatsapp/*',
        ]);

        // Aliases de middleware para tenancy
        $middleware->alias([
            'tenant.instance' => \App\Http\Middleware\InitializeTenancyFromInstance::class,
            'tenant.auth'     => \App\Http\Middleware\InitializeTenancyFromAuth::class,
            'tenant.required' => \App\Http\Middleware\RequireTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
