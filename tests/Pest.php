<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case bindings
|--------------------------------------------------------------------------
| The starter kit's Feature/Unit tests are PHPUnit classes and configure
| themselves; these bindings apply to Pest-style tests. Browser tests boot
| the framework and get a fresh Postgres schema (app_testing — phpunit.xml).
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    // Browser tests run against production-built assets (public/build), never the
    // Vite dev server — its hot-file URL is unreachable from the test browser and
    // e2e should exercise what ships. Requires `npm run build` once (guides/testing.md).
    ->beforeEach(fn () => Vite::useHotFile(storage_path('framework/testing/browser.hot')))
    ->in('Browser');

/*
|--------------------------------------------------------------------------
| Expectations & helpers
|--------------------------------------------------------------------------
| Project-wide custom expectations and test helpers live here.
*/

/**
 * Base URL for browser/e2e tests. Empty E2E_BASE_URL (default) targets the
 * local app; set it to a deployed URL to smoke-test staging/production
 * instead (context/guides/testing.md).
 */
function e2e_url(string $path = '/'): string
{
    $base = trim((string) Env::get('E2E_BASE_URL', ''));

    return $base === '' ? $path : rtrim($base, '/').$path;
}

/**
 * Freeze CSS transitions/animations on an open browser page. Use before
 * assertions that race entrance animations — e.g. axe contrast scans reading
 * a page mid-fade-in (context/guides/testing.md).
 *
 * @template TPage of \Pest\Browser\Api\Webpage|\Pest\Browser\Api\PendingAwaitablePage
 *
 * @param  TPage  $page
 * @return TPage
 */
function freeze_motion($page)
{
    $page->script(<<<'JS'
        const style = document.createElement('style');
        style.textContent = '*, *::before, *::after { transition: none !important; animation: none !important; }';
        document.head.appendChild(style);
    JS);

    return $page;
}
