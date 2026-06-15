<?php

/**
 * PHPUnit bootstrap for in-memory SQLite (no MySQL required).
 */
require __DIR__.'/../vendor/autoload.php';

$overrides = [
    'APP_ENV' => 'testing',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array',
    'BCRYPT_ROUNDS' => '4',
    'PAYMENT_PENDING_EXPIRATION_HOURS' => '48',
];

foreach ($overrides as $key => $value) {
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv("{$key}={$value}");
}
