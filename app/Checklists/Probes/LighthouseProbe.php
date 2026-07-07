<?php

namespace App\Checklists\Probes;

use App\Checklists\Probe;
use App\Checklists\ProbeResult;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

/**
 * Reads the report written by `npm run lighthouse` (browser container —
 * scripts/lighthouse-check.sh). Passes when every category meets the minimum
 * score and the report is fresh (config/checklists.php `lighthouse`).
 */
class LighthouseProbe implements Probe
{
    public function run(): ProbeResult
    {
        $path = storage_path('app/private/lighthouse.json');
        $min = (int) config('checklists.lighthouse.min_score', 80);
        $maxAge = (int) config('checklists.lighthouse.max_age_days', 30);

        if (! File::exists($path)) {
            return ProbeResult::fail('no report yet — run: docker compose run --rm browser npm run lighthouse');
        }

        if (Carbon::createFromTimestamp(File::lastModified($path))->lt(now()->subDays($maxAge))) {
            return ProbeResult::fail("report older than {$maxAge} days — rerun the audit");
        }

        $report = json_decode(File::get($path), true);
        $categories = $report['categories'] ?? [];

        if ($categories === []) {
            return ProbeResult::fail('report unreadable — rerun the audit');
        }

        $scores = [];
        $failing = [];
        foreach ($categories as $key => $category) {
            $score = (int) round(($category['score'] ?? 0) * 100);
            $scores[] = "{$key} {$score}";
            if ($score < $min) {
                $failing[] = "{$key} {$score} < {$min}";
            }
        }

        return $failing === []
            ? ProbeResult::pass(implode(' · ', $scores))
            : ProbeResult::fail(implode(' · ', $failing));
    }
}
