<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            // i18n (guides/i18n.md): current locale + its translation map for
            // the t()/tChoice() helpers in resources/js/lib/i18n.ts.
            'locale' => app()->getLocale(),
            'translations' => fn (): array => $this->translations(app()->getLocale()),
        ];
    }

    /**
     * Flat translation map for one locale: lang/{locale}.json keys merged with
     * lang/{locale}/*.php groups flattened to dot notation. Cached forever —
     * busted by deploys (config:cache flow) or `php artisan cache:clear`.
     *
     * @return array<string, string>
     */
    private function translations(string $locale): array
    {
        return Cache::rememberForever("translations.{$locale}", function () use ($locale): array {
            $map = [];

            $json = lang_path("{$locale}.json");
            if (File::exists($json)) {
                $map = json_decode(File::get($json), true, 512, JSON_THROW_ON_ERROR);
            }

            foreach (File::glob(lang_path("{$locale}/*.php")) as $file) {
                $group = pathinfo($file, PATHINFO_FILENAME);
                foreach (Arr::dot(require $file) as $key => $value) {
                    if (is_string($value)) {
                        $map["{$group}.{$key}"] = $value;
                    }
                }
            }

            return $map;
        });
    }
}
