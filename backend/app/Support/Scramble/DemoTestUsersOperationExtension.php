<?php

namespace App\Support\Scramble;

use App\Http\Controllers\TestUserController;
use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\RouteInfo;

/** Surfaces demo-only warning on GET /api/test-users in Scramble UI. */
final class DemoTestUsersOperationExtension extends OperationExtension
{
    public function handle(Operation $operation, RouteInfo $routeInfo): void
    {
        if ($routeInfo->className() !== TestUserController::class || $routeInfo->methodName() !== 'index') {
            return;
        }

        $operation
            ->summary('DEMO ONLY — List seeded demo accounts')
            ->description(
                '**Demo only — not for production.** '
                . 'Returns finance and employee emails for evaluator login (same data as the login-screen test-users modal). '
                . '**Password for all seeded accounts: `123456`** — use any listed email with **Public → auth.login**. '
                . 'Finance-created employees are the exception: initial password is their first name until they change it via **Auth → password**. '
                . 'Disable or protect this route before deploying to a real environment.'
            );
    }
}
