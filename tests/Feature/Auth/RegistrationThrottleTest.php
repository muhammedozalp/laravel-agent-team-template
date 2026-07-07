<?php

test('registration is rate limited', function () {
    // Limit is 10/min per IP (FortifyServiceProvider) — the 11th attempt is
    // throttled. CACHE_STORE=array in tests, so the counter starts fresh.
    foreach (range(1, 10) as $i) {
        $this->post(route('register.store'), [
            'name' => 'Bot',
            'email' => "bot{$i}@example.com",
            'password' => 'not-good-enough', // fails validation; still counts
            'password_confirmation' => 'mismatch',
        ]);
    }

    $this->post(route('register.store'), [
        'name' => 'Bot',
        'email' => 'bot11@example.com',
        'password' => 'a-secure-password',
        'password_confirmation' => 'a-secure-password',
    ])->assertTooManyRequests();
});
