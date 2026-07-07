<?php

use App\Models\User;

test('app:make-admin promotes an existing user', function () {
    $user = User::factory()->create(['email' => 'owner@example.com']);

    $this->artisan('app:make-admin', ['email' => 'owner@example.com'])
        ->assertSuccessful();

    expect($user->fresh()->is_admin)->toBeTrue();
});

test('app:make-admin fails clearly for an unknown email', function () {
    $this->artisan('app:make-admin', ['email' => 'nobody@example.com'])
        ->assertFailed();
});

test('promoted admins are auto-approved so the approval gate cannot lock them out', function () {
    config(['auth.require_approval' => true]);

    $user = User::factory()->unapproved()->create(['email' => 'owner@example.com']);

    $this->artisan('app:make-admin', ['email' => 'owner@example.com'])->assertSuccessful();

    expect($user->fresh()->approved_at)->not->toBeNull();
});
