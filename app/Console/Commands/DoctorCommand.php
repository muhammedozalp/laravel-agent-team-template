<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Fresh-clone / environment sanity check with green-red output — the
 * executable version of the bootstrap checklist in
 * context/guides/new-project-from-template.md. Exit 1 when any check FAILS
 * (warnings don't fail), so agents and CI can gate on it.
 */
class DoctorCommand extends Command
{
    protected $signature = 'app:doctor';

    protected $description = 'Check that this clone is correctly set up (env, DB, assets, tooling)';

    private bool $failed = false;

    public function handle(): int
    {
        $this->line('');

        // --- Environment
        $this->check('APP_KEY set', (string) config('app.key') !== '',
            'run: php artisan key:generate');
        $host = (string) config('app.host');
        $this->check('APP_HOST resolves (vhost alias)',
            gethostbyname($host) !== $host,
            'compose network alias missing — check docker-compose.yml / guides/docker.md',
            warnOnly: true);

        // --- Services
        $this->check('Database connection', function (): bool {
            DB::connection()->getPdo();

            return true;
        }, 'is the db container healthy? docker compose ps db');
        $this->check('Test database (app_testing) exists', function () {
            return DB::selectOne("SELECT 1 AS ok FROM pg_database WHERE datname = 'app_testing'") !== null;
        }, 'created on first volume init — docker/postgres/init-testing-db.sh; recreate: docker compose down -v && up -d');
        $this->check('Redis reachable', function () {
            return (string) Redis::connection()->ping() !== '';
        }, 'is the redis container healthy? docker compose ps redis');

        // --- State
        $this->check('Migrations up to date', function () {
            Artisan::call('migrate:status', ['--pending' => true]);

            return ! str_contains(Artisan::output(), 'Pending');
        }, 'run: php artisan migrate', warnOnly: true);
        $this->check('storage/ writable', is_writable(storage_path('framework')) && is_writable(storage_path('logs')),
            'fix ownership — DOCKER_UID/GID in .env must match your host user (guides/docker.md)');

        // --- Frontend
        $this->check('Vite assets (built or dev server)',
            file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')),
            'run: docker compose exec node npm run build (or npm run dev)');
        $this->check('Wayfinder TypeScript generated', is_dir(resource_path('js/routes')),
            'run: php artisan wayfinder:generate --with-form (normally done by the Vite build)',
            warnOnly: true);

        // --- Production readiness (advisory outside production)
        $this->check('Sentry DSN set', (string) config('sentry.dsn') !== '',
            'set SENTRY_DSN before going live (guides/deploy.md)',
            warnOnly: ! app()->environment('production'));

        $this->line('');

        if ($this->failed) {
            $this->error('Doctor found problems — fix the ✗ items above.');

            return self::FAILURE;
        }

        $this->info('All checks passed.');

        return self::SUCCESS;
    }

    /**
     * @param  bool|callable(): bool  $condition
     */
    private function check(string $label, bool|callable $condition, string $hint, bool $warnOnly = false): void
    {
        try {
            $ok = is_callable($condition) ? (bool) $condition() : $condition;
        } catch (Throwable) {
            $ok = false;
        }

        if ($ok) {
            $this->line("  <fg=green>✓</> {$label}");

            return;
        }

        if ($warnOnly) {
            $this->line("  <fg=yellow>!</> {$label} — {$hint}");

            return;
        }

        $this->line("  <fg=red>✗</> {$label} — {$hint}");
        $this->failed = true;
    }
}
