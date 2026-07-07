<?php

namespace App\Checklists\Probes;

use App\Checklists\Probe;
use App\Checklists\ProbeResult;
use Illuminate\Support\Facades\Http;
use Throwable;

class RobotsProbe implements Probe
{
    public function run(): ProbeResult
    {
        try {
            $body = Http::withoutVerifying()->timeout(10)->get(url()->to('/robots.txt'))->body();
        } catch (Throwable $e) {
            return ProbeResult::fail('robots.txt unreachable: '.$e->getMessage());
        }

        if (app()->environment('production')) {
            return str_contains($body, 'Allow: /') && str_contains($body, 'Sitemap:')
                ? ProbeResult::pass('production: crawlable + sitemap advertised')
                : ProbeResult::fail('production robots.txt must allow crawling and advertise the sitemap');
        }

        return str_contains($body, 'Disallow: /')
            ? ProbeResult::pass('non-production: crawlers blocked')
            : ProbeResult::fail('non-production robots.txt must Disallow: / — this environment is indexable!');
    }
}
