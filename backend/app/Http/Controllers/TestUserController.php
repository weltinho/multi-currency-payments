<?php

namespace App\Http\Controllers;

use App\Contracts\TestUser\TestUserServiceContract;
use App\OpenApi\TestUsersResponse;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

/**
 * Public endpoint for the login-screen "test users" modal.
 */
#[Group('Demo only', description: 'Evaluator helpers — seeded credentials. Not for production.', weight: 11)]
class TestUserController extends Controller
{
    public function __construct(private TestUserServiceContract $testUsers) {}

    /**
     * @unauthenticated
     */
    #[Response(200, type: TestUsersResponse::class)]
    public function index(): JsonResponse
    {
        return response()->json($this->testUsers->listGroupedByRole());
    }
}
