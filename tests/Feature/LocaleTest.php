<?php

use App\Models\User;

/*
|--------------------------------------------------------------------------
| i18n / locale handling (guides/i18n.md)
|--------------------------------------------------------------------------
| Default Turkish; session choice wins for guests, the user's saved locale
| wins when authenticated; translations are shared to React via Inertia.
*/

test('the default locale is Turkish', function () {
    $this->get(route('home'));

    expect(app()->getLocale())->toBe('tr');
});

test('a guest can switch locale and it persists in the session', function () {
    $this->post(route('locale.update'), ['locale' => 'en'])->assertRedirect();

    $this->get(route('home'));

    expect(app()->getLocale())->toBe('en')
        ->and(session('locale'))->toBe('en');
});

test('unsupported locales are rejected', function () {
    $this->post(route('locale.update'), ['locale' => 'xx'])
        ->assertSessionHasErrors('locale');
});

test('an authenticated user keeps their saved locale', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)->get(route('dashboard'));

    expect(app()->getLocale())->toBe('en');
});

test('switching locale while logged in saves it on the user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('locale.update'), ['locale' => 'en']);

    expect($user->fresh()->locale)->toBe('en');
});

test('translations and locale are shared with every Inertia page', function () {
    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'tr')
            ->has('translations'));
});
