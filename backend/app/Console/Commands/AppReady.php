<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Docker healthcheck — exit 0 only when bootstrap finished and demo data exists.
 */
class AppReady extends Command
{
    protected $signature = 'app:ready';

    protected $description = 'Verify bootstrap completed, database is seeded, PHP-FPM is listening, and Scramble docs respond';

    public function handle(): int
    {
        $marker = storage_path('framework/.bootstrap-complete');

        if (! is_file($marker)) {
            $this->error('Bootstrap not complete.');

            return self::FAILURE;
        }

        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $this->error('Database not reachable.');

            return self::FAILURE;
        }

        if (User::query()->count() === 0) {
            $this->error('No users seeded.');

            return self::FAILURE;
        }

        if (Payment::query()->count() === 0) {
            $this->error('No payments seeded.');

            return self::FAILURE;
        }

        $socket = @fsockopen('127.0.0.1', 9000, $errno, $errstr, 2);

        if ($socket === false) {
            $this->error('PHP-FPM not listening on port 9000.');

            return self::FAILURE;
        }

        fclose($socket);

        if (! is_file(storage_path('app/scramble-openapi.json'))) {
            $this->error('Scramble OpenAPI export missing.');

            return self::FAILURE;
        }

        foreach (['/docs/api', '/docs/api.json'] as $path) {
            if (! $this->routeResponds($path)) {
                $this->error("Scramble route [{$path}] is not responding.");

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    private function routeResponds(string $uri): bool
    {
        $request = Request::create($uri, 'GET');
        $response = app()->handle($request);
        $status = $response->getStatusCode();
        app()->terminate($request, $response);

        return $status >= 200 && $status < 400;
    }
}
