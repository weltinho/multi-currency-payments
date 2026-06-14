<?php

namespace App\Http\Controllers;

use App\Contracts\Employee\EmployeeServiceContract;
use App\Contracts\Translation\TranslatorContract;
use App\Exceptions\ForbiddenException;
use App\Http\Requests\StoreEmployeeRequest;
use App\Support\EmployeeCountryProfiles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Employee account management (finance role only).
 *
 * POST /employees — provision new worker (registration)
 * GET  /employees  — list for finance dashboard / collaborator filter
 * GET  /employee-countries — allowed country/currency profiles for the form
 */
class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeServiceContract $employees,
        private TranslatorContract $translator,
    ) {}

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

    public function countries(): JsonResponse
    {
        return response()->json([
            'data' => EmployeeCountryProfiles::all(),
        ]);
    }
}
