<?php

namespace App\Checklists\Probes;

use App\Checklists\Probe;
use App\Checklists\ProbeResult;

class HttpsProbe implements Probe
{
    public function run(): ProbeResult
    {
        $url = (string) config('app.url');

        return str_starts_with($url, 'https://')
            ? ProbeResult::pass($url)
            : ProbeResult::fail("APP_URL is not HTTPS: {$url}");
    }
}
