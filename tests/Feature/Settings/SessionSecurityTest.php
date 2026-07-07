<?php

use App\Models\User;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Session\Middleware\AuthenticateSession;

test('the web middleware group authenticates sessions', function () {
    // AuthenticateSession compares each session's stored password hash with the
    // user's current hash — without it, a password change leaves every other
    // logged-in session (a possibly stolen one) alive.
    $groups = app(Kernel::class)->getMiddlewareGroups();

    expect($groups['web'])->toContain(AuthenticateSession::class);
});

test('changing the password rotates the remember token', function () {
    $user = User::factory()->create();
    $oldToken = $user->remember_token;

    $this->actingAs($user)
        ->from(route('security.edit'))
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->remember_token)->not->toBe($oldToken);
});

test('the current session survives a password change', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('user-password.update'), [
        'current_password' => 'password',
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ])->assertSessionHasNoErrors();

    $this->get(route('dashboard'))->assertOk();
    $this->assertAuthenticatedAs($user);
});
