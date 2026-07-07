<?php

use App\Models\User;
use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

test('the verification email is queued', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    $user->sendEmailVerificationNotification();

    Notification::assertSentTo($user, QueuedVerifyEmail::class);
    expect(new QueuedVerifyEmail)->toBeInstanceOf(ShouldQueue::class);
});

test('the password reset email is queued', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHasNoErrors();

    Notification::assertSentTo($user, QueuedResetPassword::class);
    expect(new QueuedResetPassword('token'))->toBeInstanceOf(ShouldQueue::class);
});
