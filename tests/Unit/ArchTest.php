<?php

/*
|--------------------------------------------------------------------------
| Architecture tests — conventions as executable rules
|--------------------------------------------------------------------------
| When a review comment repeats, turn it into a rule here instead
| (context/guides/testing.md).
*/

arch()->preset()->php();
// AdminPanelProvider is Filament's documented name — exempt from the
// "*ServiceProvider" suffix rule.
arch()->preset()->laravel()->ignoring(App\Providers\Filament\AdminPanelProvider::class);
arch()->preset()->security();

// No debug output committed.
arch('no debug calls')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

// env() only in config/ — anywhere else breaks config caching.
arch('env() only in config files')
    ->expect('env')
    ->toOnlyBeUsedIn('config');
