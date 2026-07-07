<?php

namespace App\Checklists\Probes;

use App\Checklists\Probe;
use App\Checklists\ProbeResult;
use Illuminate\Support\Facades\Http;
use Throwable;

class HealthProbe implements Probe
{
    public function run(): ProbeResult
    {
        try {
            $response = Http::withoutVerifying()->timeout(10)->get(url()->to('/up'));

            return $response->successful()
                ? ProbeResult::pass('/up returned '.$response->status())
                : ProbeResult::fail('/up returned '.$response->status());
        } catch (Throwable $e) {
            return ProbeResult::fail('/up unreachable: '.$e->getMessage());
        }
    }
}
