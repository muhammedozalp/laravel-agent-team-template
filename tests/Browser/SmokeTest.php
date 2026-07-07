<?php

/*
|--------------------------------------------------------------------------
| Smoke suite — fast critical-path checks (context/guides/testing.md)
|--------------------------------------------------------------------------
| Runs against the local app by default; set E2E_BASE_URL to run the same
| checks against staging/production after a deploy. Cross-browser: append
| `--browser firefox` or `--browser safari` (default: chrome).
*/

it('serves the landing page cleanly', function () {
    visit(e2e_url('/'))
        ->assertSee('Documentation')
        ->assertNoSmoke(); // no console logs, no JavaScript errors
})->group('smoke');

it('has no serious accessibility issues on the landing page', function () {
    freeze_motion(visit(e2e_url('/')))
        ->assertNoAccessibilityIssues(level: 1); // 0=critical … 3=minor
})->group('smoke', 'a11y');
