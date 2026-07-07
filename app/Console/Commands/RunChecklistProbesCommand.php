<?php

namespace App\Console\Commands;

use App\Checklists\ChecklistRunner;
use Illuminate\Console\Command;

/**
 * Run the automated checklist probes (guides/checklists.md). Scheduled weekly;
 * also triggered from the admin checklists page. Exit code reflects the
 * results so it can gate CI/cron alerting if wanted.
 */
class RunChecklistProbesCommand extends Command
{
    protected $signature = 'app:run-checklist-probes';

    protected $description = 'Run automated checklist probes and record the results';

    public function handle(ChecklistRunner $runner): int
    {
        $failed = 0;

        foreach ($runner->run() as $key => $result) {
            if ($result->passed) {
                $this->line("  <fg=green>✓</> {$key}".($result->detail !== '' ? " — {$result->detail}" : ''));
            } else {
                $this->line("  <fg=red>✗</> {$key} — {$result->detail}");
                $failed++;
            }
        }

        $this->line('');
        $this->info($failed === 0 ? 'All probes passed.' : "{$failed} probe(s) failing.");

        return self::SUCCESS; // recording results is the job; failures notify separately
    }
}
