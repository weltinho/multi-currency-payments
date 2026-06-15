<?php

namespace App\Http\Controllers;

use App\Contracts\Employee\EmployeeServiceContract;
use App\Contracts\Translation\TranslatorContract;
use App\Exceptions\ForbiddenException;
use App\Http\Requests\StoreEmployeeRequest;
use App\OpenApi\CountryProfileListResponse;
use App\OpenApi\EmployeeListResponse;
use App\OpenApi\UserResponse;
use App\Support\EmployeeCountryProfiles;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Employee account management (finance role only).
 *
 * POST /employees — provision new worker (registration)
 * GET  /employees  — list for finance dashboard / collaborator filter
 * GET  /employee-countries — allowed country/currency profiles for the form
 */
#[Group('Finance', description: 'Finance-only — register employees and list collaborators.', weight: 30)]
class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeServiceContract $employees,
        private TranslatorContract $translator,
    ) {}

    #[Response(200, type: EmployeeListResponse::class)]
    public function index(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'data' => $this->employees->listEmployees($request->user()),
            ]);
        } catch (ForbiddenException $e) {
            return response()->json(['message' => $this->translator->get($e->translationKey)], 403);
        }
    }

    #[Response(201, type: UserResponse::class)]
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        try {
            return response()->json(
                $this->employees->register($request->user(), $request->validated()),
                201,
            );
        } catch (ForbiddenException $e) {
            return response()->json(['message' => $this->translator->get($e->translationKey)], 403);
        }
    }

    #[Response(200, type: CountryProfileListResponse::class)]
    public function countries(): JsonResponse
    {
        return response()->json([
            'data' => EmployeeCountryProfiles::all(),
        ]);
    }
}
