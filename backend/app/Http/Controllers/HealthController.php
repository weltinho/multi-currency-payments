<?php

namespace App\Http\Controllers;

use App\OpenApi\HealthResponse;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

/** Liveness probe for Docker healthchecks and reviewers. */
#[Group('Public', description: 'Unauthenticated endpoints — health and login.', weight: 5)]
class HealthController extends Controller
{
    /**
     * @unauthenticated
     */
    #[Response(200, type: HealthResponse::class)]
    public function __invoke(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }
}
