<?php

use App\Http\Middleware\EnsurePasswordIsChanged;
use App\Http\Middleware\SetLocaleFromRequest;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Sanctum SPA: session cookies for same-origin Next.js frontend.
        $middleware->statefulApi();
        // API messages follow the UI language sent by the frontend.
        $middleware->api(prepend: [
            SetLocaleFromRequest::class,
        ]);
        $middleware->alias([
            // Gate for employees who still need to change their initial password.
            'password.changed' => EnsurePasswordIsChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $errors = $exception->errors();
            $message = collect($errors)->flatten()->first() ?? $exception->getMessage();

            return response()->json([
                'message' => $message,
                'errors' => $errors,
            ], $exception->status);
        });
    })->create();
