<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve the request locale: authenticated user's saved choice → session
 * choice → app default (tr). Allowed locales: config('app.available_locales').
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale;

        if ($locale === null) {
            $locale = $request->session()->get('locale');
        }

        if (is_string($locale) && in_array($locale, config('app.available_locales', []), true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
