<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Framework ResetPassword, queued — a slow SMTP provider must not block the
 * forgot-password request (wired in User::sendPasswordResetNotification()).
 */
class QueuedResetPassword extends ResetPassword implements ShouldQueue
{
    use Queueable;
}
