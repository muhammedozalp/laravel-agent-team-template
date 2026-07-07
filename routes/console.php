<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automated checklist probes (guides/checklists.md) — regressions notify
// developer-tier users. The scheduler service runs always (guides/docker.md).
Schedule::command('app:run-checklist-probes')->weeklyOn(1, '06:00');
