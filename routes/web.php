<?php

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

// Approval gate notice (config auth.require_approval — guides/auth.md).
Route::get('approval-pending', function (Request $request): Response|RedirectResponse {
    /** @var User $user */
    $user = $request->user();

    if (! config('auth.require_approval') || $user->is_admin || $user->isApproved()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('auth/approval-pending');
})->middleware(['auth'])->name('approval.notice');

require __DIR__.'/settings.php';
