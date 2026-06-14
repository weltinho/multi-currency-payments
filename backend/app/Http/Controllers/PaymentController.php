<?php

namespace App\Http\Controllers;

use App\Contracts\Payment\PaymentServiceContract;
use App\Contracts\Translation\TranslatorContract;
use App\Exceptions\ConflictException;
use App\Exceptions\ExchangeRateException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Http\Requests\StorePaymentRequest;
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

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->payments->paginate($request->user(), $request->query()));
    }

    public function summary(Request $request): JsonResponse
    {
        return response()->json($this->payments->summary($request->user(), $request->query()));
    }

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
