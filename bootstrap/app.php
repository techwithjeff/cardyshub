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
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // A missing intl extension surfaces as a RuntimeException thrown by
        // Illuminate\Support\Number, which Filament calls on every admin page
        // that renders a table or pagination. With APP_DEBUG off that is an
        // unexplained 500 on /admin while the storefront works perfectly, which
        // is close to undiagnosable from the symptom. Name it instead.
        $exceptions->render(function (RuntimeException $e) {
            if (! str_contains($e->getMessage(), 'intl')) {
                return null; // Not ours — let the normal handler deal with it.
            }

            return response()->view('errors.missing-intl', [], 500);
        });
    })->create();
