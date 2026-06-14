<?php

namespace App\Http\Controllers;

use App\Contracts\TestUser\TestUserServiceContract;
use Illuminate\Http\JsonResponse;

/**
 * Public endpoint for the login-screen "test users" modal.
 * Intentionally unauthenticated so reviewers can discover demo credentials.
 */
class TestUserController extends Controller
{
    public function __construct(private TestUserServiceContract $testUsers) {}

    public function index(): JsonResponse
    {
        return response()->json($this->testUsers->listGroupedByRole());
    }
}
