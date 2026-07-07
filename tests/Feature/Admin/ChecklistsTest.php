<?php

use App\Filament\Pages\Checklists;
use App\Models\ChecklistItem;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

/*
|--------------------------------------------------------------------------
| Access — developer-only (ADR 0011): even admins must not see the page.
*/

test('regular admins cannot see or open the checklists page', function () {
    $this->actingAs(User::factory()->admin()->create());

    expect(Checklists::canAccess())->toBeFalse();

    $this->get(Checklists::getUrl())->assertForbidden();
});

test('developers can open the checklists page', function () {
    $this->actingAs(User::factory()->developer()->create());

    $this->get(Checklists::getUrl())->assertOk();
});

test('app:make-admin --developer grants the developer tier', function () {
    $user = User::factory()->create(['email' => 'dev@example.com']);

    $this->artisan('app:make-admin', ['email' => 'dev@example.com', '--developer' => true])
        ->assertSuccessful();

    expect($user->fresh()->is_developer)->toBeTrue()
        ->and($user->fresh()->is_admin)->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Manual toggling with audit trail
*/

test('a developer can toggle a manual item and the audit trail records it', function () {
    $dev = User::factory()->developer()->create();
    $this->actingAs($dev);

    livewire(Checklists::class)
        ->call('toggle', 'seo.search-console');

    $item = ChecklistItem::where('key', 'seo.search-console')->first();
    expect($item->checked_at)->not->toBeNull()
        ->and($item->checked_by)->toBe($dev->id);

    livewire(Checklists::class)->call('toggle', 'seo.search-console');

    expect($item->fresh()->checked_at)->toBeNull();
});

test('auto items cannot be toggled by hand', function () {
    $this->actingAs(User::factory()->developer()->create());

    livewire(Checklists::class)->call('toggle', 'auto.health');

    expect(ChecklistItem::where('key', 'auto.health')->value('checked_at'))->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Definitions sanity — config drives the page; keys must be coherent.
*/

test('checklist definitions are unique and probes resolve', function () {
    $keys = [];

    foreach (config('checklists.groups') as $group) {
        foreach ($group['items'] as $item) {
            $keys[] = $item['key'];
            if (isset($item['probe'])) {
                expect(class_exists($item['probe']))->toBeTrue("probe missing: {$item['probe']}");
            }
        }
    }

    expect($keys)->toBe(array_unique($keys))
        ->and(count($keys))->toBeGreaterThanOrEqual(40);
});
