<?php

namespace App\Checklists;

/**
 * An automated checklist item (guides/checklists.md). Implementations live in
 * app/Checklists/Probes and are referenced from config/checklists.php; they
 * must be cheap (run weekly + on demand) and side-effect free.
 */
interface Probe
{
    public function run(): ProbeResult;
}
