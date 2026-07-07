<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Promote an existing user to panel admin. Deliberately the ONLY way to get
 * the first admin — is_admin is never mass-assignable and never settable
 * through registration.
 */
class MakeAdminCommand extends Command
{
    protected $signature = 'app:make-admin
        {email : Email address of an existing user}
        {--developer : Also grant the developer tier (checklists page — ADR 0011)}';

    protected $description = 'Promote an existing user to admin (grants /admin panel access)';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error("No user found with email [{$this->argument('email')}]. Register the account first.");

            return self::FAILURE;
        }

        $user->forceFill([
            'is_admin' => true,
            'is_developer' => $user->is_developer || (bool) $this->option('developer'),
            // An admin must never be locked out by the approval gate.
            'approved_at' => $user->approved_at ?? now(),
        ])->save();

        $this->info("{$user->email} is now an admin".($user->is_developer ? ' + developer' : '').'.');

        return self::SUCCESS;
    }
}
