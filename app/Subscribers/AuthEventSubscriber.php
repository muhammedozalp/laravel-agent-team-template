<?php

namespace App\Subscribers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * Auth audit trail: every security-relevant auth event lands in the dedicated
 * `auth` log channel (config/logging.php) — the first thing wanted during an
 * account-takeover incident. Registered in AppServiceProvider::boot().
 */
class AuthEventSubscriber
{
    /**
     * @return array<class-string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
            Lockout::class => 'handleLockout',
            Registered::class => 'handleRegistered',
            Verified::class => 'handleVerified',
            PasswordReset::class => 'handlePasswordReset',
            OtherDeviceLogout::class => 'handleOtherDeviceLogout',
        ];
    }

    public function handleLogin(Login $event): void
    {
        $this->log('info', 'Login', $event->user->getAuthIdentifier());
    }

    public function handleLogout(Logout $event): void
    {
        $this->log('info', 'Logout', $event->user->getAuthIdentifier());
    }

    public function handleFailed(Failed $event): void
    {
        $this->log('warning', 'Failed login', $event->user?->getAuthIdentifier(), [
            'email' => $event->credentials['email'] ?? null,
        ]);
    }

    public function handleLockout(Lockout $event): void
    {
        $this->log('warning', 'Lockout', null);
    }

    public function handleRegistered(Registered $event): void
    {
        $this->log('info', 'Registered', $event->user->getAuthIdentifier());
    }

    public function handleVerified(Verified $event): void
    {
        // Verified types $user as MustVerifyEmail, which doesn't declare
        // getAuthIdentifier() — in practice it is always the User model.
        $userId = $event->user instanceof Authenticatable
            ? $event->user->getAuthIdentifier()
            : null;

        $this->log('info', 'Email verified', $userId);
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->log('info', 'Password reset', $event->user->getAuthIdentifier());
    }

    public function handleOtherDeviceLogout(OtherDeviceLogout $event): void
    {
        $this->log('info', 'Other devices logged out', $event->user->getAuthIdentifier());
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function log(string $level, string $message, int|string|null $userId, array $context = []): void
    {
        Log::channel('auth')->{$level}($message, array_filter([
            'user_id' => $userId,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            ...$context,
        ]));
    }
}
