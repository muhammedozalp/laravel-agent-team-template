<?php

namespace App\Checklists\Probes;

use App\Checklists\Probe;
use App\Checklists\ProbeResult;

class MailerProbe implements Probe
{
    public function run(): ProbeResult
    {
        $mailer = (string) config('mail.default');

        if (! app()->environment('production')) {
            return ProbeResult::pass("dev mailer: {$mailer} (Mailpit catches everything)");
        }

        return in_array($mailer, ['log', 'array', 'failover'], true) === false
            ? ProbeResult::pass("production mailer: {$mailer}")
            : ProbeResult::fail("production mailer is '{$mailer}' — verification/reset emails are silently dropped");
    }
}
