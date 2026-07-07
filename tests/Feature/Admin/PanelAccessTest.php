<?php

use App\Models\User;

test('guests are redirected from the admin panel to its login', function () {
    $this->get('/admin')->assertRedirect();
});

test('regular users cannot access the admin panel', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});

test('admins with a verified email can access the admin panel', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertOk();
});

test('admins without a verified email cannot access the admin panel', function () {
    $this->actingAs(User::factory()->admin()->unverified()->create())
        ->get('/admin')
        ->assertForbidden();
});
