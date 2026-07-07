<?php

namespace App\Checklists\Probes;

use App\Checklists\Probe;
use App\Checklists\ProbeResult;

class SentryProbe implements Probe
{
    public function run(): ProbeResult
    {
        if ((string) config('sentry.dsn') !== '') {
            return ProbeResult::pass('DSN configured');
        }

        return app()->environment('production')
            ? ProbeResult::fail('SENTRY_DSN empty — production errors go nowhere (guides/deploy.md)')
            : ProbeResult::fail('SENTRY_DSN not set yet (fine in dev, required for go-live)');
    }
}
