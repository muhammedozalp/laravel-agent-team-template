<?php

namespace App\Checklists\Probes;

use App\Checklists\Probe;
use App\Checklists\ProbeResult;
use Illuminate\Support\Facades\Http;
use Throwable;

class SitemapProbe implements Probe
{
    public function run(): ProbeResult
    {
        try {
            $response = Http::withoutVerifying()->timeout(10)->get(url()->to('/sitemap.xml'));
        } catch (Throwable $e) {
            return ProbeResult::fail('sitemap.xml unreachable: '.$e->getMessage());
        }

        if (! $response->successful()) {
            return ProbeResult::fail('sitemap.xml returned '.$response->status());
        }

        $count = substr_count($response->body(), '<loc>');

        return $count > 0
            ? ProbeResult::pass("{$count} URL(s) listed")
            : ProbeResult::fail('sitemap.xml has no URLs — fill config/seo.php sitemap');
    }
}
