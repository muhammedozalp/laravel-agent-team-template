<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * sitemap.xml served as a route (guides/seo.md) — the production image's
 * public/ is baked read-only, so a generated file has nowhere to live.
 * An Inertia SPA renders links client-side, so crawler-based generation sees
 * nothing: public pages are listed explicitly in config/seo.php.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), function (): string {
            $sitemap = Sitemap::create();

            foreach (config('seo.sitemap', ['/']) as $path) {
                $sitemap->add(Url::create(url()->to($path)));
            }

            return $sitemap->render();
        });

        return response($xml, 200, ['Content-Type' => 'text/xml; charset=UTF-8']);
    }
}
