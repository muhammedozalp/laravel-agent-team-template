<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;

test('failed logins are written to the auth log channel', function () {
    $channel = Log::spy();
    Log::shouldReceive('channel')->with('auth')->andReturn($channel);

    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $channel->shouldHaveReceived('warning')
        ->withArgs(fn (string $message) => str_contains($message, 'Failed login'));
});

test('successful logins are written to the auth log channel', function () {
    $channel = Log::spy();
    Log::shouldReceive('channel')->with('auth')->andReturn($channel);

    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $channel->shouldHaveReceived('info')
        ->withArgs(fn (string $message) => str_contains($message, 'Login'));
});
