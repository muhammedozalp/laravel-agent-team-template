<?php

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Auth smoke — the critical flow end-to-end
|--------------------------------------------------------------------------
| Local-only: writes users, so it must not run against a deployed URL
| (the smoke group stays deploy-safe; these carry no `smoke` group).
*/

it('registers a new user through the browser', function () {
    visit('/register')
        ->fill('Name', 'Template User')
        ->fill('Email address', 'user@example.com')
        ->fill('Password', 'a-secure-password')
        ->fill('Confirm password', 'a-secure-password')
        ->press('Create account')
        // User implements MustVerifyEmail, so the `verified` middleware bounces
        // fresh registrations to Fortify's verification notice first.
        ->assertPathIs('/email/verify')
        ->assertSee('Email verification');

    expect(User::where('email', 'user@example.com')->exists())->toBeTrue();
});

it('logs in and out through the browser', function () {
    User::factory()->create(['name' => 'Known User', 'email' => 'known@example.com']);

    visit('/login')
        ->fill('Email address', 'known@example.com')
        ->fill('Password', 'password') // factory default
        ->press('Log in')
        ->assertPathIs('/dashboard')
        ->click('Known User')          // opens the sidebar user menu
        ->click('Log out')
        // Back on the guest welcome page — whose nav is translated (default
        // locale tr, guides/i18n.md), so this also proves i18n end-to-end.
        ->assertSee('Giriş yap');
});

it('login page has no serious accessibility issues', function () {
    freeze_motion(visit('/login'))->assertNoAccessibilityIssues(level: 1);
})->group('a11y');

it('register page has no serious accessibility issues', function () {
    freeze_motion(visit('/register'))->assertNoAccessibilityIssues(level: 1);
})->group('a11y');
