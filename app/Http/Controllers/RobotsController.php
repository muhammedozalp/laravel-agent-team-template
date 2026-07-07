<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Environment-aware robots.txt served as a route (guides/seo.md): staging
 * serves the same app and must never be indexed, so a static file can't work.
 */
class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $content = app()->environment('production')
            ? "User-agent: *\nAllow: /\n\nSitemap: ".config('app.url').'/sitemap.xml'
            : "User-agent: *\nDisallow: /";

        return response($content."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
