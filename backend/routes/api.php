<?php

/*
|--------------------------------------------------------------------------
| API routes — Buzzvel multi-currency payment service
|--------------------------------------------------------------------------
|
| Public: health, test-users (demo login modal), login.
| Protected (auth:sanctum): everything else.
|
| Registration is NOT public self-signup. Finance provisions employees via
| POST /employees (see EmployeeController). This satisfies the brief's
| "Registration" requirement with a realistic corporate onboarding model.
|
| Payment routes: employees create/read own; finance lists all, approves/rejects.
| Exchange rate is fetched only on POST /payments — never on PATCH.
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TestUserController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::get('/test-users', [TestUserController::class, 'index']);

Route::post('/login', [AuthController::class, 'login']);

// Logged in, but password change is still allowed before the employee picks a new password.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/password', [AuthController::class, 'updatePassword']);
});

// Everything else requires a "real" password (not the initial full-name one).
Route::middleware(['auth:sanctum', 'password.changed'])->group(function () {
    Route::get('/employee-countries', [EmployeeController::class, 'countries']);
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::post('/employees', [EmployeeController::class, 'store']);

    Route::get('/payments/summary', [PaymentController::class, 'summary']);
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/exchange-rates/{currency}', [ExchangeRateController::class, 'show']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::patch('/payments/{id}', [PaymentController::class, 'decide']);
});
