<?php

namespace App\Checklists\Probes;

use App\Checklists\Probe;
use App\Checklists\ProbeResult;

class DebugModeProbe implements Probe
{
    public function run(): ProbeResult
    {
        if (! app()->environment('production')) {
            return ProbeResult::pass('non-production — debug allowed here');
        }

        return config('app.debug') === false
            ? ProbeResult::pass('APP_DEBUG=false')
            : ProbeResult::fail('APP_DEBUG must be false in production — it leaks secrets in error pages');
    }
}
