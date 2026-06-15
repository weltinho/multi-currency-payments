<?php

/*
|--------------------------------------------------------------------------
| Console commands
|--------------------------------------------------------------------------
|
| db:ensure-seeded — called from Docker entrypoint when the DB has no users,
| so a fresh compose up still allows login without a manual migrate --seed.
|
*/

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('db:ensure-seeded', function () {
    // Docker entrypoint calls this after migrate. Idempotent — won't re-seed if
    // someone already registered employees or we ran seed manually.
    if (User::query()->count() > 0) {
        $this->info('Database already has users — skipping seed.');

        return;
    }

    $this->warn('No users found — running seeders...');
    $this->call('db:seed', ['--force' => true]);
})->purpose('Seed demo users and payments when the database is empty');
