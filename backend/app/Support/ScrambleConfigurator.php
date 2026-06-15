<?php

namespace App\Support;

use App\Support\Scramble\OpenApiSchemaRegistrar;
use Dedoc\Scramble\Configuration\DocumentTransformers;
use Dedoc\Scramble\OpenApiContext;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\InfoObject;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Path;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\Server;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;

/**
 * Scramble / OpenAPI presentation — theme, auth flow, and Try It ergonomics.
 */
final class ScrambleConfigurator
{
    public static function register(): void
    {
        Scramble::configure()
            ->withDocumentTransformers(function (DocumentTransformers $transformers) {
                $transformers->prepend(function (OpenApi $openApi, OpenApiContext $context) {
                    self::transform($openApi, $context);
                });
                $transformers->append(OpenApiSchemaRegistrar::class);
            });
    }

    public static function transform(OpenApi $openApi, OpenApiContext $context): void
    {
        $openApi->info = InfoObject::make(
            config('scramble.info.title', 'Buzzvel Multi-Currency Payments API'),
        )
            ->setVersion(config('scramble.info.version', '1.0.0'))
            ->setDescription(self::overviewMarkdown());

        $openApi->secure(
            SecurityScheme::apiKey('cookie', 'laravel_session')
                ->as('session')
                ->setDescription(
                    'Laravel session cookie set by `POST /api/login`. '
                    .'Use **Try It** on this same docs page so cookies are shared (`credentials: include`). '
                    .'Before login or any mutating call, run **Public → Get CSRF cookie** once (see step-by-step there).'
                ),
        );

        self::prependCsrfPath($openApi);
    }

    private static function overviewMarkdown(): string
    {
        $appUrl = rtrim((string) config('app.url', 'http://localhost:8080'), '/');

        return <<<MD
        Multi-currency reimbursement API for Buzzvel's technical assessment. Finance audits company-wide payment requests; employees submit expenses in their profile currency (or another supported ISO code). Pending requests **expire after 48 hours** when finance does not act.

        ## Try It — step by step (no Postman)

        All steps use **Send API Request** on this docs page (same browser tab — cookies must be shared).

        1. **Public → Get CSRF cookie** — no body. Click Send → expect **204 No Content** (empty body is normal). **Then proceed** to step 2 — the `XSRF-TOKEN` cookie is now set.
        2. **Public → auth.login** — body `{"email":"finance@buzzvel.com","password":"123456"}` (or any seeded employee). Send → **200**. Session cookie is stored automatically.
        3. **Protected endpoint** — e.g. **Employee → payment.store** as `rafael@buzzvel.com` / `123456` with `{"description":"…","local_amount":4200,"currency":"BRL"}`.

        Seeded emails: **Public → DEMO ONLY — List seeded demo accounts**. UI: [{$appUrl}]({$appUrl}).

        ### Roles

        | Group | Who | Capabilities |
        |-------|-----|--------------|
        | **Public** | Everyone | CSRF cookie, health, login, demo test-users |
        | **Auth** | Logged-in users | Session user, logout, password change |
        | **Finance** | `finance@buzzvel.com` | Registration (`POST /employees`), list/approve/reject payments |
        | **Employee** | e.g. `rafael@buzzvel.com` | Create and track own payment requests |

        MD;
    }

    private static function prependCsrfPath(OpenApi $openApi): void
    {
        $gateway = rtrim((string) config('app.url', 'http://localhost:8080'), '/');

        $operation = Operation::make('get')
            ->setOperationId('public.csrfCookie')
            ->summary('Get CSRF cookie (Try It — do this first)')
            ->description(self::csrfCookieMarkdown($gateway))
            ->setTags(['Public'])
            ->servers([Server::make($gateway)])
            ->addSecurity(new SecurityRequirement([]))
            ->addResponse(Response::make(204)->setDescription(
                'No content — success. The `XSRF-TOKEN` cookie was set; **proceed to Public → auth.login** (or any mutating endpoint).'
            ));

        $path = Path::make('sanctum/csrf-cookie')
            ->servers([Server::make($gateway)])
            ->addOperation($operation);

        array_unshift($openApi->paths, $path);
    }

    private static function csrfCookieMarkdown(string $gateway): string
    {
        return <<<MD
        Laravel requires a CSRF token for every `POST`, `PUT`, `PATCH`, and `DELETE`. This call has **no request body** and returns **204 No Content** — it only sets the `XSRF-TOKEN` cookie.

        ### How to use (Try It on this page)

        1. Stay on **`{$gateway}/docs/api`** (same tab for all steps).
        2. Open **Public → Get CSRF cookie** (this endpoint).
        3. Click **Send API Request** — no headers or body to fill. Expect **204 No Content** (no JSON body — that is expected).
        4. **Proceed to the next step:** open **Public → auth.login** (step 2 in the overview) or any mutating endpoint. Try It sends the `X-XSRF-TOKEN` header automatically from the cookie.

        **Why a different URL?** This route lives at **`{$gateway}/sanctum/csrf-cookie`** (site root), not under `/api`. Other endpoints use `/api/...` — that is normal.

        Repeat step 3 only if you open docs in a new browser or cleared cookies.
        MD;
    }
}
