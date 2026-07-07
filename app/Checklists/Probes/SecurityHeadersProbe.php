<?php

namespace App\Checklists\Probes;

use App\Checklists\Probe;
use App\Checklists\ProbeResult;
use Illuminate\Support\Facades\Http;
use Throwable;

class SecurityHeadersProbe implements Probe
{
    private const REQUIRED = ['X-Frame-Options', 'X-Content-Type-Options', 'Referrer-Policy'];

    public function run(): ProbeResult
    {
        try {
            $response = Http::withoutVerifying()->timeout(10)->get(url()->to('/'));
        } catch (Throwable $e) {
            return ProbeResult::fail('homepage unreachable: '.$e->getMessage());
        }

        $missing = array_filter(self::REQUIRED, fn (string $h): bool => $response->header($h) === '');

        return $missing === []
            ? ProbeResult::pass('all present')
            : ProbeResult::fail('missing: '.implode(', ', $missing).' — served by nginx/Caddy, not PHP: check the proxy config');
    }
}
