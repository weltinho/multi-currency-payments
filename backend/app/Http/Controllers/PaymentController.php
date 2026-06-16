<?php

namespace App\Http\Controllers;

use App\Contracts\Payment\PaymentServiceContract;
use App\Contracts\Translation\TranslatorContract;
use App\Exceptions\ConflictException;
use App\Exceptions\ExchangeRateException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Http\Requests\StorePaymentRequest;
use App\OpenApi\PaginatedPaymentResponse;
use App\OpenApi\PaymentResponse;
use App\OpenApi\PaymentSummaryResponse;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Thin controller — validates input, delegates to PaymentServiceContract,
 * maps domain exceptions to localized JSON + HTTP status codes.
 */
class PaymentController extends Controller
{
    public function __construct(
        private PaymentServiceContract $payments,
        private TranslatorContract $translator,
    ) {}

    #[Group('Finance', description: 'Finance audit — list, summary cards, approve/reject.', weight: 30)]
    #[QueryParameter('status', example: 'pending', description: 'Filter by status badge: pending, approved, rejected, expired.')]
    #[QueryParameter('collaborator', example: 'Rafael Silva')]
    #[QueryParameter('page', type: 'integer', example: 1)]
    #[QueryParameter('per_page', type: 'integer', example: 8)]
    #[QueryParameter('sort', example: 'created_at', description: 'Sort column: created_at, currency, local_amount, eur_amount, status, user_name, exchange_rate, country.')]
    #[QueryParameter('dir', example: 'desc', description: 'Sort direction: asc or desc.')]
    #[Response(200, type: PaginatedPaymentResponse::class)]
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->payments->paginate($request->user(), $request->query()));
    }

    #[Group('Finance', weight: 30)]
    #[QueryParameter('collaborator', example: 'Rafael Silva')]
    #[Response(200, type: PaymentSummaryResponse::class)]
    public function summary(Request $request): JsonResponse
    {
        return response()->json($this->payments->summary($request->user(), $request->query()));
    }

    #[Group('Employee', description: 'Employees submit reimbursements in profile currency (or override).', weight: 40)]
    #[Response(201, type: PaymentResponse::class)]
    #[Response(403, examples: [['message' => 'You are not allowed to perform this action.']])]
    #[Response(422, examples: [['message' => 'The given data was invalid.', 'errors' => ['local_amount' => ['The local amount must be greater than 0.']]]])]
    #[Response(503, examples: [['message' => 'Exchange rate is temporarily unavailable. Please try again later.']])]
    public function store(StorePaymentRequest $request): JsonResponse
    {
        try {
            return response()->json(
                $this->payments->create($request->user(), $request->validated()),
                201,
            );
        } catch (ForbiddenException $e) {
            return response()->json(['message' => $this->translator->get($e->translationKey)], 403);
        } catch (ExchangeRateException $e) {
            return response()->json(['message' => $this->translator->get($e->translationKey)], 503);
        }
    }

    #[Group('Employee', description: 'Payment detail for the employee history modal.', weight: 40)]
    #[Response(200, type: PaymentResponse::class)]
    #[Response(403, examples: [['message' => 'You are not allowed to perform this action.']])]
    #[Response(404, examples: [['message' => 'Payment not found.']])]
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            return response()->json($this->payments->show($request->user(), $id));
        } catch (ForbiddenException $e) {
            return response()->json(['message' => $this->translator->get($e->translationKey)], 403);
        } catch (NotFoundException $e) {
            return response()->json(['message' => $this->translator->get($e->translationKey)], 404);
        }
    }

    #[Group('Finance', weight: 30)]
    #[Response(200, type: PaymentResponse::class)]
    #[Response(403, examples: [['message' => 'You are not allowed to perform this action.']])]
    #[Response(404, examples: [['message' => 'Payment not found.']])]
    #[Response(409, examples: [['message' => 'Only pending payments can be approved or rejected.']])]
    #[Response(422, examples: [['message' => 'The given data was invalid.', 'errors' => ['status' => ['The status field is required.']]]])]
    public function decide(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        try {
            return response()->json(
                $this->payments->decide($request->user(), $id, $validated['status'])
            );
        } catch (ForbiddenException $e) {
            return response()->json(['message' => $this->translator->get($e->translationKey)], 403);
        } catch (NotFoundException $e) {
            return response()->json(['message' => $this->translator->get($e->translationKey)], 404);
        } catch (ConflictException $e) {
            return response()->json(['message' => $this->translator->get($e->translationKey)], 409);
        }
    }
}
