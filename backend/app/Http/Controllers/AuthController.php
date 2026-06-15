<?php

namespace App\Http\Controllers;

use App\Contracts\Auth\AuthServiceContract;
use App\Contracts\Translation\TranslatorContract;
use App\Http\Requests\ChangePasswordRequest;
use App\OpenApi\MessageResponse;
use App\OpenApi\UserResponse;
use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
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

    /**
     * @unauthenticated
     */
    #[Group('Public', description: 'Session login — run Public → Get CSRF cookie first when using Try It.', weight: 10)]
    #[BodyParameter('email', type: 'string', format: 'email', example: 'finance@buzzvel.com')]
    #[BodyParameter('password', type: 'string', example: '123456')]
    #[Response(200, type: MessageResponse::class, examples: [['message' => 'Authenticated']])]
    #[Response(422, examples: [['message' => 'The given data was invalid.', 'errors' => ['email' => ['The email field is required.']]]])]
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

    #[Group('Auth', description: 'Session user, logout, and first-login password change.', weight: 20)]
    #[Response(200, type: UserResponse::class)]
    public function user(Request $request): JsonResponse
    {
        return response()->json($this->auth->currentUser($request->user()));
    }

    #[Group('Auth', weight: 20)]
    #[Response(200, type: UserResponse::class)]
    public function updatePassword(ChangePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return response()->json(
            $this->auth->changePassword(
                $request->user(),
                $validated['current_password'],
                $validated['password'],
            ),
        );
    }

    #[Group('Auth', weight: 20)]
    #[Response(204)]
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(null, 204);
    }
}
