<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Locale switching (guides/i18n.md): session for guests, persisted on the
 * user when authenticated.
 */
class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(config('app.available_locales', []))],
        ]);

        $request->session()->put('locale', $validated['locale']);

        $request->user()?->forceFill(['locale' => $validated['locale']])->save();

        return back();
    }
}
