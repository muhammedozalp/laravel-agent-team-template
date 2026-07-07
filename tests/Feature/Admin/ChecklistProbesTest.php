<?php

use App\Models\ChecklistItem;
use App\Models\User;
use App\Notifications\ChecklistProbeFailed;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

/*
|--------------------------------------------------------------------------
| Auto-check probes (checklists Phase 2): run on demand + weekly schedule;
| a previously-green probe going red notifies developers.
*/

test('probes mark passing auto items as checked', function () {
    Http::fake(['*' => Http::response('ok', 200, [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ])]);

    $this->artisan('app:run-checklist-probes')->assertSuccessful();

    expect(ChecklistItem::where('key', 'auto.health')->value('checked_at'))->not->toBeNull();
});

test('a probe regression notifies developers', function () {
    Notification::fake();
    $dev = User::factory()->developer()->create();

    // Http::fake stubs are cumulative — a sequence models "green first run,
    // then the app breaks" (4 HTTP probes per run).

    Http::fake(['*' => Http::sequence()
        ->push('ok', 200, ['X-Frame-Options' => 'SAMEORIGIN', 'X-Content-Type-Options' => 'nosniff', 'Referrer-Policy' => 'strict-origin-when-cross-origin'])
        ->push('ok', 200, ['X-Frame-Options' => 'SAMEORIGIN', 'X-Content-Type-Options' => 'nosniff', 'Referrer-Policy' => 'strict-origin-when-cross-origin'])
        ->push('ok', 200, ['X-Frame-Options' => 'SAMEORIGIN', 'X-Content-Type-Options' => 'nosniff', 'Referrer-Policy' => 'strict-origin-when-cross-origin'])
        ->push('ok', 200, ['X-Frame-Options' => 'SAMEORIGIN', 'X-Content-Type-Options' => 'nosniff', 'Referrer-Policy' => 'strict-origin-when-cross-origin'])
        ->whenEmpty(Http::response('down', 500)),
    ]);

    $this->artisan('app:run-checklist-probes'); // all green
    $this->artisan('app:run-checklist-probes'); // regression

    Notification::assertSentTo($dev, ChecklistProbeFailed::class);
});

test('a probe that was already failing does not re-notify', function () {
    Notification::fake();
    User::factory()->developer()->create();

    Http::fake(['*' => Http::response('down', 500)]);
    $this->artisan('app:run-checklist-probes');
    $this->artisan('app:run-checklist-probes');

    Notification::assertCount(0);
});
