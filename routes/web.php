<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

Route::inertia('/', 'welcome')->name('home');

// SEO endpoints — environment-aware (guides/seo.md).
Route::get('robots.txt', RobotsController::class)->name('seo.robots');
Route::get('sitemap.xml', SitemapController::class)->name('seo.sitemap');

// Locale switch — guests (session) and users (persisted). guides/i18n.md.
Route::post('locale', [LocaleController::class, 'update'])->name('locale.update');

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
