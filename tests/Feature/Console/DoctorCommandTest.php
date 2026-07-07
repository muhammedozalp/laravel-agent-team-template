<?php

use Illuminate\Support\Facades\DB;

test('app:doctor passes in a healthy environment', function () {
    $this->artisan('app:doctor')
        ->expectsOutputToContain('APP_KEY')
        ->expectsOutputToContain('Database connection')
        ->expectsOutputToContain('Test database')
        ->expectsOutputToContain('Migrations')
        ->assertSuccessful();
});

test('app:doctor fails when APP_KEY is missing', function () {
    config(['app.key' => '']);

    $this->artisan('app:doctor')->assertFailed();
});

test('app:doctor fails when the database is unreachable', function () {
    $originalHost = config('database.connections.pgsql.host');

    config(['database.connections.pgsql.host' => 'nonexistent-host']);
    DB::purge('pgsql');

    $this->artisan('app:doctor')->assertFailed();

    // Restore before teardown — RefreshDatabase reconnects to roll back.
    config(['database.connections.pgsql.host' => $originalHost]);
    DB::purge('pgsql');
});
