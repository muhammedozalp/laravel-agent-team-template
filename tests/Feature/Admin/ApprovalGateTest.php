<?php

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Optional approval gate (REQUIRE_ACCOUNT_APPROVAL, default off)
|--------------------------------------------------------------------------
| When on, fresh registrations wait in a pending state until an admin
| approves them — same middleware pattern as email verification.
*/

test('the gate is off by default and users pass through', function () {
    $user = User::factory()->unapproved()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

test('with the gate on, unapproved users are held at the approval notice', function () {
    config(['auth.require_approval' => true]);

    $user = User::factory()->unapproved()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('approval.notice'));

    $this->actingAs($user)->get(route('approval.notice'))->assertOk();
});

test('with the gate on, approved users pass through', function () {
    config(['auth.require_approval' => true]);

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk();
});

test('approved users visiting the notice are sent to the dashboard', function () {
    config(['auth.require_approval' => true]);

    $this->actingAs(User::factory()->create())
        ->get(route('approval.notice'))
        ->assertRedirect(route('dashboard'));
});
