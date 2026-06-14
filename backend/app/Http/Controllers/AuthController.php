<?php

namespace App\Http\Controllers;

use App\Contracts\Auth\AuthServiceContract;
use App\Contracts\Translation\TranslatorContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Sanctum session auth. No register endpoint here — see EmployeeController.
 * Session regenerate on login follows Laravel security defaults.
 */
class AuthController extends Controller
{
    public function __construct(
        private AuthServiceContract $auth,
        private TranslatorContract $translator,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->auth->login($credentials);
        $request->session()->regenerate();

        return response()->json([
            'message' => $this->translator->get('auth.authenticated'),
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($this->auth->currentUser($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(null, 204);
    }
}
