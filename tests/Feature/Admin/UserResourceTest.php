<?php

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Notifications\AccountApproved;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
    $this->actingAs(User::factory()->admin()->create());
});

it('lists users', function () {
    $users = User::factory()->count(3)->create();

    livewire(ListUsers::class)
        ->assertOk()
        ->assertCanSeeTableRecords($users);
});

it('approves a pending user and notifies them', function () {
    Notification::fake();

    $pending = User::factory()->unapproved()->create();

    livewire(ListUsers::class)
        ->callTableAction('approve', $pending)
        ->assertNotified();

    expect($pending->fresh()->isApproved())->toBeTrue();
    Notification::assertSentTo($pending, AccountApproved::class);
});

it('does not show the approve action for already-approved users', function () {
    $approved = User::factory()->create();

    livewire(ListUsers::class)
        ->assertTableActionHidden('approve', $approved);
});

it('deletes a user', function () {
    $user = User::factory()->create();

    livewire(ListUsers::class)
        ->callTableAction('delete', $user)
        ->assertNotified();

    expect(User::find($user->id))->toBeNull();
});

it('cannot delete yourself', function () {
    /** @var User $me */
    $me = auth()->user();

    livewire(ListUsers::class)
        ->assertTableActionHidden('delete', $me);
});
