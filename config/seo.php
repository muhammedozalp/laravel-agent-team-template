<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SEO defaults (guides/seo.md)
    |--------------------------------------------------------------------------
    | Server-rendered fallbacks in resources/views/app.blade.php; pages
    | override client-side via the <Seo> React component. Set real values
    | when bootstrapping a project.
    */

    'description' => env('SEO_DESCRIPTION', 'A production-grade Laravel application.'),

    // Absolute URL to the default social-share image (1200x630).
    'image' => env('SEO_IMAGE', '/apple-touch-icon.png'),

    /*
    | Public pages listed in /sitemap.xml. An Inertia SPA renders links
    | client-side, so a crawler-based generator sees nothing — list public
    | routes explicitly as the app grows (routes/web.php names them).
    */
    'sitemap' => [
        '/',
    ],

];
