<?php

/*
|--------------------------------------------------------------------------
| Cross-device / mobile rendering (context/guides/testing.md)
|--------------------------------------------------------------------------
| Device emulation via Playwright profiles. Add the devices your project's
| audience actually uses; keep this to key pages.
*/

it('renders the landing page on mobile', function () {
    visit(e2e_url('/'))
        ->on()->mobile()
        ->assertSee('Documentation')
        ->assertNoJavaScriptErrors();
});

it('renders the landing page on an iPhone 15 Pro', function () {
    visit(e2e_url('/'))
        ->on()->iPhone15Pro()
        ->assertSee('Documentation');
});

it('renders the landing page on a Pixel 8', function () {
    visit(e2e_url('/'))
        ->on()->pixel8()
        ->assertSee('Documentation');
});
