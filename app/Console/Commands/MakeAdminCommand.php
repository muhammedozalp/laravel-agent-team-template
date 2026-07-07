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
    protected $signature = 'app:make-admin {email : Email address of an existing user}';

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
            // An admin must never be locked out by the approval gate.
            'approved_at' => $user->approved_at ?? now(),
        ])->save();

        $this->info("{$user->email} is now an admin.");

        return self::SUCCESS;
    }
}
