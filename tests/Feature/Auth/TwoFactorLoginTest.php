<?php

use App\Models\User;
use Laravel\Fortify\Features;
use PragmaRX\Google2FA\Google2FA;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());
});

test('a valid TOTP code completes the two-factor login', function () {
    $google2fa = app(Google2FA::class);
    $secret = $google2fa->generateSecretKey();

    $user = User::factory()->withTwoFactor()->create([
        'two_factor_secret' => encrypt($secret),
    ]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));

    $this->assertGuest();

    $this->post(route('two-factor.login.store'), [
        'code' => $google2fa->getCurrentOtp($secret),
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('a recovery code completes the two-factor login and is consumed', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->post(route('two-factor.login.store'), [
        'recovery_code' => 'recovery-code-1', // factory's withTwoFactor() state
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);

    // Single use: the consumed code must be gone (replaced by a fresh one).
    $remaining = json_decode(decrypt($user->fresh()->two_factor_recovery_codes), true);
    expect($remaining)->not->toContain('recovery-code-1');
});

test('an invalid TOTP code is rejected', function () {
    $user = User::factory()->withTwoFactor()->create([
        'two_factor_secret' => encrypt(app(Google2FA::class)->generateSecretKey()),
    ]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->post(route('two-factor.login.store'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    $this->assertGuest();
});
