<?php

namespace App\Http\Middleware;

use App\Contracts\Translation\TranslatorContract;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stops provisioned employees from hitting payments (etc.) until they've set a
 * real password. Routes like /user, /logout and /password sit outside this
 * middleware so the first-login flow can still work.
 */
class EnsurePasswordIsChanged
{
    public function __construct(private TranslatorContract $translator) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->must_change_password) {
            return response()->json([
                'message' => $this->translator->get('auth.password_change_required'),
            ], 403);
        }

        return $next($request);
    }
}
