<?php

namespace App\Checklists;

use App\Models\ChecklistItem;
use App\Models\User;
use App\Notifications\ChecklistProbeFailed;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Runs every auto probe from config/checklists.php, persists results, and
 * notifies developers when a previously-passing probe regresses
 * (guides/checklists.md). Used by the admin page button and the weekly
 * schedule in routes/console.php.
 */
class ChecklistRunner
{
    /**
     * @return array<string, ProbeResult> keyed by item key
     */
    public function run(): array
    {
        $results = [];
        $regressions = [];

        foreach (config('checklists.groups', []) as $group) {
            foreach ($group['items'] as $definition) {
                $probeClass = $definition['probe'] ?? null;
                if ($probeClass === null) {
                    continue;
                }

                try {
                    /** @var Probe $probe */
                    $probe = app($probeClass);
                    $result = $probe->run();
                } catch (Throwable $e) {
                    $result = ProbeResult::fail('probe crashed: '.$e->getMessage());
                }

                $item = ChecklistItem::firstOrCreate(['key' => $definition['key']]);

                if ($item->last_result === true && ! $result->passed) {
                    $regressions[$definition['key']] = $result->detail;
                }

                $item->forceFill([
                    'checked_at' => $result->passed ? now() : null,
                    'checked_by' => null, // automated — not a human toggle
                    'last_result' => $result->passed,
                    'last_run_at' => now(),
                    'detail' => $result->detail,
                ])->save();

                $results[$definition['key']] = $result;
            }
        }

        if ($regressions !== []) {
            Notification::send(
                User::where('is_developer', true)->get(),
                new ChecklistProbeFailed($regressions),
            );
        }

        return $results;
    }
}
