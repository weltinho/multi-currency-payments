<?php

/*
|--------------------------------------------------------------------------
| Console commands & schedule
|--------------------------------------------------------------------------
|
| db:ensure-seeded — called from Docker entrypoint when the DB has no users.
| payments:expire-pending — scheduled every 15 seconds by the scheduler container.
|
| Expiration is a scheduled command, not a per-payment queued Job — see README.
|
*/

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('db:ensure-seeded', function () {
    // Docker entrypoint calls this after migrate. Idempotent — re-seeds when demo
    // data is missing (e.g. users without payments after a partial wipe).
    $users = User::query()->count();
    $payments = Payment::query()->count();

    if ($users > 0 && $payments > 0) {
        $this->info('Database already has demo data — skipping seed.');

        return;
    }

    $this->warn('Demo data incomplete — running seeders...');
    $this->call('db:seed', ['--force' => true]);
})->purpose('Seed demo users and payments when the database is empty');

Artisan::command('db:ensure-test-database', function () {
    $testDatabase = env('DB_TEST_DATABASE', 'payments_test');
    $host = env('DB_HOST', '127.0.0.1');
    $port = (string) env('DB_PORT', '3306');
    $rootPassword = env('DB_ROOT_PASSWORD', 'root');
    $appUser = env('DB_USERNAME', 'payments');
    $appPassword = env('DB_PASSWORD', 'secret');

    try {
        $root = new \PDO("mysql:host={$host};port={$port}", 'root', $rootPassword, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
    } catch (\PDOException $exception) {
        $this->error('Could not connect to MySQL as root: '.$exception->getMessage());

        return 1;
    }

    $root->exec(
        "CREATE DATABASE IF NOT EXISTS `{$testDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );
    $root->exec("GRANT ALL PRIVILEGES ON `{$testDatabase}`.* TO '{$appUser}'@'%'");
    $root->exec('FLUSH PRIVILEGES');

    try {
        $app = new \PDO(
            "mysql:host={$host};port={$port};dbname={$testDatabase}",
            $appUser,
            $appPassword,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION],
        );
    } catch (\PDOException $exception) {
        $this->error('Test database exists but app user cannot connect: '.$exception->getMessage());

        return 1;
    }

    $this->info("Test database [{$testDatabase}] is ready.");
})->purpose('Create the isolated MySQL database used by PHPUnit');

// Scheduled command (not a delayed Job) — see README for why we chose this.
Schedule::command('payments:expire-pending')
    ->everyFifteenSeconds()
    ->withoutOverlapping();
