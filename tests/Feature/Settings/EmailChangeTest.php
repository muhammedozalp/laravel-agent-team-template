<?php

use App\Models\User;
use App\Notifications\EmailChanged;
use Illuminate\Support\Facades\Notification;

test('changing the email requires the current password', function () {
    $user = User::factory()->create(['email' => 'old@example.com']);

    $this->actingAs($user)
        ->from(route('profile.edit'))
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => 'new@example.com',
        ])
        ->assertSessionHasErrors('current_password');

    expect($user->fresh()->email)->toBe('old@example.com');
});

test('changing the email with the current password succeeds, notifies the old address, and forces re-verification', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => 'new@example.com',
            'current_password' => 'password',
        ])
        ->assertSessionHasNoErrors();

    $user->refresh();
    expect($user->email)->toBe('new@example.com')
        ->and($user->email_verified_at)->toBeNull();

    // The OLD address is warned — a hijacked session can't silently swap the email.
    Notification::assertSentOnDemand(
        EmailChanged::class,
        fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'old@example.com'
    );
});

test('updating the profile without changing the email does not require a password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'New Name',
            'email' => $user->email,
        ])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->name)->toBe('New Name');
});
