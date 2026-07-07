<?php

namespace App\Filament\Pages;

use App\Checklists\ChecklistRunner;
use App\Models\ChecklistItem;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Launch & maintenance checklists — DEVELOPER-ONLY (ADR 0011): even client
 * admins never see this page. Definitions in config/checklists.php; state in
 * checklist_items; auto items run via probes (guides/checklists.md).
 */
class Checklists extends Page
{
    protected string $view = 'filament.pages.checklists';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    public static function canAccess(): bool
    {
        return auth()->user()?->is_developer === true;
    }

    /**
     * Toggle a MANUAL item (auto items only change via probes).
     */
    public function toggle(string $key): void
    {
        $definition = $this->definition($key);

        if ($definition === null || isset($definition['probe'])) {
            return;
        }

        $item = ChecklistItem::firstOrCreate(['key' => $key]);

        $item->forceFill($item->isChecked()
            ? ['checked_at' => null, 'checked_by' => null]
            : ['checked_at' => now(), 'checked_by' => auth()->id()],
        )->save();
    }

    public function runProbes(ChecklistRunner $runner): void
    {
        $results = $runner->run();
        $failed = count(array_filter($results, fn ($r) => ! $r->passed));

        Notification::make()
            ->title($failed === 0
                ? __('All :count probes passed', ['count' => count($results)])
                : __(':failed of :count probes failing', ['failed' => $failed, 'count' => count($results)]))
            ->{$failed === 0 ? 'success' : 'warning'}()
            ->send();
    }

    /**
     * Groups + items merged with their state, for the view.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getGroupsProperty(): array
    {
        $state = ChecklistItem::all()->keyBy('key');

        return array_map(function (array $group) use ($state): array {
            $group['items'] = array_map(function (array $item) use ($state): array {
                $item['state'] = $state->get($item['key']);
                $item['auto'] = isset($item['probe']);

                return $item;
            }, $group['items']);

            $checked = count(array_filter($group['items'], fn (array $i): bool => $i['state']?->isChecked() ?? false));
            $group['progress'] = $checked.' / '.count($group['items']);
            $group['complete'] = $checked === count($group['items']);

            return $group;
        }, config('checklists.groups', []));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function definition(string $key): ?array
    {
        foreach (config('checklists.groups', []) as $group) {
            foreach ($group['items'] as $item) {
                if ($item['key'] === $key) {
                    return $item;
                }
            }
        }

        return null;
    }
}
