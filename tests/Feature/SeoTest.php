<?php

/*
|--------------------------------------------------------------------------
| SEO baseline (context/guides/seo.md)
|--------------------------------------------------------------------------
*/

test('robots.txt blocks crawlers outside production', function () {
    // Local/staging must never be indexed (the staging vhost serves the same app).
    $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('content-type', 'text/plain; charset=UTF-8')
        ->assertSee('Disallow: /')
        ->assertDontSee('Sitemap:');
});

test('robots.txt allows crawlers and advertises the sitemap in production', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('Allow: /')
        ->assertSee('Sitemap: '.config('app.url').'/sitemap.xml');

    app()->detectEnvironment(fn () => 'testing');
});

test('sitemap.xml lists the public pages', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('content-type', 'text/xml; charset=UTF-8')
        ->assertSee('<urlset', escape: false)
        ->assertSee(config('app.url').'</loc>', escape: false);
});

test('the root template ships server-rendered SEO defaults', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('name="description"', escape: false)
        ->assertSee('property="og:title"', escape: false)
        ->assertSee('property="og:site_name"', escape: false);
});
