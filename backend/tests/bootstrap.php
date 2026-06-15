<?php

/**
 * PHPUnit bootstrap — force test environment before Laravel loads.
 *
 * Docker Compose injects APP_ENV=local and DB_DATABASE=payments from backend/.env.
 * Without these overrides, RefreshDatabase would wipe demo data.
 */
require __DIR__.'/../vendor/autoload.php';

$overrides = [
    'APP_ENV' => 'testing',
    'DB_CONNECTION' => 'mysql',
    'DB_DATABASE' => 'payments_test',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array',
    'BCRYPT_ROUNDS' => '4',
];

foreach ($overrides as $key => $value) {
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv("{$key}={$value}");
}
