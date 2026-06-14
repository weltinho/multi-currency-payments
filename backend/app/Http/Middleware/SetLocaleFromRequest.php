<?php

namespace App\Http\Middleware;

use App\Enums\AppLocale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets Laravel locale from the SPA language header so validation errors and
 * API messages match the UI (lang/{locale}/messages.php).
 */
class SetLocaleFromRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = AppLocale::tryFromHeader(
            $request->header('X-App-Language') ?? $request->header('Accept-Language')
        );

        app()->setLocale($locale->value);

        return $next($request);
    }
}
